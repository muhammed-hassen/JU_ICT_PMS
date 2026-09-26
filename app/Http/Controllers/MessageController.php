<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Messaging (SRS 5.9). People only ever see conversations they take part in;
 * the Director's delete-any-message is the one exception, for moderation.
 */
class MessageController extends Controller
{
    public function index(): View
    {
        return view('messages.index', $this->sidebarData(null));
    }

    public function create(Request $request): View
    {
        $user = $request->user();

        return view('messages.create', $this->sidebarData(null) + [
            'people' => $user->messageableUsers(),
            'preselected' => array_map('intval', (array) $request->query('to', [])),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $allowed = $user->messageableUsers()->pluck('id')->all();

        $validated = $request->validate([
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['integer', 'in:'.implode(',', $allowed ?: [0])],
            'subject' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ], [
            'participants.required' => 'Choose at least one person.',
            'participants.*.in' => 'You can only message people in your teams and the ICT Director.',
        ]);

        $people = collect($validated['participants'])->push($user->id)->unique()->sort()->values();

        // A one-to-one chat without a subject reuses the existing thread with that person.
        $conversation = null;
        if ($people->count() === 2 && empty($validated['subject'])) {
            $conversation = Conversation::for($user)
                ->whereNull('subject')
                ->has('participants', '=', 2)
                ->whereHas('participants', fn ($q) => $q->where('users.id', $people->first(fn ($id) => $id !== $user->id)))
                ->first();
        }

        DB::transaction(function () use (&$conversation, $user, $people, $validated) {
            if (! $conversation) {
                $conversation = Conversation::create([
                    'subject' => $validated['subject'] ?? null,
                    'created_by' => $user->id,
                ]);
                $conversation->participants()->attach($people->all());
            }

            $this->postMessage($conversation, $user, $validated['body']);
        });

        return redirect()->route('messages.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();
        $this->authorizeParticipant($conversation, $user);

        $conversation->load(['participants', 'messages.sender']);
        $conversation->markReadFor($user);

        return view('messages.show', $this->sidebarData($conversation) + [
            'conversation' => $conversation,
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $this->authorizeParticipant($conversation, $user);

        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $message = $this->postMessage($conversation, $user, $validated['body']);
        $conversation->markReadFor($user);

        if ($request->wantsJson()) {
            return response()->json(['html' => view('messages.partials.message', ['message' => $message->load('sender')])->render(), 'id' => $message->id]);
        }

        return redirect()->route('messages.show', $conversation)->withFragment('message-'.$message->id);
    }

    /** New messages since the given id, for the open conversation page to poll. */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $this->authorizeParticipant($conversation, $user);

        $messages = $conversation->messages()
            ->with('sender')
            ->where('id', '>', (int) $request->query('after', 0))
            ->get();

        if ($messages->isNotEmpty()) {
            $conversation->markReadFor($user);
        }

        return response()->json([
            'html' => $messages->map(fn ($m) => view('messages.partials.message', ['message' => $m])->render())->implode(''),
            'last_id' => $messages->last()?->id,
        ]);
    }

    public function update(Request $request, Message $message): RedirectResponse
    {
        $user = $request->user();
        abort_unless((int) $message->user_id === (int) $user->id && $user->can('edit-own-message'), 403, 'You can only edit your own messages.');

        $validated = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $message->update(['body' => $validated['body'], 'edited_at' => now()]);

        return redirect()->route('messages.show', $message->conversation_id)->withFragment('message-'.$message->id);
    }

    public function destroy(Request $request, Message $message): RedirectResponse
    {
        $user = $request->user();
        $own = (int) $message->user_id === (int) $user->id && $user->can('delete-own-message');
        abort_unless($own || $user->can('delete-any-message'), 403, 'You can only delete your own messages.');

        $message->delete();

        return redirect()->route('messages.show', $message->conversation_id)->with('success', 'Message deleted.');
    }

    private function postMessage(Conversation $conversation, User $sender, string $body): Message
    {
        $message = $conversation->messages()->create(['user_id' => $sender->id, 'body' => $body]);
        $conversation->update(['last_message_at' => $message->created_at]);

        // One unread notification per conversation: refresh it instead of piling up a new one per message.
        foreach ($conversation->participants()->where('users.id', '!=', $sender->id)->get() as $recipient) {
            $recipient->unreadNotifications()
                ->where('type', NewMessage::class)
                ->get()
                ->filter(fn ($n) => ($n->data['conversation_id'] ?? null) === $conversation->id)
                ->each->delete();

            $recipient->notify(new NewMessage($message->setRelation('conversation', $conversation)));
        }

        return $message;
    }

    private function authorizeParticipant(Conversation $conversation, User $user): void
    {
        abort_unless($conversation->hasParticipant($user), 403, 'You are not part of this conversation.');
    }

    /** Conversation list shown beside every messages page. */
    private function sidebarData(?Conversation $active): array
    {
        $user = auth()->user();

        $conversations = Conversation::for($user)
            ->with(['participants', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->get()
            ->each(fn ($c) => $c->unread = $c->unreadCountFor($user));

        return ['conversations' => $conversations, 'active' => $active];
    }
}
