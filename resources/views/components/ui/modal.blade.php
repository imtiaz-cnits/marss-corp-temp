@props([
    'show' => false,
    'maxWidth' => 'md',
    'title' => '',
    'closeMethod' => null,
    'icon' => 'fa-solid fa-layer-group',
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
    class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto bg-slate-900/65 backdrop-blur-xs"
>
    <div
        class="relative w-full {{ $widthClass }} my-auto bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]"
        @click.stop
    >
        @if($title)
            {{-- Reference Project Sticky Emerald Green Header with White Title & Circular Red Close Button --}}
            <div class="marss-modal-header sticky top-0 z-20 flex items-center justify-between px-4 sm:px-5 py-3 shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-white/20 text-white flex items-center justify-center shrink-0">
                        <i class="{{ $icon }} text-sm text-white"></i>
                    </div>
                    <h3 class="text-base font-bold text-white truncate m-0">{{ $title }}</h3>
                </div>
                @if($closeMethod)
                    <button
                        type="button"
                        wire:click="{{ $closeMethod }}"
                        class="marss-modal-close-btn"
                        aria-label="Close"
                        title="Close"
                    >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                @endif
            </div>
        @endif

        <div class="marss-modal-body p-4 sm:p-5 overflow-y-auto flex-1">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="sticky bottom-0 z-20 flex items-center justify-end gap-2 px-4 sm:px-5 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 shrink-0">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
@endif
