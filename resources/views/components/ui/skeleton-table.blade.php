<div class="space-y-4 animate-pulse">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border-[1.5px] border-slate-300 dark:border-slate-700 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-6 w-48 rounded-lg bg-slate-200 dark:bg-slate-800"></div>
            </div>
            <div class="flex gap-2">
                <div class="h-9 w-9 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-9 w-9 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-9 w-28 rounded-xl bg-emerald-200/70 dark:bg-emerald-900/40"></div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 mb-4">
            <div class="h-9 flex-1 min-w-[200px] rounded-xl bg-slate-200 dark:bg-slate-800"></div>
            <div class="h-9 w-36 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
            <div class="h-9 w-36 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
        </div>
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="h-10 bg-emerald-700/80"></div>
            @for($i = 0; $i < 6; $i++)
                <div class="flex items-center gap-4 px-4 py-3 border-t border-slate-100 dark:border-slate-800">
                    <div class="h-4 w-8 rounded bg-slate-200 dark:bg-slate-800"></div>
                    <div class="h-4 flex-1 rounded bg-slate-200 dark:bg-slate-800"></div>
                    <div class="h-4 w-24 rounded bg-slate-200 dark:bg-slate-800"></div>
                    <div class="h-4 w-20 rounded bg-slate-200 dark:bg-slate-800"></div>
                    <div class="h-6 w-16 rounded-full bg-slate-200 dark:bg-slate-800"></div>
                </div>
            @endfor
        </div>
    </div>
</div>
