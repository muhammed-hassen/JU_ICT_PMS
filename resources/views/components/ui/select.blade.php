{{-- Native select styled like the input (chevron comes from .ui-select in ju.css). Options go in the slot. --}}
@props(['invalid' => false, 'size' => 'default'])

<select {{ $attributes->class([
    'ui-select block w-full appearance-none rounded-lg border bg-card pl-3 pr-9 text-foreground shadow-xs transition-[border-color,box-shadow] duration-200 focus:outline-none focus:ring-[3px]',
    'h-10 text-sm' => $size !== 'sm',
    'h-9 text-[13px]' => $size === 'sm',
    'border-input focus:border-ring focus:ring-ring/15' => ! $invalid,
    'border-destructive focus:border-destructive focus:ring-destructive/15' => $invalid,
]) }}>{{ $slot }}</select>
