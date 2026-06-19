/**
 * PST — optional cut size inputs for SQ / PO line items (quote & order only; no stock logic).
 */
(function (global) {
    'use strict';

    function normalizeCutUnit(u) {
        if (!u) return null;
        const s = String(u).toLowerCase().trim();
        if (s === 'inch' || s === 'inches' || s === 'in') return 'inches';
        if (s === 'foot' || s === 'feet' || s === 'ft') return 'ft';
        if (s === 'millimeter' || s === 'millimeters' || s === 'mm') return 'mm';
        if (s === 'centimeter' || s === 'centimeters' || s === 'cm') return 'cm';
        if (s === 'meter' || s === 'meters' || s === 'm') return 'm';
        if (s === 'sq ft' || s === 'sqft') return 'sq ft';
        return String(u).trim();
    }

    function linearToInches(value, from) {
        const f = normalizeCutUnit(from);
        const v = Number(value);
        if (Number.isNaN(v)) return 0;
        if (f === 'inches') return v;
        if (f === 'ft') return v * 12;
        if (f === 'mm') return v / 25.4;
        if (f === 'cm') return v / 2.54;
        if (f === 'm') return v * 39.3700787;
        return v;
    }

    function inchesToLinear(inches, to) {
        const t = normalizeCutUnit(to);
        if (t === 'inches') return inches;
        if (t === 'ft') return inches / 12;
        if (t === 'mm') return inches * 25.4;
        if (t === 'cm') return inches * 2.54;
        if (t === 'm') return inches / 39.3700787;
        return inches;
    }

    function convertLinear(value, from, to) {
        return inchesToLinear(linearToInches(Number(value), from), to);
    }

    function productLinearStorageUnit(product) {
        const mu = normalizeCutUnit(product && product.measurement_unit);
        if (mu === 'sq ft') return 'ft';
        if (mu && !['kg', 'g', 'liter', 'ml', 'pail', 'gallon'].includes(mu)) return mu;
        return 'ft';
    }

    function allowedCutUnitsForProduct(product) {
        const storage = productLinearStorageUnit(product);
        const mu = normalizeCutUnit(product && product.measurement_unit);
        let units = [storage];
        if (mu && mu !== storage && mu !== 'sq ft') units.push(mu);
        if (mu === 'sq ft' || storage === 'ft') {
            units.push('inches', 'ft');
        } else if (storage === 'inches' || mu === 'inches') {
            units.push('ft');
        } else if (['mm', 'cm', 'm'].includes(storage)) {
            units.push('inches', 'ft');
        }
        return [...new Set(units.map(normalizeCutUnit).filter((u) => u && u !== 'sq ft'))];
    }

    function productCutMeasurementLabel(unit) {
        const n = normalizeCutUnit(unit);
        if (n === 'inches') return 'inches';
        if (n === 'ft') return 'ft';
        if (n === 'mm') return 'mm';
        if (n === 'cm') return 'cm';
        if (n === 'm') return 'm';
        return unit ? String(unit).trim() : '';
    }

    function productDimensionInCutUnit(product, dim, cutUnit) {
        const storage = productLinearStorageUnit(product);
        const key = dim === 'length' ? 'default_length' : (dim === 'width' ? 'default_width' : 'default_height');
        const val = product[key];
        if (val == null || val === '') return null;
        return convertLinear(Number(val), storage, cutUnit);
    }

    function roundDim(n) {
        return Math.round(Number(n) * 10000) / 10000;
    }

    function hasCutValues(cut) {
        if (!cut) return false;
        return (cut.cut_length > 0) || (cut.cut_width > 0) || (cut.cut_height > 0);
    }

    /**
     * Validate cut dimensions against a full product or remainder limits.
     * Full stock: cut must be strictly less than the piece (no full-length cut).
     * Remainder: cut cannot exceed what is left (equal allowed).
     */
    function validateCut(cut, product, opts) {
        opts = opts || {};
        if (!hasCutValues(cut)) {
            return { ok: true };
        }

        const hasW = (cut.cut_width || 0) > 0;
        const hasH = (cut.cut_height || 0) > 0;
        if (hasW !== hasH) {
            return { ok: false, message: 'Enter both width and height for a sheet cut, or leave both empty.' };
        }

        const cutUnit = normalizeCutUnit(cut.cut_measurement_unit)
            || (product ? productLinearStorageUnit(product) : null)
            || 'ft';
        const unitLbl = productCutMeasurementLabel(cutUnit);
        const allowEqual = opts.allowEqualToLimit === true;
        const limits = opts.limits || null;

        const check = (val, max, label) => {
            if (val == null || val <= 0 || max == null || max <= 0) return null;
            const bad = allowEqual ? (val > max) : (val >= max);
            if (!bad) return null;
            const cmp = allowEqual ? 'cannot exceed' : 'must be less than';
            return `Cut ${label} ${cmp} ${roundDim(max)} ${unitLbl}.`;
        };

        if (limits) {
            const e1 = check(cut.cut_length, limits.length, 'length');
            if (e1) return { ok: false, message: e1 };
            const e2 = check(cut.cut_width, limits.width, 'width');
            if (e2) return { ok: false, message: e2 };
            const e3 = check(cut.cut_height, limits.height, 'height');
            if (e3) return { ok: false, message: e3 };
            return { ok: true };
        }

        if (!product) {
            return { ok: true };
        }

        if (cut.cut_length > 0 && product.default_length) {
            const max = productDimensionInCutUnit(product, 'length', cutUnit);
            const err = check(cut.cut_length, max, 'length');
            if (err) return { ok: false, message: err };
        }
        if (cut.cut_width > 0 && product.default_width) {
            const max = productDimensionInCutUnit(product, 'width', cutUnit);
            const err = check(cut.cut_width, max, 'width');
            if (err) return { ok: false, message: err };
        }
        if (cut.cut_height > 0 && product.default_height) {
            const max = productDimensionInCutUnit(product, 'height', cutUnit);
            const err = check(cut.cut_height, max, 'height');
            if (err) return { ok: false, message: err };
        }

        return { ok: true };
    }

    function maxCutInputAttr(max, unitLabel, strictLess) {
        if (max == null || max <= 0) return '';
        const shown = roundDim(max);
        const hint = strictLess
            ? `Must be less than ${shown} ${unitLabel}`
            : `Max ${shown} ${unitLabel}`;
        return ` max="${shown}" title="${hint}" data-cut-max="${shown}"`;
    }

    function isCuttable(product) {
        if (!product) return false;
        if (String(product.base_unit || '').toLowerCase() === 'per set') return false;
        return !!(product.default_length || product.default_width || product.default_height);
    }

    function formatDisplay(cut) {
        if (!cut) return '';
        const parts = [];
        if (cut.cut_length > 0) parts.push(cut.cut_length);
        if (cut.cut_width > 0) parts.push(cut.cut_width);
        if (cut.cut_height > 0) parts.push(cut.cut_height);
        if (!parts.length) return '';
        const u = cut.cut_measurement_unit ? ` ${cut.cut_measurement_unit}` : '';
        return parts.join(' × ') + u;
    }

    function readInline(container) {
        if (!container) {
            return { cut_length: null, cut_width: null, cut_height: null, cut_measurement_unit: null };
        }
        const num = (sel) => {
            const el = container.querySelector(sel);
            if (!el || el.value === '') return null;
            const n = parseFloat(el.value);
            return !Number.isNaN(n) && n > 0 ? n : null;
        };
        const unitEl = container.querySelector('.pst-cut-unit');
        const unit = unitEl && unitEl.value ? normalizeCutUnit(unitEl.value) : null;
        return {
            cut_length: num('.pst-cut-length'),
            cut_width: num('.pst-cut-width'),
            cut_height: num('.pst-cut-height'),
            cut_measurement_unit: unit,
        };
    }

    function renderFreeform(container, saved, onUnitChange) {
        if (!container) return;
        saved = saved || {};
        const units = ['ft', 'inches', 'm', 'mm', 'cm'];
        let cutUnit = saved.cut_measurement_unit ? normalizeCutUnit(saved.cut_measurement_unit) : 'ft';
        if (!units.includes(cutUnit)) cutUnit = 'ft';
        const unitOpts = units.map((u) =>
            `<option value="${u}" ${u === cutUnit ? 'selected' : ''}>${productCutMeasurementLabel(u)}</option>`
        ).join('');
        const val = (key) => (saved[key] != null && saved[key] !== '' ? ` value="${saved[key]}"` : '');
        container.innerHTML = `
            <select class="pst-cut-unit min-w-[4.5rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs">${unitOpts}</select>
            <input type="number" class="pst-cut-length min-w-[5rem] max-w-[7rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Length" step="0.01" min="0"${val('cut_length')}>
            <input type="number" class="pst-cut-width min-w-[5rem] max-w-[7rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Width" step="0.01" min="0"${val('cut_width')}>
            <input type="number" class="pst-cut-height min-w-[5rem] max-w-[7rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Height" step="0.01" min="0"${val('cut_height')}>
        `;
        const unitSel = container.querySelector('.pst-cut-unit');
        if (unitSel && typeof onUnitChange === 'function') {
            unitSel.addEventListener('change', function () {
                const cur = readInline(container);
                cur.cut_measurement_unit = normalizeCutUnit(this.value);
                onUnitChange(cur);
            });
        }
    }

    function renderInline(container, product, saved, onUnitChange) {
        if (!container) return;
        saved = saved || {};
        if (!isCuttable(product)) {
            container.innerHTML = '';
            return;
        }
        const units = allowedCutUnitsForProduct(product);
        let cutUnit = saved.cut_measurement_unit ? normalizeCutUnit(saved.cut_measurement_unit) : (units[0] || 'ft');
        if (!units.includes(cutUnit)) cutUnit = units[0] || 'ft';
        const unitLabel = productCutMeasurementLabel(cutUnit);
        const unitOpts = units.map((u) =>
            `<option value="${u}" ${u === cutUnit ? 'selected' : ''}>${productCutMeasurementLabel(u)}</option>`
        ).join('');

        const parts = [];
        if (product.default_length) {
            const max = productDimensionInCutUnit(product, 'length', cutUnit);
            const maxAttr = maxCutInputAttr(max, unitLabel, true);
            const val = saved.cut_length != null && saved.cut_length !== '' ? ` value="${saved.cut_length}"` : '';
            parts.push(`<input type="number" class="pst-cut-length min-w-[5rem] max-w-[7rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Length" step="0.01" min="0"${maxAttr}${val}>`);
        }
        if (product.default_width) {
            const max = productDimensionInCutUnit(product, 'width', cutUnit);
            const maxAttr = maxCutInputAttr(max, unitLabel, true);
            const val = saved.cut_width != null && saved.cut_width !== '' ? ` value="${saved.cut_width}"` : '';
            parts.push(`<input type="number" class="pst-cut-width min-w-[5rem] max-w-[7rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Width" step="0.01" min="0"${maxAttr}${val}>`);
        }
        if (product.default_height) {
            const max = productDimensionInCutUnit(product, 'height', cutUnit);
            const maxAttr = maxCutInputAttr(max, unitLabel, true);
            const val = saved.cut_height != null && saved.cut_height !== '' ? ` value="${saved.cut_height}"` : '';
            parts.push(`<input type="number" class="pst-cut-height min-w-[5rem] max-w-[7rem] rounded border border-gray-300 px-2 py-1.5 text-xs shadow-sm" placeholder="Height" step="0.01" min="0"${maxAttr}${val}>`);
        }

        container.innerHTML = `
            <select class="pst-cut-unit min-w-[4.5rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-xs">${unitOpts}</select>
            ${parts.join('')}
        `;

        const unitSel = container.querySelector('.pst-cut-unit');
        if (unitSel && typeof onUnitChange === 'function') {
            unitSel.addEventListener('change', function () {
                const cur = readInline(container);
                cur.cut_measurement_unit = normalizeCutUnit(this.value);
                onUnitChange(cur);
            });
        }
    }

    global.PstCutFields = {
        isCuttable,
        renderInline,
        renderFreeform,
        readInline,
        formatDisplay,
        validateCut,
        hasCutValues,
        productCutMeasurementLabel,
        productDimensionInCutUnit,
        normalizeCutUnit,
    };
})(typeof window !== 'undefined' ? window : globalThis);
