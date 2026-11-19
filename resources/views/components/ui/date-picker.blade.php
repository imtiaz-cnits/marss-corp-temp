@props([
    'placeholder' => 'Select Date',
])

<div
    wire:ignore
    x-data="{
        value: @entangle($attributes->wire('model')),
        picker: null,
        init() {
            this.picker = window.flatpickr(this.$refs.input, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                monthSelectorType: 'static',
                defaultDate: this.value || null,
                onChange: (selectedDates, dateStr) => {
                    this.value = dateStr;
                }
            });
            this.$watch('value', (val) => {
                if (this.picker && val !== this.picker.input.value) {
                    this.picker.setDate(val || null, false);
                }
            });
        },
        destroy() {
            if (this.picker) this.picker.destroy();
        }
    }"
    class="relative flex items-center"
>
    <i class="fa-regular fa-calendar text-emerald-600 dark:text-emerald-400 text-xs absolute left-3 pointer-events-none z-10"></i>
    <input
        x-ref="input"
        type="text"
        placeholder="{{ $placeholder }}"
        readonly
        {{ $attributes->whereDoesntStartWith('wire:model')->merge([
            'class' => 'h-[38px] w-full pl-8 pr-3 rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/90 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-100 hover:border-emerald-600 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 outline-none cursor-pointer transition-colors'
        ]) }}
    >
</div>
