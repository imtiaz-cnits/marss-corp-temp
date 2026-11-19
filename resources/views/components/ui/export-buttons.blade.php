@props([
    'tableId' => 'printTable',
    'filename' => 'marss-export',
    'title' => 'MARSS CORPORATION',
])

<div class="flex items-center flex-wrap gap-1.5 flex-shrink-0 no-print">
    <button
        type="button"
        onclick="window.marssExport.copy('{{ $tableId }}')"
        title="Copy table data"
        class="export-btn export-btn--copy"
    >
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
        <span>Copy</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.csv('{{ $tableId }}', '{{ $filename }}')"
        title="Export CSV table data"
        class="export-btn export-btn--csv"
    >
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        <span>CSV</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.excel('{{ $tableId }}', '{{ $filename }}', '{{ addslashes($title) }}')"
        title="Export Excel table data"
        class="export-btn export-btn--excel"
    >
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><polyline points="8 13 12 17 16 13"></polyline><line x1="12" y1="17" x2="12" y2="10"></line></svg>
        <span>Excel</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.printTable('{{ $tableId }}', '{{ addslashes($title) }}')"
        title="Download PDF / Print"
        class="export-btn export-btn--pdf"
    >
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>
        <span>PDF</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.printTable('{{ $tableId }}', '{{ addslashes($title) }}')"
        title="Print table on A4"
        class="export-btn export-btn--print"
    >
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        <span>Print</span>
    </button>
</div>
