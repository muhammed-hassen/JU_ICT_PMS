<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Team;
use App\Models\User;
use App\Notifications\NewMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;

    private User $bob;

    private User $outsider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        [$this->alice, $this->bob, $this->outsider] = User::factory()->count(3)->create()
            ->each(fn ($u) => $u->assignRole('Team Member'))->all();

        $team = Team::create(['name' => 'Chat Team']);
        $team->members()->attach([$this->alice->id, $this->bob->id]);
        Team::create(['name' => 'Other Team'])->members()->attach($this->outsider->id);
    }

    public function test_member_can_message_a_teammate_and_they_are_notified(): void
    {
        $this->actingAs($this->alice)
            ->post(route('messages.store'), ['participants' => [$this->bob->id], 'body' => 'Hi Bob'])
            ->assertRedirect();

        $conversation = Conversation::firstOrFail();
        $this->assertTrue($conversation->hasParticipant($this->bob));
        $this->assertSame(1, $this->bob->unreadConversationCount());
        $this->assertSame(1, $this->bob->unreadNotifications()->where('type', NewMessage::class)->count());

        $this->actingAs($this->bob)->get(route('messages.show', $conversation))->assertOk()->assertSee('Hi Bob');
        $this->assertSame(0, $this->bob->fresh()->unreadConversationCount());
    }

    public function test_second_message_reuses_the_one_to_one_thread_and_the_notification(): void
    {
        $this->actingAs($this->alice)->post(route('messages.store'), ['participants' => [$this->bob->id], 'body' => 'One']);
        $this->actingAs($this->alice)->post(route('messages.store'), ['participants' => [$this->bob->id], 'body' => 'Two']);

        $this->assertSame(1, Conversation::count());
        $this->assertSame(1, $this->bob->unreadNotifications()->count());
    }

    public function test_member_cannot_message_someone_outside_their_teams(): void
    {
        $this->actingAs($this->alice)
            ->post(route('messages.store'), ['participants' => [$this->outsider->id], 'body' => 'Hello'])
            ->assertSessionHasErrors('participants.0');
    }

    public function test_members_reach_their_own_leader_only_and_leaders_reach_each_other(): void
    {
        $ownLeader = User::factory()->create();
        $ownLeader->assignRole('Team Leader');
        Team::where('name', 'Chat Team')->first()->update(['team_leader_id' => $ownLeader->id]);
        $otherLeader = User::factory()->create();
        $otherLeader->assignRole('Team Leader');
        Team::where('name', 'Other Team')->first()->update(['team_leader_id' => $otherLeader->id]);

        $this->assertTrue($this->alice->messageableUsers()->contains($ownLeader));
        $this->assertFalse($this->alice->messageableUsers()->contains($otherLeader));
        $this->actingAs($this->alice)
            ->post(route('messages.store'), ['participants' => [$otherLeader->id], 'body' => 'Hi'])
            ->assertSessionHasErrors('participants.0');

        $this->assertTrue($ownLeader->messageableUsers()->contains($otherLeader));
    }

    public function test_outsider_cannot_read_or_reply_to_a_conversation(): void
    {
        $this->actingAs($this->alice)->post(route('messages.store'), ['participants' => [$this->bob->id], 'body' => 'Private']);
        $conversation = Conversation::firstOrFail();

        $this->actingAs($this->outsider)->get(route('messages.show', $conversation))->assertForbidden();
        $this->actingAs($this->outsider)->post(route('messages.reply', $conversation), ['body' => 'x'])->assertForbidden();
    }

    public function test_only_the_author_can_edit_or_delete_a_message(): void
    {
        $this->actingAs($this->alice)->post(route('messages.store'), ['participants' => [$this->bob->id], 'body' => 'Original']);
        $message = Conversation::firstOrFail()->messages()->first();

        $this->actingAs($this->bob)->patch(route('messages.update', $message), ['body' => 'Hacked'])->assertForbidden();
        $this->actingAs($this->bob)->delete(route('messages.destroy', $message))->assertForbidden();

        $this->actingAs($this->alice)->patch(route('messages.update', $message), ['body' => 'Edited'])->assertRedirect();
        $this->assertSame('Edited', $message->fresh()->body);
        $this->assertNotNull($message->fresh()->edited_at);
    }

    public function test_poll_returns_only_newer_messages(): void
    {
        $this->actingAs($this->alice)->post(route('messages.store'), ['participants' => [$this->bob->id], 'body' => 'First']);
        $conversation = Conversation::firstOrFail();
        $first = $conversation->messages()->first();
        $this->actingAs($this->bob)->postJson(route('messages.reply', $conversation), ['body' => 'Second'])->assertOk();

        $this->actingAs($this->alice)
            ->getJson(route('messages.poll', [$conversation, 'after' => $first->id]))
            ->assertOk()
            ->assertSee('Second')
            ->assertDontSee('First');
    }
}
