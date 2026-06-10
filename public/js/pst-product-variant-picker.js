/**
 * PST — product variant narrowing for SQ / PO / Sales.
 * Groups rows by category + variant group (variant_group_label if set, else name),
 * then filters by color, thickness, and measurement.
 * thickness (dedicated `thickness` column, else legacy `default_width` when not sq ft),
 * and measurement (default_length + unit, or WxH for sq ft).
 */
(function (global) {
    'use strict';

    function measuringSqFt(p) {
        return ((p && p.measurement_unit) || '').toLowerCase() === 'sq ft';
    }

    /** Normalized thickness string from the explicit column, if any */
    function explicitThicknessRaw(p) {
        if (!p || p.thickness == null || p.thickness === '') return '';
        return String(p.thickness).trim();
    }

    /**
     * UI label for the thickness dropdown (e.g. "Thickness", "Gauge", "Wall").
     * When empty/null, defaults to "Thickness".
     */
    function thicknessSpecLabel(p) {
        if (!p || p.thickness_spec_label == null) return 'Thickness';
        const s = String(p.thickness_spec_label).trim();
        return s || 'Thickness';
    }

    function thicknessKey(p) {
        if (!p) return null;
        const raw = explicitThicknessRaw(p);
        if (raw) return 't:' + raw.toLowerCase().replace(/\s+/g, ' ');
        if (measuringSqFt(p)) return null;
        if (p.default_width == null || p.default_width === '') return null;
        return 'w:' + String(p.default_width);
    }

    function thicknessLabel(p) {
        if (!p) return '';
        const raw = explicitThicknessRaw(p);
        if (raw) return raw;
        if (measuringSqFt(p)) return '';
        if (!thicknessKey(p)) return '';
        const mu = (p.measurement_unit || '').trim();
        return mu ? `${p.default_width} ${mu}`.trim() : String(p.default_width);
    }

    function measurementKey(p) {
        if (!p) return '';
        if (measuringSqFt(p)) {
            const w = p.default_width ?? '';
            const h = p.default_height ?? '';
            return 'sqft:' + w + 'x' + h;
        }
        const l = p.default_length ?? '';
        const mu = (p.measurement_unit || (p.base_unit || '').toString().replace(/^per\s+/i, '') || '');
        return 'len:' + l + '|' + mu;
    }

    function measurementLabel(p) {
        if (!p) return '';
        if (measuringSqFt(p)) {
            const w = p.default_width;
            const h = p.default_height;
            if (w && h) return w + '×' + h + ' sq ft';
            if (w) return w + ' sq ft';
            if (h) return h + ' sq ft';
            return 'sq ft';
        }
        if (p.default_length != null && p.default_length !== '') {
            const mu = p.measurement_unit || String(p.base_unit || '').replace(/^per\s+/i, '') || '';
            return (String(p.default_length) + ' ' + mu).trim();
        }
        return String(p.base_unit || '—');
    }

    /** Public name for grouping / search: same on all variants = one dropdown row */
    function effectiveGroupName(p) {
        if (!p) return '';
        const g = p.variant_group_label != null && String(p.variant_group_label).trim() !== '' ? String(p.variant_group_label).trim() : '';
        if (g) return g;
        return String(p.name || '').trim();
    }

    function groupKey(p) {
        if (!p) return '';
        return String(p.category_id) + '\t' + effectiveGroupName(p).toLowerCase();
    }

    function groupLabel(sample) {
        if (!sample) return '';
        const cat = sample.category && sample.category.name ? ' · ' + sample.category.name : '';
        return effectiveGroupName(sample) + cat;
    }

    /**
     * @param {Array<{product: object}>} invRows
     * @param {string} query
     * @returns {Map<string, Array<{product: object}>>}
     */
    function groupsMatchingQuery(invRows, query) {
        const q = (query || '').trim().toLowerCase();
        const map = new Map();
        invRows.forEach(function (inv) {
            const p = inv.product;
            if (!p) return;
            const lab = (p.name || '').toLowerCase();
            const gname = effectiveGroupName(p).toLowerCase();
            const sku = (p.sku && String(p.sku).toLowerCase()) || '';
            const meas = measurementLabel(p).toLowerCase();
            const thick = explicitThicknessRaw(p).toLowerCase();
            const matches =
                !q ||
                lab.includes(q) ||
                gname.includes(q) ||
                sku.includes(q) ||
                meas.includes(q) ||
                thick.includes(q) ||
                (p.color && String(p.color).toLowerCase().includes(q));
            if (!matches) return;
            const k = groupKey(p);
            if (!map.has(k)) map.set(k, []);
            map.get(k).push(inv);
        });
        return map;
    }

    function distinctColors(invs) {
        const set = new Set();
        invs.forEach(function (inv) {
            const p = inv.product;
            set.add(p && p.color != null && p.color !== '' ? String(p.color) : '');
        });
        return Array.from(set).sort(function (a, b) {
            if (a === '' && b !== '') return -1;
            if (b === '' && a !== '') return 1;
            return String(a).localeCompare(String(b), undefined, { sensitivity: 'base' });
        });
    }

    function distinctThicknesses(invs) {
        const map = new Map();
        invs.forEach(function (inv) {
            const p = inv.product;
            const tk = thicknessKey(p);
            if (!tk) return;
            if (!map.has(tk)) map.set(tk, thicknessLabel(p));
        });
        return Array.from(map.entries()).map(function (e) {
            return { value: e[0], label: e[1] };
        });
    }

    function distinctMeasurements(invs) {
        const map = new Map();
        invs.forEach(function (inv) {
            const p = inv.product;
            const mk = measurementKey(p);
            if (!map.has(mk)) map.set(mk, measurementLabel(p));
        });
        return Array.from(map.entries()).map(function (e) {
            return { value: e[0], label: e[1] };
        });
    }

    /**
     * @param {Array} invs
     * @param {{ color?: string, thicknessValue?: string|null, measurementValue?: string|null }} f
     * Pass null/undefined for a dimension to skip filtering on it.
     */
    function narrowVariants(invs, f) {
        return invs.filter(function (inv) {
            const p = inv.product;
            if (!p) return false;
            if (f.color !== undefined && f.color !== null) {
                const pc = p.color != null && p.color !== '' ? String(p.color) : '';
                if (pc !== String(f.color)) return false;
            }
            if (f.thicknessValue) {
                if (thicknessKey(p) !== f.thicknessValue) return false;
            }
            if (f.measurementValue) {
                if (measurementKey(p) !== f.measurementValue) return false;
            }
            return true;
        });
    }

    global.PstProductVariantPicker = {
        measuringSqFt: measuringSqFt,
        effectiveGroupName: effectiveGroupName,
        thicknessKey: thicknessKey,
        thicknessLabel: thicknessLabel,
        thicknessSpecLabel: thicknessSpecLabel,
        measurementKey: measurementKey,
        measurementLabel: measurementLabel,
        groupKey: groupKey,
        groupLabel: groupLabel,
        groupsMatchingQuery: groupsMatchingQuery,
        distinctColors: distinctColors,
        distinctThicknesses: distinctThicknesses,
        distinctMeasurements: distinctMeasurements,
        narrowVariants: narrowVariants,
    };
})(typeof window !== 'undefined' ? window : globalThis);
