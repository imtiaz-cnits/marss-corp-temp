@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';

$currentPage = $paginator->currentPage();
$lastPage = $paginator->lastPage();

// Smart page window matching reference project (e.g. 1 2 3 ... 9)
$startPage = max(1, $currentPage - 1);
$endPage = min($lastPage, $currentPage + 1);

if ($currentPage <= 2) {
    $startPage = 1;
    $endPage = min($lastPage, 3);
}
if ($currentPage >= $lastPage - 1) {
    $startPage = max(1, $lastPage - 2);
    $endPage = $lastPage;
}

$pageList = [];
if ($startPage > 1) {
    $pageList[] = 1;
    if ($startPage > 2) {
        $pageList[] = '...';
    }
}
for ($p = $startPage; $p <= $endPage; $p++) {
    $pageList[] = $p;
}
if ($endPage < $lastPage) {
    if ($endPage < $lastPage - 1) {
        $pageList[] = '...';
    }
    $pageList[] = $lastPage;
}
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-center sm:justify-end w-full">
            <div class="flex items-center gap-1 sm:gap-1.5 flex-nowrap justify-center max-w-full overflow-x-auto py-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <button type="button" disabled class="custom-pagination-btn pagination-nav-btn disabled" aria-disabled="true">
                        <svg class="w-3 h-3 me-0.5 inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        <span>Prev</span>
                    </button>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" class="custom-pagination-btn pagination-nav-btn" aria-label="{{ __('pagination.previous') }}">
                        <svg class="w-3 h-3 me-0.5 inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                        <span>Prev</span>
                    </button>
                @endif

                {{-- Smart Pagination Elements --}}
                @foreach ($pageList as $item)
                    @if ($item === '...')
                        <span class="px-1 text-slate-400 font-bold text-xs sm:text-sm">...</span>
                    @elseif ($item == $currentPage)
                        <button type="button" class="custom-pagination-btn active" disabled aria-current="page">
                            {{ $item }}
                        </button>
                    @else
                        <button type="button" wire:click="gotoPage({{ $item }}, '{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" class="custom-pagination-btn" aria-label="{{ __('Go to page :page', ['page' => $item]) }}">
                            {{ $item }}
                        </button>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}.after" class="custom-pagination-btn pagination-nav-btn" aria-label="{{ __('pagination.next') }}">
                        <span>Next</span>
                        <svg class="w-3 h-3 ms-0.5 inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                @else
                    <button type="button" disabled class="custom-pagination-btn pagination-nav-btn disabled" aria-disabled="true">
                        <span>Next</span>
                        <svg class="w-3 h-3 ms-0.5 inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </button>
                @endif
            </div>
        </nav>
    @endif
</div>
