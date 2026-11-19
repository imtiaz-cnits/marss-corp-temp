<div class="space-y-4 animate-pulse">
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-sky-100 dark:bg-slate-800"></div>
            <div class="space-y-2">
                <div class="h-5 w-44 rounded bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-3 w-64 rounded bg-slate-200 dark:bg-slate-800"></div>
            </div>
        </div>
        <div class="flex gap-2">
            <div class="h-9 w-28 rounded-full bg-emerald-200/70 dark:bg-emerald-900/40"></div>
            <div class="h-9 w-28 rounded-full bg-slate-200 dark:bg-slate-800"></div>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        @for($i = 0; $i < 4; $i++)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 space-y-3">
                <div class="flex justify-between">
                    <div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-800"></div>
                    <div class="w-7 h-7 rounded-lg bg-slate-200 dark:bg-slate-800"></div>
                </div>
                <div class="h-7 w-32 rounded bg-slate-200 dark:bg-slate-800"></div>
                <div class="h-3 w-28 rounded bg-slate-200 dark:bg-slate-800"></div>
            </div>
        @endfor
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 h-80"></div>
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 h-80"></div>
    </div>
</div>
