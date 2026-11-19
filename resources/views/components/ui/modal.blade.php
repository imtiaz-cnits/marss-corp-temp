@props([
    'show' => false,
    'maxWidth' => 'md',
    'title' => '',
    'closeMethod' => null,
])

@php
    $widthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-3xl',
        'xl' => 'max-w-5xl',
        'full-edit' => 'max-w-[920px]',
        default => 'max-w-lg',
    };
@endphp

@if($show)
<div
    x-data
    x-on:keydown.escape.window="{{ $closeMethod ? '$wire.' . $closeMethod . '()' : '' }}"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto bg-slate-900/65 backdrop-blur-xs"
>
    <div
        class="relative w-full {{ $widthClass }} my-auto bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-2xl overflow-hidden"
        @click.stop
    >
        @if($title)
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/60">
                <h3 class="text-base font-bold text-slate-800 dark:text-white">{{ $title }}</h3>
                @if($closeMethod)
                    <button
                        type="button"
                        wire:click="{{ $closeMethod }}"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200/70 dark:hover:bg-slate-700 transition-colors cursor-pointer"
                        aria-label="Close"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                @endif
            </div>
        @endif

        <div class="p-5 max-h-[80vh] overflow-y-auto">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex items-center justify-end gap-2 px-5 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/40">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
@endif
