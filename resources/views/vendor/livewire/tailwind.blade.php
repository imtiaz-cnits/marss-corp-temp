@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }
    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';
@endphp

<div>
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
        {{-- Left: Reference Project Display Info Box with Pulsing Emerald Dot --}}
        <div id="display-info">
            <div class="flex items-center gap-1.5 text-xs sm:text-sm font-medium text-slate-500 dark:text-slate-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                <span>Showing</span>
                <span class="display-info-box font-bold text-slate-800 dark:text-slate-100 px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">
                    {{ $paginator->firstItem() ?? 0 }} – {{ $paginator->lastItem() ?? 0 }}
                </span>
                <span>of</span>
                <span class="display-info-box font-bold text-emerald-700 dark:text-emerald-400 px-1.5 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-slate-700">
                    {{ $paginator->total() }}
                </span>
                <span>entries</span>
            </div>
        </div>

        {{-- Right: Reference Project Smart Pagination Controls (.custom-pagination-btn) --}}
        @if ($paginator->hasPages())
            <nav role="navigation" aria-label="Pagination Navigation" id="pagination" class="flex items-center gap-1 sm:gap-1.5 flex-nowrap justify-center max-w-full overflow-x-auto pb-0.5">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="custom-pagination-btn disabled">
                        <svg class="w-3 h-3 mr-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        Prev
                    </span>
                @else
                    <button
                        type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="custom-pagination-btn"
                    >
                        <svg class="w-3 h-3 mr-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        Prev
                    </button>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="px-1 text-slate-400 font-bold">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" aria-current="page" class="custom-pagination-btn active">
                                    {{ $page }}
                                </span>
                            @else
                                <button
                                    type="button"
                                    wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                    class="custom-pagination-btn"
                                >
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <button
                        type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        wire:loading.attr="disabled"
                        class="custom-pagination-btn"
                    >
                        Next
                        <svg class="w-3 h-3 ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                @else
                    <span class="custom-pagination-btn disabled">
                        Next
                        <svg class="w-3 h-3 ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                @endif
            </nav>
        @else
            <div id="pagination" class="flex items-center gap-1 sm:gap-1.5">
                <span class="custom-pagination-btn disabled">
                    <svg class="w-3 h-3 mr-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Prev
                </span>
                <span class="custom-pagination-btn active">1</span>
                <span class="custom-pagination-btn disabled">
                    Next
                    <svg class="w-3 h-3 ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </span>
            </div>
        @endif
    </div>
</div>
