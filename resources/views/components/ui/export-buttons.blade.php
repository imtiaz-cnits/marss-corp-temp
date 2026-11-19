@props([
    'tableId' => 'printTable',
    'filename' => 'marss-export',
    'title' => 'MARSS CORPORATION',
])

<div class="flex items-center gap-1.5 flex-shrink-0">
    <button
        type="button"
        onclick="window.marssExport.copy('{{ $tableId }}')"
        title="Copy Table"
        class="w-[38px] h-[38px] rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-slate-100/80 dark:bg-slate-800 hover:bg-slate-200/80 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 flex items-center justify-center shadow-xs transition-colors cursor-pointer"
    >
        <i class="fa-regular fa-copy text-sm"></i>
    </button>
    <button
        type="button"
        onclick="window.marssExport.csv('{{ $tableId }}', '{{ $filename }}')"
        title="Export CSV"
        class="w-[38px] h-[38px] rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-emerald-50/80 dark:bg-slate-800 hover:bg-emerald-100 dark:hover:bg-slate-700 text-emerald-700 dark:text-emerald-400 flex items-center justify-center shadow-xs transition-colors cursor-pointer"
    >
        <i class="fa-solid fa-file-csv text-sm"></i>
    </button>
    <button
        type="button"
        onclick="window.marssExport.excel('{{ $tableId }}', '{{ $filename }}', '{{ addslashes($title) }}')"
        title="Export Excel"
        class="w-[38px] h-[38px] rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-green-50/80 dark:bg-slate-800 hover:bg-green-100 dark:hover:bg-slate-700 text-green-700 dark:text-green-400 flex items-center justify-center shadow-xs transition-colors cursor-pointer"
    >
        <i class="fa-solid fa-file-excel text-sm"></i>
    </button>
    <button
        type="button"
        onclick="window.marssExport.printTable('{{ $tableId }}', '{{ addslashes($title) }}')"
        title="Export PDF / Print"
        class="w-[38px] h-[38px] rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-rose-50/80 dark:bg-slate-800 hover:bg-rose-100 dark:hover:bg-slate-700 text-rose-700 dark:text-rose-400 flex items-center justify-center shadow-xs transition-colors cursor-pointer"
    >
        <i class="fa-solid fa-file-pdf text-sm"></i>
    </button>
    <button
        type="button"
        onclick="window.marssExport.printTable('{{ $tableId }}', '{{ addslashes($title) }}')"
        title="Print"
        class="w-[38px] h-[38px] rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-blue-50/80 dark:bg-slate-800 hover:bg-blue-100 dark:hover:bg-slate-700 text-blue-700 dark:text-blue-400 flex items-center justify-center shadow-xs transition-colors cursor-pointer"
    >
        <i class="fa-solid fa-print text-sm"></i>
    </button>
</div>
