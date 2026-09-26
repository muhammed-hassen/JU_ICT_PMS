{{-- Budget and resources for one task (SRS 5.6). $task is a new or existing Task. --}}
@php
    $textareaClasses = 'block w-full rounded-lg border border-input bg-card px-3 py-2 text-sm text-foreground shadow-xs placeholder:text-muted-foreground/70 focus:border-ring focus:outline-none focus:ring-[3px] focus:ring-ring/15';
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-ui.label for="estimated_cost">Estimated Cost (ETB)</x-ui.label>
        <x-ui.input type="number" step="0.01" min="0" id="estimated_cost" name="estimated_cost" :value="old('estimated_cost', $task->estimated_cost)" placeholder="0.00" :invalid="$errors->has('estimated_cost')" />
        @error('estimated_cost')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
    </div>
    <div>
        <x-ui.label for="actual_cost">Actual Cost (ETB)</x-ui.label>
        <x-ui.input type="number" step="0.01" min="0" id="actual_cost" name="actual_cost" :value="old('actual_cost', $task->actual_cost)" placeholder="0.00" :invalid="$errors->has('actual_cost')" />
        @error('actual_cost')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <x-ui.label for="resources">Resources</x-ui.label>
    <textarea name="resources" id="resources" rows="2" placeholder="Equipment, licences or people this task needs" class="{{ $textareaClasses }}">{{ old('resources', $task->resources) }}</textarea>
    @error('resources')<p class="m-0 mt-1 text-[13px] text-destructive">{{ $message }}</p>@enderror
</div>
