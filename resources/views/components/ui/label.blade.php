@props(['size' => 'default'])

<label {{ $attributes->class(['mb-1.5 block font-semibold text-foreground', 'text-[13px]' => $size !== 'lg', 'text-[15px]' => $size === 'lg']) }}>{{ $slot }}</label>
