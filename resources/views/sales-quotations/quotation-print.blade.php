<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Quotation — {{ $quotation->quotation_number ?? ('#'.$quotation->id) }}</title>
    <style>
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 8mm 6mm 10mm 8mm;
            color: #111;
            font-size: 10px;
            background: #fff;
        }
        .sheet { width: 100%; max-width: 194mm; margin: 0 auto; }
        .doc-top {
            display: table;
            width: 100%;
            margin-bottom: 6px;
        }
        .doc-top-left { display: table-cell; width: 42%; vertical-align: top; }
        .doc-top-right {
            display: table-cell;
            width: 58%;
            vertical-align: top;
            text-align: right;
            font-size: 9px;
            line-height: 1.35;
            padding-left: 8px;
        }
        .doc-top-left img { max-height: 64px; width: auto; max-width: 100%; }
        .sq-banner {
            background-color: #1a56a8;
            color: #fff;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.08em;
            padding: 9px 6px;
            margin: 8px 0 10px;
        }
        .meta-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .meta-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 2px 6px 2px 0;
        }
        .meta-col.right { padding: 2px 0 2px 6px; }
        .field-table { width: 100%; border-collapse: collapse; }
        .field-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
            font-size: 10px;
        }
        .field-table .lbl {
            font-weight: 700;
            width: 88px;
            background: #f3f4f6;
            white-space: nowrap;
        }
        table.lines {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            table-layout: fixed;
        }
        table.lines th,
        table.lines td {
            border: 1px solid #000;
            padding: 4px 3px;
            vertical-align: middle;
            font-size: 9px;
            word-wrap: break-word;
        }
        table.lines th {
            color: #b91c1c;
            font-weight: 700;
            text-align: center;
            background: #fff;
        }
        .cat-row td {
            background: #b91c1c;
            color: #fff;
            font-weight: 700;
            text-align: center;
            font-size: 9px;
            letter-spacing: 0.04em;
        }
        .thick-row td {
            background: #facc15;
            color: #111;
            font-weight: 700;
            text-align: center;
            font-size: 9px;
            letter-spacing: 0.03em;
        }
        .num { text-align: right; }
        .cen { text-align: center; }
        .item-name { font-weight: 600; }
        .color-cell { font-weight: 700; color: #92400e; }
        tr.line-free td {
            color: #b91c1c;
            font-style: italic;
            font-weight: 600;
        }
        tr.line-free td.color-cell {
            color: #b91c1c;
        }
        tr.line-free td.amount-free {
            font-weight: 700;
            font-style: italic;
            letter-spacing: 0.04em;
        }
        tr.line-non-catalog td {
            font-weight: 600;
        }
        .subtotal-row td { font-weight: 700; }
        .bottom-wrap {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        .terms-col {
            display: table-cell;
            width: 52%;
            vertical-align: top;
            padding-right: 8px;
            font-size: 9px;
            line-height: 1.45;
        }
        .terms-col ol { margin: 0; padding-left: 16px; }
        .terms-col li { margin-bottom: 4px; }
        .totals-col {
            display: table-cell;
            width: 48%;
            vertical-align: top;
        }
        .totals-box { width: 100%; border-collapse: collapse; }
        .totals-box td {
            border: 1px solid #000;
            padding: 5px 8px;
            font-size: 10px;
        }
        .totals-box .lbl { font-weight: 700; text-align: left; }
        .totals-box .val { text-align: right; font-weight: 700; white-space: nowrap; }
        .totals-discount td { background: #dbeafe; }
        .grand-bar {
            background-color: #1a56a8;
            color: #fff;
            text-align: center;
            font-weight: 700;
            padding: 10px 8px;
            margin-top: 6px;
            font-size: 11px;
        }
        .grand-bar .amt { font-size: 14px; display: block; margin-top: 4px; }
        .sign-grid {
            display: table;
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .sign-cell {
            display: table-cell;
            width: 33.33%;
            border: 1px solid #000;
            vertical-align: bottom;
            text-align: center;
            padding: 8px 4px 10px;
            min-height: 70px;
        }
        .sign-title { font-weight: 700; font-size: 10px; margin-bottom: 24px; }
        .sign-name { font-size: 10px; min-height: 28px; }
        .sign-role { font-size: 9px; color: #333; margin-top: 2px; }
        @media print {
            @page { size: A4 portrait; margin: 8mm 5mm 8mm 8mm; }
            body { padding: 0; }
            .sheet { max-width: none; width: 100%; }
        }
    </style>
</head>
<body>
@php
    $branch = $quotation->branch;
    $items = $quotation->items;
    $fmtP = fn ($n) => 'P ' . number_format((float) $n, 2);
    $subtotal = (float) $quotation->subtotal;
    $discount = (float) $quotation->discount_amount;
    $delivery = max(0, (float) ($quotation->delivery_charge ?? 0));
    $afterDiscount = (float) $quotation->grand_total - (float) $quotation->tax_amount - $delivery;
    $quoteDate = $quotation->created_at ? \Illuminate\Support\Carbon::parse($quotation->created_at) : now();
    $quoteNo = $quotation->quotation_number ?? ('SQ-' . $quotation->id);

    $unitLabel = function ($p, $line = null) {
        if ($line && method_exists($line, 'displayLineUnit')) {
            return $line->displayLineUnit();
        }
        if ($line && $line->is_long_span) {
            return 'lmtrs';
        }
        if (! $p) {
            return 'pcs';
        }
        $u = strtolower(preg_replace('/^per\s+/i', '', (string) ($p->base_unit ?? '')) ?: 'pcs');
        if (in_array($u, ['m', 'meter', 'meters', 'metre', 'metres', 'length'], true)) {
            return 'lmtrs';
        }

        return $u;
    };

    $longSpanDimension = function ($line, $p) {
        $parts = [];
        $thick = $line->printThicknessLabel();
        if ($thick) {
            $parts[] = $thick;
        }
        $coverage = $line->printLongSpanCoverage();
        if ($coverage) {
            $parts[] = $coverage;
        }
        $parts[] = 'LS';

        return implode(' x ', $parts);
    };

    $lineDimension = function ($line, $p) {
        if ($line->is_long_span) {
            return $longSpanDimension($line, $p);
        }
        if (! $p) {
            return '—';
        }
        $parts = [];
        $t = trim((string) ($p->thickness ?? ''));
        if ($t !== '') {
            $parts[] = $t;
        }
        $mu = strtolower(trim((string) ($p->measurement_unit ?? '')));
        if ($mu === 'sq ft') {
            $w = $p->default_width ?? null;
            $h = $p->default_height ?? null;
            if ($w !== null && $w !== '' && (float) $w > 0 && $h !== null && $h !== '' && (float) $h > 0) {
                $parts[] = rtrim(rtrim(number_format((float) $w, 3), '0'), '.').'×'.rtrim(rtrim(number_format((float) $h, 3), '0'), '.').' sq ft';
            } elseif ($w !== null && $w !== '' && (float) $w > 0) {
                $parts[] = rtrim(rtrim(number_format((float) $w, 3), '0'), '.').' sq ft';
            }
        } elseif ($p->default_length != null && $p->default_length !== '' && (float) $p->default_length > 0) {
            $unit = trim((string) ($p->measurement_unit ?? ''));
            if ($unit === '') {
                $unit = strtolower(preg_replace('/^per\s+/i', '', (string) ($p->base_unit ?? '')) ?: '');
            }
            $ln = rtrim(rtrim(number_format((float) $p->default_length, 3), '0'), '.');
            $parts[] = $unit !== '' ? "{$ln} {$unit}" : $ln;
        }
        if ($parts === [] && $p->default_width != null && $p->default_width !== '' && (float) $p->default_width > 0 && $mu !== 'sq ft') {
            $wu = trim((string) ($p->measurement_unit ?? '')) ?: 'm';
            $parts[] = rtrim(rtrim(number_format((float) $p->default_width, 3), '0'), '.').' '.$wu;
        }

        return $parts ? implode(' · ', $parts) : '—';
    };

    $formatCut = function ($line) {
        $parts = array_values(array_filter([
            $line->cut_length,
            $line->cut_width,
            $line->cut_height,
        ], fn ($v) => $v !== null && (float) $v > 0));
        if ($parts === []) {
            return '';
        }
        $s = implode(' × ', array_map(
            fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.'),
            $parts
        ));
        if ($line->cut_measurement_unit) {
            $s .= ' '.$line->cut_measurement_unit;
        }

        return $s;
    };

    $resolveLineDim = function ($line) use ($lineDimension, $formatCut, $longSpanDimension) {
        $p = $line->product;
        if ($line->is_long_span) {
            return $longSpanDimension($line, $p);
        }
        if ($p) {
            $savedSpecs = array_filter([
                $line->custom_thickness ?? null,
                $line->custom_measurement ?? null,
            ]);
            $dim = count($savedSpecs)
                ? implode(' · ', $savedSpecs)
                : $lineDimension($line, $p);
            $cutTxt = $formatCut($line);
            if ($cutTxt !== '') {
                $dim = ($dim !== '—' ? $dim.' · ' : '').'Cut: '.$cutTxt;
            }

            return $dim;
        }
        if ($line->product_id) {
            return trim((string) ($line->description ?? '')) !== ''
                ? \Illuminate\Support\Str::limit(trim((string) $line->description), 120)
                : '—';
        }
        $specParts = array_filter([
            $line->custom_thickness ?? null,
            $line->custom_measurement ?? null,
        ]);
        $dim = count($specParts) ? implode(' · ', $specParts) : 'As quoted';
        $cutTxt = $formatCut($line);
        if ($cutTxt !== '') {
            $dim = ($dim !== 'As quoted' ? $dim.' · ' : '').'Cut: '.$cutTxt;
        }

        return $dim;
    };

    $groupedLines = [];
    foreach ($items as $line) {
        $cat = $line->printCategoryName();
        $thick = $line->printThicknessLabel() ?? '';
        $groupedLines[$cat][$thick][] = $line;
    }
    ksort($groupedLines);
    foreach ($groupedLines as $cat => $thickGroups) {
        ksort($thickGroups);
    }

    $defaultTerms = "1. 60% down payment upon confirmation of order; balance upon completion of delivery.\n2. Lead time: 4–6 working days upon receipt of down payment.\n3. Cancellation of confirmed orders may be subject to charges.\n4. Returns accepted only for manufacturing defects, subject to inspection.\n5. Prices are valid until the date indicated on this quotation.";
    $termsText = trim((string) ($quotation->terms ?? '')) ?: $defaultTerms;
    $termsLines = preg_split('/\r\n|\r|\n/', $termsText);
@endphp

<div class="sheet">
    <div class="doc-top">
        <div class="doc-top-left">
            <img src="{{ asset('images/PSTLogoDoc.png') }}" alt="Polytech Steel Trading">
        </div>
        <div class="doc-top-right">
            @if($branch?->location)
                {{ $branch->location }}<br>
            @else
                Poblacion, Barra, Opol, Misamis Oriental<br>
            @endif
            @if($branch?->phone)
                {{ $branch->phone }}
            @else
                0917-703-0702 / (088) 531-0427
            @endif
        </div>
    </div>

    <div class="sq-banner">SALES QUOTATION / SALES CONTRACT</div>

    <div class="meta-grid">
        <div class="meta-col">
            <table class="field-table">
                <tr><td class="lbl">Name</td><td>{{ $quotation->customer_name }}</td></tr>
                <tr><td class="lbl">Address</td><td>{{ $quotation->customer_address ?: '—' }}</td></tr>
                <tr><td class="lbl">Project</td><td>{{ $quotation->customer_company ?: '—' }}</td></tr>
                <tr><td class="lbl">Cellphone #</td><td>{{ $quotation->customer_phone ?: '—' }}</td></tr>
                <tr><td class="lbl">ATTENTION</td><td>—</td></tr>
            </table>
        </div>
        <div class="meta-col right">
            <table class="field-table">
                <tr><td class="lbl">Date</td><td>{{ $quoteDate->format('d-M-y') }}</td></tr>
                <tr><td class="lbl">S.O.</td><td>INHOUSE</td></tr>
                <tr><td class="lbl">Based On</td><td>GIVEN</td></tr>
                <tr><td class="lbl">Cel #</td><td>{{ $quotation->customer_phone ?: '—' }}</td></tr>
                <tr><td class="lbl">Email Add</td><td>{{ $quotation->customer_email ?: '—' }}</td></tr>
                <tr><td class="lbl">Quotation No.</td><td>{{ $quoteNo }}</td></tr>
            </table>
        </div>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:11%;">Qty. &amp; Unit</th>
                <th style="width:22%;">ITEM</th>
                <th style="width:10%;">COLOR</th>
                <th style="width:24%;">DIMENSION</th>
                <th style="width:14%;">PRICE/UNIT</th>
                <th style="width:14%;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedLines as $categoryName => $thicknessGroups)
                <tr class="cat-row">
                    <td colspan="6">{{ $categoryName }}</td>
                </tr>
                @foreach($thicknessGroups as $thicknessKey => $groupLines)
                    @if($thicknessKey !== '')
                        <tr class="thick-row">
                            <td colspan="6">{{ strtoupper($thicknessKey) }} THICKNESS</td>
                        </tr>
                    @endif
                    @foreach($groupLines as $line)
                        @php
                            $p = $line->product;
                            $isNonCatalog = $line->product_id === null;
                            $isFree = (bool) $line->is_free;
                            $qty = (float) $line->quantity;
                            $u = $unitLabel($p, $line);
                            $qtyDisplay = rtrim(rtrim(number_format($qty, 2), '0'), '.') . ' ' . $u;
                            $itemName = $p?->name ?? ($line->custom_item_name ?: $line->description);
                            $color = $p ? ($p->color ?: '—') : ($line->custom_color ?: '—');
                            $dim = $resolveLineDim($line);
                        @endphp
                        <tr @class(['line-non-catalog' => $isNonCatalog, 'line-free' => $isFree])>
                            <td class="cen">{{ $qtyDisplay }}</td>
                            <td class="item-name">{{ $itemName }}</td>
                            <td class="color-cell">{{ $color }}</td>
                            <td class="cen" style="font-size:8px;">{{ $dim }}</td>
                            <td class="num">{{ $fmtP($line->retail_unit_price ?? $line->unit_price) }}</td>
                            <td class="num amount-free">{{ $isFree ? 'FREE' : $fmtP($qty * (float) ($line->retail_unit_price ?? $line->unit_price)) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
            <tr class="subtotal-row">
                <td colspan="5" class="num" style="padding-right:8px;">Sub-total Amount</td>
                <td class="num">{{ $fmtP($subtotal) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="bottom-wrap">
        <div class="terms-col">
            <strong>Terms &amp; Conditions:</strong>
            <ol>
                @foreach($termsLines as $tl)
                    @if(trim($tl) !== '')
                        <li>{{ preg_replace('/^\d+\.\s*/', '', trim($tl)) }}</li>
                    @endif
                @endforeach
            </ol>
            @if($quotation->valid_until)
                <p style="margin-top:8px;"><strong>Valid until:</strong> {{ $quotation->valid_until->format('F j, Y') }}</p>
            @endif
            @if($quotation->notes)
                <p style="margin-top:6px;"><strong>Note:</strong> {{ $quotation->notes }}</p>
            @endif
        </div>
        <div class="totals-col">
            <table class="totals-box">
                <tr>
                    <td class="lbl">Total Costs Of Materials</td>
                    <td class="val">{{ $fmtP($subtotal) }}</td>
                </tr>
                @if($discount > 0)
                    <tr>
                        <td class="lbl">Less: Customer Discount</td>
                        <td class="val">{{ $fmtP($discount) }}</td>
                    </tr>
                @endif
                @if((float) $quotation->tax_rate > 0)
                    <tr>
                        <td class="lbl">Tax ({{ rtrim(rtrim(number_format((float) $quotation->tax_rate, 2), '0'), '.') }}%)</td>
                        <td class="val">{{ $fmtP($quotation->tax_amount) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="lbl">Delivery Charge{{ (float) ($quotation->delivery_charge ?? 0) > 0 ? ' (within CDO)' : '' }}</td>
                    <td class="val" style="{{ (float) ($quotation->delivery_charge ?? 0) > 0 ? '' : 'font-style:italic;' }}">
                        @if((float) ($quotation->delivery_charge ?? 0) > 0)
                            {{ $fmtP($quotation->delivery_charge) }}
                        @else
                            PICK UP
                        @endif
                    </td>
                </tr>
            </table>
            <div class="grand-bar">
                Grand Total Cost
                <span class="amt">{{ $fmtP($quotation->grand_total) }}</span>
            </div>
        </div>
    </div>

    <div class="sign-grid">
        <div class="sign-cell">
            <div class="sign-title">Confirmed by:</div>
            <div class="sign-name">{{ $quotation->customer_name }}</div>
            <div class="sign-role">Authorized Representative</div>
        </div>
        <div class="sign-cell">
            <div class="sign-title">Noted by:</div>
            <div class="sign-name">&nbsp;</div>
            <div class="sign-role">Proprietor / Owner</div>
        </div>
        <div class="sign-cell">
            <div class="sign-title">Prepared by:</div>
            <div class="sign-name">{{ $quotation->user->name ?? '—' }}</div>
            <div class="sign-role">{{ $branch->name ?? 'Office' }}</div>
        </div>
    </div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () { window.print(); }, 300);
    });
</script>
</body>
</html>
