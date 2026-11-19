import ApexCharts from 'apexcharts';
import flatpickr from 'flatpickr';
import JsBarcode from 'jsbarcode';
import { Html5Qrcode } from 'html5-qrcode';

window.ApexCharts = ApexCharts;
window.flatpickr = flatpickr;
window.JsBarcode = JsBarcode;
window.Html5Qrcode = Html5Qrcode;

// Single authoritative Dark/Light theme controller (eliminates 4 conflicting legacy scripts)
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

// Re-apply theme smoothly after every Livewire wire:navigate transition
document.addEventListener('livewire:navigated', () => {
    window.marssTheme.apply();
});

// Pure vanilla browser table export utilities (Copy TSV, CSV, Excel .xls, Print)
window.marssExport = {
    extractTableData(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return { headers: [], rows: [], footers: [] };
        const skipLast = table.dataset.exportSkipLast === 'true';
        const getCells = (tr) => {
            const cells = Array.from(tr.querySelectorAll('th, td'));
            const usable = skipLast ? cells.slice(0, -1) : cells;
            return usable.map((c) => (c.innerText || '').replace(/\s+/g, ' ').trim());
        };
        const headers = Array.from(table.querySelectorAll('thead tr')).map(getCells);
        const rows = Array.from(table.querySelectorAll('tbody tr'))
            .filter((tr) => !tr.dataset.emptyRow)
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
        const renderRows = (list, tag) => list.map((r) => `<tr>${r.map((c) => `<${tag}>${c}</${tag}>`).join('')}</tr>`).join('');
        win.document.write(`<!doctype html><html><head><meta charset="utf-8"><title>${title}</title><style>
            @page { size: A4 portrait; margin: 10mm; }
            body { font-family: 'Valley Sans', 'Baloo Da 2', Arial, sans-serif; color: #0f172a; margin: 0; padding: 12px; }
            .hdr { text-align: center; border-bottom: 2px solid #15803d; padding-bottom: 8px; margin-bottom: 14px; }
            .hdr h2 { margin: 0; color: #15803d; font-size: 20px; }
            .hdr p { margin: 4px 0 0; font-size: 12px; color: #475569; }
            table { width: 100%; border-collapse: collapse; font-size: 11px; }
            th { background: #15803d; color: #fff; padding: 6px; border: 1px solid #15803d; text-align: left; }
            td { padding: 5px 6px; border: 1px solid #cbd5e1; }
            tfoot th { background: #f1f5f9; color: #0f172a; border-color: #cbd5e1; }
            .sigs { display: flex; justify-content: space-between; margin-top: 48px; font-size: 11px; }
            .sigs div { border-top: 1px solid #0f172a; padding-top: 4px; width: 140px; text-align: center; }
        </style></head><body>
            <div class="hdr"><h2>MARSS CORPORATION</h2><p>${title} — Printed ${new Date().toLocaleString()}</p></div>
            <table><thead>${renderRows(headers, 'th')}</thead><tbody>${renderRows(rows, 'td')}</tbody><tfoot>${renderRows(footers, 'th')}</tfoot></table>
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
