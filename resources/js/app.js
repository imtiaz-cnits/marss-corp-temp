import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';
import JsBarcode from 'jsbarcode';
import { Html5Qrcode } from 'html5-qrcode';

window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.JsBarcode = JsBarcode;
window.Html5Qrcode = Html5Qrcode;

// Single authoritative Dark/Light theme controller
window.marssTheme = {
    isDark() {
        return localStorage.getItem('lightMode') === 'dark';
    },
    apply() {
        const dark = this.isDark();
        const mode = dark ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', dark);
        document.documentElement.setAttribute('light-mode', mode);
        if (document.body) {
            document.body.setAttribute('light-mode', mode);
            document.body.setAttribute('data-layout-mode', mode);
        }
    },
    toggle() {
        const next = this.isDark() ? 'light' : 'dark';
        localStorage.setItem('lightMode', next);
        localStorage.setItem('layout-mode', next);
        this.apply();
        window.dispatchEvent(new CustomEvent('marss-theme-changed', { detail: { dark: next === 'dark' } }));
    },
};

// Web Audio API POS Scan Beep Sound
window.marssPlayBeep = function () {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.setValueAtTime(1200, ctx.currentTime);
        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.00001, ctx.currentTime + 0.09);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start();
        osc.stop(ctx.currentTime + 0.09);
    } catch {}
};

// Interactive Table Column Sorting (works across all MARSS tables)
window.marssTableSort = {
    init() {
        document.querySelectorAll('table thead tr').forEach((tr) => {
            const ths = Array.from(tr.querySelectorAll('th'));
            ths.forEach((th, colIdx) => {
                if (th.dataset.sortBound === '1') return;
                const label = (th.textContent || '').trim().toLowerCase();
                if (!label || label === 'action' || label === 'actions' || label === 'img' || label === 'image' || label === 'logo') {
                    return;
                }
                th.dataset.sortBound = '1';
                th.dataset.sortable = 'true';
                if (!th.querySelector('.sort-icon')) {
                    const icon = document.createElement('span');
                    icon.className = 'sort-icon';
                    icon.innerHTML = '<i class="fa-solid fa-sort"></i>';
                    th.appendChild(icon);
                }
                th.addEventListener('click', () => {
                    const table = th.closest('table');
                    const tbody = table?.querySelector('tbody');
                    if (!tbody) return;
                    const rows = Array.from(tbody.querySelectorAll('tr')).filter(
                        (r) => !r.dataset.emptyRow && r.querySelectorAll('td').length > 1
                    );
                    if (rows.length <= 1) return;

                    const currentDir = th.dataset.sortDir === 'asc' ? 'desc' : 'asc';
                    ths.forEach((h) => {
                        delete h.dataset.sortDir;
                        const ic = h.querySelector('.sort-icon');
                        if (ic) ic.innerHTML = '<i class="fa-solid fa-sort"></i>';
                    });
                    th.dataset.sortDir = currentDir;
                    const activeIcon = th.querySelector('.sort-icon');
                    if (activeIcon) {
                        activeIcon.innerHTML =
                            currentDir === 'asc'
                                ? '<i class="fa-solid fa-sort-up"></i>'
                                : '<i class="fa-solid fa-sort-down"></i>';
                    }

                    const parseCell = (row) => {
                        const cell = row.querySelectorAll('td')[colIdx];
                        const raw = (cell?.innerText || '').trim();
                        const numeric = raw.replace(/[৳$,%\s,]/g, '');
                        if (numeric !== '' && !isNaN(Number(numeric)) && /^[-+]?\d+(\.\d+)?$/.test(numeric)) {
                            return { type: 'num', val: Number(numeric) };
                        }
                        return { type: 'str', val: raw.toLowerCase() };
                    };

                    rows.sort((a, b) => {
                        const va = parseCell(a);
                        const vb = parseCell(b);
                        let cmp = 0;
                        if (va.type === 'num' && vb.type === 'num') {
                            cmp = va.val - vb.val;
                        } else {
                            cmp = String(va.val).localeCompare(String(vb.val), undefined, { numeric: true });
                        }
                        return currentDir === 'asc' ? cmp : -cmp;
                    });

                    rows.forEach((r) => tbody.appendChild(r));
                });
            });
        });
    },
};

// Root-Level Collapsed Sidebar Flyout & Tooltip Portal (document.body, z-index: 999999)
// Completely immune to vertical scrollbar overflow clipping in BOTH Retail & Battery!
window.marssSidebarFlyouts = {
    _portal: null,
    _hideTimer: null,
    _scrollBound: false,
    ensurePortal() {
        if (!this._portal || !document.body.contains(this._portal)) {
            let existing = document.getElementById('marss-sidebar-portal');
            if (!existing) {
                existing = document.createElement('div');
                existing.id = 'marss-sidebar-portal';
                document.body.appendChild(existing);
            }
            this._portal = existing;
            this._portal.addEventListener('mouseenter', () => clearTimeout(this._hideTimer));
            this._portal.addEventListener('mouseleave', () => this.scheduleHide());
        }
        return this._portal;
    },
    hide() {
        clearTimeout(this._hideTimer);
        if (this._portal) {
            this._portal.innerHTML = '';
        }
    },
    scheduleHide() {
        clearTimeout(this._hideTimer);
        this._hideTimer = setTimeout(() => this.hide(), 140);
    },
    showForItem(item) {
        const aside = item.closest('aside');
        if (!aside || !aside.classList.contains('sidebar-is-collapsed')) {
            this.hide();
            return;
        }
        clearTimeout(this._hideTimer);
        const portal = this.ensurePortal();
        const rect = item.getBoundingClientRect();
        const asideRect = aside.getBoundingClientRect();

        const srcFlyout = item.querySelector('.sidebar-flyout');
        if (srcFlyout) {
            const box = document.createElement('div');
            box.className = 'portal-flyout' + (srcFlyout.classList.contains('flyout-wide') ? ' flyout-wide' : '');
            box.innerHTML = srcFlyout.innerHTML;
            box.style.left = `${asideRect.right + 10}px`;
            box.style.top = `${rect.top}px`;

            portal.innerHTML = '';
            portal.appendChild(box);

            const flyoutHeight = box.offsetHeight || 220;
            const maxTop = Math.max(8, window.innerHeight - flyoutHeight - 12);
            const finalTop = Math.max(8, Math.min(rect.top, maxTop));
            box.style.top = `${finalTop}px`;
            const caretTop = Math.max(12, Math.min(flyoutHeight - 24, (rect.top + rect.height / 2) - finalTop - 5));
            box.style.setProperty('--caret-top', `${caretTop}px`);

            box.querySelectorAll('a[href]').forEach((link) => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    this.hide();
                    if (href && window.Livewire && typeof window.Livewire.navigate === 'function') {
                        e.preventDefault();
                        window.Livewire.navigate(href);
                    }
                });
            });
            return;
        }

        const srcTooltip = item.querySelector('.sidebar-mini-tooltip');
        if (srcTooltip) {
            const tip = document.createElement('div');
            tip.className = 'portal-tooltip';
            tip.textContent = (srcTooltip.textContent || '').trim();
            tip.style.left = `${asideRect.right + 10}px`;
            tip.style.top = `${rect.top + rect.height / 2}px`;
            portal.innerHTML = '';
            portal.appendChild(tip);
        }
    },
    init() {
        const panel = document.getElementById('sidebar-main-panel');
        if (panel && !panel.dataset.portalScrollBound) {
            panel.dataset.portalScrollBound = '1';
            panel.addEventListener('scroll', () => this.hide(), { passive: true });
        }

        document.querySelectorAll('.sidebar-item, .sidebar-bottom-logout').forEach((item) => {
            if (item.dataset.flyoutBound === '1') return;
            item.dataset.flyoutBound = '1';
            item.addEventListener('mouseenter', () => this.showForItem(item));
            item.addEventListener('mouseleave', () => this.scheduleHide());
            item.addEventListener('focusin', () => this.showForItem(item));
        });
    },
};

// Global Custom Select Enhancer (MARSS Emerald Theme + Auto Search Filter when > 8 Options)
// Works automatically across ALL pages & ALL modals in Retail, Battery, and Shared!
window.marssCustomSelect = {
    _menu: null,
    _activeSelect: null,
    _activeTrigger: null,
    _observer: null,
    _observedBody: null,
    _globalBound: false,

    shouldEnhance(select) {
        if (!select || select.tagName !== 'SELECT') return false;
        if (select.multiple || select.size > 1) return false;
        if (select.classList.contains('no-custom-select') || select.classList.contains('flatpickr-monthDropdown-months')) return false;
        if (select.dataset.marssNative === '1') return false;
        if (select.closest('.flatpickr-calendar')) return false;
        return true;
    },

    getSelectedLabel(select) {
        const opt = select.options[select.selectedIndex];
        return opt ? (opt.textContent || '').trim() : 'Select...';
    },

    syncTrigger(select) {
        const trigger = select._marssTrigger;
        if (!trigger) return;
        const labelEl = trigger.querySelector('.marss-cs-trigger-label');
        if (labelEl) {
            labelEl.textContent = this.getSelectedLabel(select);
        }
        trigger.disabled = !!select.disabled;
        trigger.style.opacity = select.disabled ? '0.6' : '';
    },

    enhance(select) {
        if (!this.shouldEnhance(select)) return;
        if (select._marssEnhanced && select._marssTrigger && document.body.contains(select._marssTrigger)) {
            this.syncTrigger(select);
            return;
        }

        select._marssEnhanced = true;
        const origClass = select.className || '';
        const computed = window.getComputedStyle(select);
        const hasFullWidth = /\bw-full\b/.test(origClass) || computed.width === select.parentElement?.clientWidth + 'px';

        // Build trigger button preserving height/sizing classes cleanly
        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'marss-cs-trigger';

        // Copy height, padding-x, font-size, width, rounded classes from original select
        const carryTokens = origClass.split(/\s+/).filter((cls) =>
            /^(w-|min-w-|max-w-|h-|min-h-|text-|px-|rounded|flex-|col-span|sm:|md:|lg:)/.test(cls)
        );
        if (carryTokens.length) {
            trigger.className += ' ' + carryTokens.join(' ');
        }
        if (!/\bh-/.test(trigger.className)) {
            trigger.style.height = '38px';
        }
        if (!/\bpx-/.test(trigger.className)) {
            trigger.style.paddingLeft = '12px';
            trigger.style.paddingRight = '10px';
        }
        if (!/\btext-/.test(trigger.className)) {
            trigger.style.fontSize = '13px';
        }
        if (!hasFullWidth && !/\bw-/.test(trigger.className)) {
            trigger.style.width = 'auto';
            trigger.style.minWidth = '72px';
        }

        trigger.innerHTML = `<span class="marss-cs-trigger-label"></span><i class="fa-solid fa-chevron-down marss-cs-trigger-caret"></i>`;
        select._marssTrigger = trigger;
        select.classList.add('marss-cs-native-hidden');
        select.tabIndex = -1;
        select.insertAdjacentElement('afterend', trigger);

        this.syncTrigger(select);

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (this._activeSelect === select) {
                this.close();
            } else {
                this.open(select, trigger);
            }
        });

        select.addEventListener('change', () => this.syncTrigger(select));
    },

    open(select, trigger) {
        this.close();
        this._activeSelect = select;
        this._activeTrigger = trigger;
        trigger.classList.add('is-open');

        const menu = document.createElement('div');
        menu.id = 'marss-cs-portal-menu';
        const options = Array.from(select.options);
        const showSearch = options.length > 8;

        let searchInput = null;
        if (showSearch) {
            const searchWrap = document.createElement('div');
            searchWrap.className = 'marss-cs-search-wrap';
            searchWrap.innerHTML = `<div style="position:relative;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:11px;color:#94a3b8;pointer-events:none;"></i>
                <input type="text" class="marss-cs-search-input" placeholder="Search options..." autocomplete="off">
            </div>`;
            menu.appendChild(searchWrap);
            searchInput = searchWrap.querySelector('input');
        }

        const list = document.createElement('div');
        list.className = 'marss-cs-list';
        menu.appendChild(list);

        const renderOptions = (filterText = '') => {
            const q = filterText.trim().toLowerCase();
            list.innerHTML = '';
            let matched = 0;

            options.forEach((opt) => {
                const label = (opt.textContent || '').trim();
                if (q && !label.toLowerCase().includes(q)) return;
                matched++;

                const row = document.createElement('div');
                const isSelected = opt.value === select.value && opt.index === select.selectedIndex;
                row.className = 'marss-cs-option' + (isSelected ? ' is-selected' : '') + (opt.disabled ? ' is-disabled' : '');
                row.dataset.value = opt.value;
                row.innerHTML = `<span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${label.replace(/</g, '&lt;')}</span>`
                    + (isSelected ? `<i class="fa-solid fa-check" style="font-size:11px;color:#15803d;flex-shrink:0;"></i>` : '');

                if (!opt.disabled) {
                    row.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        select.value = opt.value;
                        select.selectedIndex = opt.index;
                        select.dispatchEvent(new Event('input', { bubbles: true }));
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        this.syncTrigger(select);
                        this.close();
                    });
                }
                list.appendChild(row);
            });

            if (matched === 0) {
                const empty = document.createElement('div');
                empty.className = 'marss-cs-empty';
                empty.textContent = 'No matching option found';
                list.appendChild(empty);
            }
        };

        renderOptions();
        if (searchInput) {
            searchInput.addEventListener('input', () => renderOptions(searchInput.value));
            searchInput.addEventListener('click', (e) => e.stopPropagation());
        }

        document.body.appendChild(menu);
        this._menu = menu;

        // Position floating menu relative to viewport (auto-flips up if near bottom)
        const rect = trigger.getBoundingClientRect();
        const menuWidth = Math.max(rect.width, showSearch ? 220 : 140);
        const clampedLeft = Math.min(rect.left, Math.max(8, window.innerWidth - menuWidth - 8));
        menu.style.width = `${Math.min(menuWidth, window.innerWidth - 16)}px`;
        menu.style.left = `${clampedLeft}px`;

        const menuHeight = Math.min(menu.offsetHeight || 260, 290);
        const spaceBelow = window.innerHeight - rect.bottom - 10;
        const spaceAbove = rect.top - 10;
        if (spaceBelow < menuHeight && spaceAbove > spaceBelow) {
            menu.style.top = `${Math.max(8, rect.top - menuHeight - 6)}px`;
        } else {
            menu.style.top = `${rect.bottom + 6}px`;
        }

        // Scroll selected item into view
        const selEl = list.querySelector('.is-selected');
        if (selEl) selEl.scrollIntoView({ block: 'nearest' });
        if (searchInput) setTimeout(() => searchInput.focus(), 20);
    },

    close() {
        if (this._menu) {
            this._menu.remove();
            this._menu = null;
        }
        if (this._activeTrigger) {
            this._activeTrigger.classList.remove('is-open');
            this._activeTrigger = null;
        }
        this._activeSelect = null;
    },

    scan(root = document) {
        root.querySelectorAll('select').forEach((sel) => this.enhance(sel));
        // Remove orphaned triggers if Livewire removed the select
        document.querySelectorAll('.marss-cs-trigger').forEach((btn) => {
            const prev = btn.previousElementSibling;
            if (!prev || prev.tagName !== 'SELECT') {
                btn.remove();
            }
        });
    },

    init() {
        this.scan(document);

        if (!this._globalBound) {
            this._globalBound = true;
            document.addEventListener('click', (e) => {
                if (!this._menu) return;
                if (this._menu.contains(e.target) || (this._activeTrigger && this._activeTrigger.contains(e.target))) return;
                this.close();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this._menu) this.close();
            });
            window.addEventListener('resize', () => this.close());
            window.addEventListener('scroll', (e) => {
                if (this._menu && e.target !== this._menu && !this._menu.contains(e.target)) {
                    this.close();
                }
            }, true);
        }

        if (document.body && this._observedBody !== document.body) {
            if (this._observer) {
                try { this._observer.disconnect(); } catch {}
            }
            let timer = null;
            this._observer = new MutationObserver(() => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    this.scan(document);
                    window.marssNoWrapAmounts.apply();
                }, 40);
            });
            this._observer.observe(document.body, { childList: true, subtree: true });
            this._observedBody = document.body;
        }
    },
};

// Guarantee Taka symbol (৳) & currency amounts NEVER wrap onto 2 lines in ANY table
window.marssNoWrapAmounts = {
    apply() {
        document.querySelectorAll('table td, table th').forEach((cell) => {
            if (cell.dataset.marssWrapChecked === '1') return;
            const txt = cell.textContent || '';
            if (txt.includes('৳') && !cell.querySelector('table')) {
                cell.style.whiteSpace = 'nowrap';
                cell.dataset.marssWrapChecked = '1';
            }
        });
    },
};

// Global Page & Table Placeholder Skeleton Controller (All Retail, Battery, Shared & POS Pages)
window.marssSkeleton = {
    _inlineTimer: null,
    _navSafetyTimer: null,
    showNav() {
        window.marssSidebarFlyouts.hide();
        window.marssCustomSelect.close();
        const mainSk = document.getElementById('marss-main-skeleton');
        const mainContent = document.getElementById('marss-main-content');
        if (mainSk) {
            mainSk.style.display = 'block';
            if (mainContent) mainContent.style.opacity = '0.18';
        }
        const posSk = document.getElementById('marss-pos-skeleton');
        if (posSk) {
            posSk.style.display = 'flex';
        }
        clearTimeout(this._navSafetyTimer);
        this._navSafetyTimer = setTimeout(() => this.hideNav(), 8000);
    },
    hideNav() {
        clearTimeout(this._navSafetyTimer);
        const mainSk = document.getElementById('marss-main-skeleton');
        const mainContent = document.getElementById('marss-main-content');
        if (mainSk) mainSk.style.display = 'none';
        if (mainContent) mainContent.style.opacity = '';
        const posSk = document.getElementById('marss-pos-skeleton');
        if (posSk) posSk.style.display = 'none';
    },
    showInlineTableSkeleton() {
        const container = document.querySelector('#marss-main-content .table-responsive, #marss-main-content .overflow-x-auto');
        if (!container || container.querySelector('.marss-inline-skeleton-overlay')) return;
        const cs = window.getComputedStyle(container);
        if (cs.position === 'static') {
            container.dataset.marssPosFixed = '1';
            container.style.position = 'relative';
        }
        const overlay = document.createElement('div');
        overlay.className = 'marss-inline-skeleton-overlay space-y-2.5';
        overlay.innerHTML = Array.from({ length: 6 })
            .map((_, idx) => `<div class="grid grid-cols-12 gap-2 items-center ${idx === 0 ? 'pb-1.5' : ''}">
                <div class="col-span-1 h-4 marss-skeleton"></div>
                <div class="col-span-3 h-4 marss-skeleton"></div>
                <div class="col-span-2 h-4 marss-skeleton"></div>
                <div class="col-span-2 h-4 marss-skeleton"></div>
                <div class="col-span-2 h-4 marss-skeleton"></div>
                <div class="col-span-2 h-4 marss-skeleton"></div>
            </div>`)
            .join('');
        container.appendChild(overlay);
    },
    hideInlineTableSkeleton() {
        clearTimeout(this._inlineTimer);
        document.querySelectorAll('.marss-inline-skeleton-overlay').forEach((el) => el.remove());
    },
    bindLivewireHooks() {
        if (window._marssSkeletonHooksBound || !window.Livewire) return;
        window._marssSkeletonHooksBound = true;
        window.Livewire.hook('commit', ({ respond, succeed, fail }) => {
            const shouldShowSkeleton = !!document.getElementById('marss-main-content') && !document.querySelector('.marss-modal-header');
            if (shouldShowSkeleton) {
                clearTimeout(this._inlineTimer);
                this._inlineTimer = setTimeout(() => this.showInlineTableSkeleton(), 120);
            }
            const cleanup = () => {
                if (shouldShowSkeleton) {
                    this.hideInlineTableSkeleton();
                }
                setTimeout(() => {
                    window.marssCustomSelect.scan(document);
                    window.marssNoWrapAmounts.apply();
                }, 25);
            };
            respond(cleanup);
            succeed(cleanup);
            fail(cleanup);
        });
    },
};

document.addEventListener('livewire:navigate', () => {
    window.marssSkeleton.showNav();
});

document.addEventListener('livewire:init', () => {
    window.marssSkeleton.bindLivewireHooks();
});

document.addEventListener('DOMContentLoaded', () => {
    window.marssTheme.apply();
    window.marssTableSort.init();
    window.marssSidebarFlyouts.init();
    window.marssCustomSelect.init();
    window.marssNoWrapAmounts.apply();
    window.marssSkeleton.hideNav();
    window.marssSkeleton.bindLivewireHooks();
});

document.addEventListener('livewire:navigated', () => {
    window.marssTheme.apply();
    window.marssTableSort.init();
    window.marssSidebarFlyouts.init();
    window.marssCustomSelect.close();
    window.marssCustomSelect.init();
    window.marssNoWrapAmounts.apply();
    window.marssSkeleton.hideNav();
    window.marssSkeleton.hideInlineTableSkeleton();
    window.marssSkeleton.bindLivewireHooks();
});

// Pure vanilla browser table export utilities (Copy TSV, CSV, Excel .xls, PDF/Print)
window.marssExport = {
    extractTableData(tableId) {
        const table = document.getElementById(tableId) || document.querySelector('table');
        if (!table) return { headers: [], rows: [], footers: [] };
        const skipLast = table.dataset.exportSkipLast === 'true';
        const getCells = (tr) => {
            const cells = Array.from(tr.querySelectorAll('th, td'));
            const lastText = (cells[cells.length - 1]?.innerText || '').trim().toLowerCase();
            const shouldDropLast = skipLast || lastText === 'action' || lastText === 'actions';
            const usable = shouldDropLast && cells.length > 1 ? cells.slice(0, -1) : cells;
            return usable.map((c) => (c.innerText || '').replace(/\s+/g, ' ').trim());
        };
        const headers = Array.from(table.querySelectorAll('thead tr')).map(getCells);
        const rows = Array.from(table.querySelectorAll('tbody tr'))
            .filter((tr) => !tr.dataset.emptyRow && tr.querySelectorAll('td').length > 1)
            .map(getCells);
        const footers = Array.from(table.querySelectorAll('tfoot tr')).map(getCells);
        return { headers, rows, footers };
    },
    copy(tableId) {
        const { headers, rows, footers } = this.extractTableData(tableId);
        const lines = [...headers, ...rows, ...footers].map((r) => r.join('\t')).join('\n');
        navigator.clipboard.writeText(lines).then(() => {
            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Table copied to clipboard!' } }));
        });
    },
    csv(tableId, filename = 'marss-report') {
        const { headers, rows, footers } = this.extractTableData(tableId);
        const escape = (v) => `"${String(v).replace(/"/g, '""')}"`;
        const csv = '\uFEFF' + [...headers, ...rows, ...footers].map((r) => r.map(escape).join(',')).join('\r\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        this._download(blob, `${filename}.csv`);
    },
    excel(tableId, filename = 'marss-report', title = 'MARSS CORPORATION') {
        const { headers, rows, footers } = this.extractTableData(tableId);
        const renderRows = (list, tag) => list.map((r) => `<tr>${r.map((c) => `<${tag} style="border:1px solid #cbd5e1;padding:6px;">${c}</${tag}>`).join('')}</tr>`).join('');
        const html = `<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8"></head><body><h3>${title}</h3><table style="border-collapse:collapse;"><thead>${renderRows(headers, 'th')}</thead><tbody>${renderRows(rows, 'td')}</tbody><tfoot>${renderRows(footers, 'th')}</tfoot></table></body></html>`;
        const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        this._download(blob, `${filename}.xls`);
    },
    printTable(tableId, title = 'MARSS CORPORATION Report') {
        const { headers, rows, footers } = this.extractTableData(tableId);
        const win = window.open('', '_blank', 'width=960,height=720');
        if (!win) {
            window.print();
            return;
        }
        const renderRows = (list, tag) => list.map((r) => `<tr>${r.map((c) => {
            const isAmt = String(c).includes('৳');
            return `<${tag}${isAmt ? ' class="amt"' : ''}>${c}</${tag}>`;
        }).join('')}</tr>`).join('');
        win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${title}</title><style>
            @page { size: A4 portrait; margin: 16px; }
            * { box-sizing: border-box; }
            html, body { font-family: 'Valley Sans', 'Baloo Da 2', Arial, sans-serif; color: #0f172a; margin: 0; padding: 0; background: #fff; }
            .print-layout { width: 100%; border-collapse: collapse; }
            .print-layout > tbody > tr > td { border: none; padding: 16px 16px 0 16px; vertical-align: top; }
            .print-layout > tfoot > tr > td { border: none; padding: 0; height: 76px; }
            .hdr { text-align: center; border-bottom: 2px solid #15803d; padding-bottom: 8px; margin-bottom: 14px; }
            .hdr h2 { margin: 0; color: #15803d; font-size: 20px; }
            .hdr p { margin: 4px 0 0; font-size: 12px; color: #475569; }
            table.data-table { width: 100%; border-collapse: collapse; font-size: 11px; }
            table.data-table th { background: #15803d; color: #fff; padding: 6px; border: 1px solid #15803d; text-align: left; -webkit-print-color-adjust: exact; print-color-adjust: exact; white-space: nowrap; }
            table.data-table td { padding: 5px 6px; border: 1px solid #cbd5e1; }
            table.data-table td.amt, table.data-table th.amt { white-space: nowrap; text-align: right; }
            table.data-table tfoot th { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; white-space: nowrap; }
            .sigs { position: fixed; bottom: 16px; left: 16px; right: 16px; display: flex; justify-content: space-between; font-size: 11px; background: #fff; padding-top: 12px; }
            .sigs div { border-top: 1.5px solid #0f172a; padding-top: 5px; width: 155px; text-align: center; font-weight: 700; }
            @media screen {
                body { padding: 16px; }
                .print-layout { min-height: calc(100vh - 32px); }
            }
        </style></head><body>
            <table class="print-layout">
                <tbody>
                    <tr>
                        <td>
                            <div class="hdr"><h2>MARSS CORPORATION</h2><p>${title} — Printed ${new Date().toLocaleString()}</p></div>
                            <table class="data-table"><thead>${renderRows(headers, 'th')}</thead><tbody>${renderRows(rows, 'td')}</tbody><tfoot>${renderRows(footers, 'th')}</tfoot></table>
                        </td>
                    </tr>
                </tbody>
                <tfoot><tr><td>&nbsp;</td></tr></tfoot>
            </table>
            <div class="sigs"><div>Cashier</div><div>Accountant</div><div>Authorized Signatory</div></div>
        </body></html>`);
        win.document.close();
        win.focus();
        setTimeout(() => win.print(), 300);
    },
    _download(blob, name) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = name;
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    },
};
