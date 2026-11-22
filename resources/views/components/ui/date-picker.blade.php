@props([
    'placeholder' => 'Select Date',
    'altFormat' => 'd/m/Y',
    'iconPosition' => 'left',
])

<div
    wire:ignore
    x-data="{
        value: @entangle($attributes->wire('model')),
        picker: null,
        init() {
            const initPicker = () => {
                if (typeof window.flatpickr === 'undefined') {
                    setTimeout(initPicker, 50);
                    return;
                }
                if (this.picker) this.picker.destroy();
                this.picker = window.flatpickr(this.$refs.input, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: '{{ $altFormat }}',
                    altInputClass: this.$refs.input.className,
                    monthSelectorType: 'static',
                    allowInput: true,
                    clickOpens: true,
                    disableMobile: true,
                    defaultDate: this.value || null,
                    onChange: (selectedDates, dateStr) => {
                        this.value = dateStr;
                    },
                    onClose: (selectedDates, dateStr) => {
                        if (dateStr !== this.value) {
                            this.value = dateStr;
                        }
                    }
                });
                if (this.picker && this.picker.altInput) {
                    this.picker.altInput.placeholder = '{{ $placeholder }}';
                }
            };
            this.$nextTick(initPicker);
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
    class="relative flex items-center w-full"
>
    @if($iconPosition === 'left')
        <i class="fa-regular fa-calendar text-emerald-600 dark:text-emerald-400 text-xs absolute left-3 pointer-events-none z-10"></i>
    @endif
    <input
        x-ref="input"
        type="text"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        {{ $attributes->whereDoesntStartWith('wire:model')->merge([
            'class' => ($iconPosition === 'right' ? 'pl-3 pr-8' : 'pl-8 pr-3') . ' h-[38px] w-full rounded-xl border-[1.5px] border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800/90 text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-100 hover:border-emerald-600 focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 outline-none cursor-pointer transition-colors'
        ]) }}
    >
    @if($iconPosition === 'right')
        <i class="fa-regular fa-calendar text-slate-400 dark:text-slate-400 text-xs absolute right-3 pointer-events-none z-10"></i>
    @endif
</div>
