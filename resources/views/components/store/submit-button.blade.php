<button type="submit" data-ripple {{ $attributes->class('relative flex h-11 w-full cursor-pointer items-center justify-center gap-2 overflow-hidden rounded-md bg-button px-5 text-sm font-semibold text-button-text transition hover:bg-button-hover active:scale-[0.98] disabled:cursor-wait disabled:opacity-70') }}>
    {{ $slot }}
</button>
