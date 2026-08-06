const editableSelector = 'input:not([type="hidden"]):not([type="file"]):not([type="button"]):not([type="submit"]):not([readonly]):not([disabled]), select:not([disabled]), textarea:not([readonly]):not([disabled])';

export function parseExcelClipboard(text) {
    const rows = [];
    let row = [];
    let cell = '';
    let quoted = false;

    for (let index = 0; index < text.length; index += 1) {
        const character = text[index];
        if (character === '"') {
            if (quoted && text[index + 1] === '"') {
                cell += '"';
                index += 1;
            } else {
                quoted = !quoted;
            }
        } else if (character === '\t' && !quoted) {
            row.push(cell);
            cell = '';
        } else if ((character === '\n' || character === '\r') && !quoted) {
            if (character === '\r' && text[index + 1] === '\n') index += 1;
            row.push(cell);
            rows.push(row);
            row = [];
            cell = '';
        } else {
            cell += character;
        }
    }

    row.push(cell);
    rows.push(row);
    if (rows.length > 1 && rows.at(-1).length === 1 && rows.at(-1)[0] === '') rows.pop();
    return rows;
}

export function normalizeExcelNumber(value) {
    let normalized = String(value).trim().replace(/\u00a0/g, '').replace(/\s/g, '');
    if (normalized === '') return '';

    const comma = normalized.lastIndexOf(',');
    const dot = normalized.lastIndexOf('.');
    if (comma >= 0 && dot >= 0) {
        const decimalSeparator = comma > dot ? ',' : '.';
        normalized = normalized.replace(decimalSeparator === ',' ? /\./g : /,/g, '');
        if (decimalSeparator === ',') normalized = normalized.replace(',', '.');
    } else if (comma >= 0) {
        normalized = normalized.replace(/\./g, '').replace(',', '.');
    }
    return normalized;
}

export function normalizeExcelDate(value) {
    const text = String(value).trim();
    const match = text.match(/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/);
    if (!match) return text;
    const [, day, month, year] = match;
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
}

function controlsByRow(table) {
    return [...table.tBodies]
        .flatMap((body) => [...body.rows])
        .map((row) => [...row.querySelectorAll(editableSelector)])
        .filter((controls) => controls.length > 0);
}

function addGridSpace(table, requiredRows, requiredColumns) {
    let matrix = controlsByRow(table);
    const direction = table.dataset.excelGrow;
    if (!direction) return matrix;

    const configuredButton = table.dataset.excelAdd
        ? document.querySelector(table.dataset.excelAdd)
        : null;
    const button = direction === 'columns'
        ? table.closest('.test-section')?.querySelector('.add-observation') || configuredButton
        : configuredButton;

    if (!button) return matrix;
    let safety = 100;
    while (safety > 0 && ((direction === 'rows' && matrix.length < requiredRows)
        || (direction === 'columns' && Math.max(0, ...matrix.map((row) => row.length)) < requiredColumns))) {
        button.click();
        matrix = controlsByRow(table);
        safety -= 1;
    }
    return matrix;
}

function assignValue(control, value) {
    let normalized = value;
    if (control.type === 'number' || control.type === 'range') normalized = normalizeExcelNumber(value);
    if (control.type === 'date') normalized = normalizeExcelDate(value);

    if (control instanceof HTMLSelectElement) {
        const wanted = String(normalized).trim().toLocaleLowerCase('id');
        const option = [...control.options].find((item) => item.value.trim().toLocaleLowerCase('id') === wanted
            || item.textContent.trim().toLocaleLowerCase('id') === wanted);
        if (option) control.value = option.value;
    } else if (control.type === 'checkbox') {
        control.checked = ['1', 'ya', 'yes', 'true', 'aktif'].includes(String(normalized).trim().toLowerCase());
    } else {
        control.value = normalized;
    }

    control.dispatchEvent(new Event('input', { bubbles: true }));
    control.dispatchEvent(new Event('change', { bubbles: true }));
}

function notifyPaste(count, skipped) {
    document.querySelector('.excel-paste-notice')?.remove();
    const notice = document.createElement('div');
    notice.className = `excel-paste-notice alert ${skipped ? 'alert-warning' : 'alert-success'} shadow-sm py-2 px-3`;
    notice.setAttribute('role', 'status');
    notice.textContent = skipped
        ? `${count} sel ditempel; ${skipped} sel tidak muat di tabel.`
        : `${count} sel berhasil ditempel dari Excel.`;
    document.body.appendChild(notice);
    window.setTimeout(() => notice.remove(), 3500);
}

function pasteIntoTable(event) {
    const start = event.target.closest(editableSelector);
    const table = start?.closest('table[data-excel-paste]');
    if (!table) return;

    const clipboard = event.clipboardData?.getData('text/plain');
    if (clipboard === undefined || clipboard === null) return;

    const values = parseExcelClipboard(clipboard);
    let matrix = controlsByRow(table);
    const startRow = matrix.findIndex((row) => row.includes(start));
    const startColumn = startRow >= 0 ? matrix[startRow].indexOf(start) : -1;
    if (startRow < 0 || startColumn < 0) return;

    event.preventDefault();
    matrix = addGridSpace(table, startRow + values.length,
        startColumn + Math.max(0, ...values.map((row) => row.length)));

    let pasted = 0;
    let skipped = 0;
    values.forEach((row, rowOffset) => row.forEach((value, columnOffset) => {
        const control = matrix[startRow + rowOffset]?.[startColumn + columnOffset];
        if (!control) {
            skipped += 1;
            return;
        }
        assignValue(control, value);
        pasted += 1;
    }));
    notifyPaste(pasted, skipped);
}

if (typeof document !== 'undefined') {
    document.addEventListener('paste', pasteIntoTable);

    const persistedForms = [...document.querySelectorAll('form[method="post"], form[method="POST"]')];
    let dirty = false;
    persistedForms.forEach((form) => {
        form.addEventListener('input', () => { dirty = true; });
        form.addEventListener('change', () => { dirty = true; });
        form.addEventListener('submit', () => {
            dirty = false;
            form.querySelectorAll('button[type="submit"], button:not([type])').forEach((button) => {
                button.disabled = true;
                button.dataset.originalHtml = button.innerHTML;
                button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Menyimpan...';
            });
        });
    });
    window.addEventListener('beforeunload', (event) => {
        if (!dirty) return;
        event.preventDefault();
        event.returnValue = '';
    });
}
