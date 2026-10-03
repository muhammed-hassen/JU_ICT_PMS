@props(['invalid' => false, 'size' => 'default'])

<input {{ $attributes->class([
    'block w-full rounded-lg border bg-card px-3 text-foreground shadow-xs transition-[border-color,box-shadow] duration-200 placeholder:text-muted-foreground/70 focus:outline-none focus:ring-[3px] disabled:opacity-60',
    'h-10 text-sm' => $size === 'default',
    'h-9 text-[13px]' => $size === 'sm',
    'h-12 text-[15px]' => $size === 'lg',
    'border-input focus:border-ring focus:ring-ring/15' => ! $invalid,
    'border-destructive focus:border-destructive focus:ring-destructive/15' => $invalid,
]) }}>
