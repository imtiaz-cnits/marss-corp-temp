@props([
    'tableId' => 'printTable',
    'filename' => 'marss-export',
    'title' => 'MARSS CORPORATION',
])

<div class="flex items-center flex-wrap gap-1.5 w-full sm:w-auto no-print">
    <button
        type="button"
        onclick="window.marssExport.copy('{{ $tableId }}')"
        title="Copy table data"
        class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer"
    >
        <i class="fa-regular fa-copy text-slate-500"></i>
        <span>Copy</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.csv('{{ $tableId }}', '{{ $filename }}')"
        title="Export CSV file"
        class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer"
    >
        <i class="fa-solid fa-file-csv text-emerald-600"></i>
        <span>CSV</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.excel('{{ $tableId }}', '{{ $filename }}', '{{ addslashes($title) }}')"
        title="Export Excel file"
        class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer"
    >
        <i class="fa-solid fa-file-excel text-emerald-600"></i>
        <span>Excel</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.pdf('{{ $tableId }}', '{{ $filename }}', '{{ addslashes($title) }}')"
        title="Download PDF"
        class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer"
    >
        <i class="fa-solid fa-file-pdf text-rose-600"></i>
        <span>PDF</span>
    </button>
    <button
        type="button"
        onclick="window.marssExport.printTable('{{ $tableId }}', '{{ addslashes($title) }}')"
        title="Print table"
        class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/60 text-slate-700 dark:text-slate-200 text-xs font-bold inline-flex items-center gap-1.5 shadow-2xs transition-all cursor-pointer"
    >
        <i class="fa-solid fa-print text-sky-600"></i>
        <span>Print</span>
    </button>
</div>
