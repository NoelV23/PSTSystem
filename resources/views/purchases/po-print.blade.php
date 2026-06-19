<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order — {{ $purchaseOrder->po_number ?? ('#'.$purchaseOrder->id) }}</title>
    <style>
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 8mm 5mm 10mm 8mm;
            color: #111;
            font-size: 11px;
            background: #fff;
        }
        .sheet {
            width: 100%;
            max-width: 194mm;
            margin: 0 auto;
        }
        .doc-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .doc-header img {
            max-height: 72px;
            width: auto;
            max-width: 100%;
            display: block;
            margin: 0 auto 6px;
        }
        .po-banner {
            background-color: #1a56a8 !important;
            color: #fff !important;
            text-align: center;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.12em;
            padding: 10px 8px;
            margin: 12px 0 14px;
            width: 100%;
        }
        .meta-wrap {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .meta-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 4px 12px 4px 0;
        }
        .meta-col.right { padding: 4px 0 4px 12px; text-align: right; }
        .meta-label { font-weight: 700; }
        .form-num {
            font-size: 9px;
            color: #333;
            margin-bottom: 4px;
        }
        table.lines {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        table.lines th,
        table.lines td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: middle;
            font-size: 10px;
        }
        table.lines th {
            color: #b91c1c;
            font-weight: 700;
            text-align: center;
            background: #fff;
        }
        .num { text-align: right; }
        .cen { text-align: center; }
        .color-cell {
            font-weight: 700;
            color: #92400e;
        }
        .total-lm-row td {
            color: #b91c1c;
            font-weight: 700;
        }
        .pickup-note {
            text-align: center;
            color: #0a3d8a;
            font-size: 11px;
            margin: 12px 0 14px;
            font-style: italic;
        }
        .supplier-block {
            margin-bottom: 12px;
        }
        .supplier-block .meta-label { font-weight: 700; }
        .supplier-name { font-weight: 700; font-size: 12px; margin-top: 2px; }
        .grand-wrap {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        .grand-spacer { display: table-cell; width: 28%; }
        .grand-bar {
            display: table-cell;
            background-color: #fde047 !important;
            border: 1px solid #ca8a04;
            padding: 12px 14px;
            text-align: right;
            vertical-align: middle;
            width: 72%;
        }
        .grand-bar .lbl {
            font-weight: 700;
            font-size: 11px;
            display: block;
            margin-bottom: 4px;
        }
        .grand-bar .amt {
            font-weight: 700;
            font-size: 14px;
        }
        .sign {
            margin-top: 36px;
            width: 100%;
        }
        .sign-row { margin-top: 10px; font-size: 10px; }
        .sign-line {
            border-top: 1px solid #000;
            margin-top: 22px;
            padding-top: 4px;
            min-width: 160px;
            display: inline-block;
        }
        @media print {
            @page { size: A4 portrait; margin: 8mm 5mm 8mm 8mm; }
            body { padding: 0; margin: 0; }
            .sheet { max-width: none; width: 100%; }
            .po-banner { background-color: #1a56a8 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .grand-bar { background-color: #fde047 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
@php
    $branch = $purchaseOrder->branch;
    $items = $purchaseOrder->purchaseItems;
    $fmtPhp = fn ($n) => 'Php ' . number_format((float) $n, 2);
    $orderDate = $purchaseOrder->order_date
        ? \Illuminate\Support\Carbon::parse($purchaseOrder->order_date)
        : null;
    $unitLabel = function ($p, $line = null) {
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
    $lineTotalLm = function ($line) {
        if ($line->is_long_span) {
            return (float) $line->quantity;
        }
        $qty = (float) $line->quantity;
        $cutParts = array_values(array_filter([
            $line->cut_length,
            $line->cut_width,
            $line->cut_height,
        ], fn ($v) => $v !== null && (float) $v > 0));
        if (count($cutParts) === 1) {
            return $qty * (float) $cutParts[0];
        }
        $p = $line->product;
        if ($p) {
            $len = (float) ($p->default_length ?? 0);
            if ($len > 0 && $cutParts === []) {
                return $qty * $len;
            }
        }
        if (! $p && $line->custom_measurement) {
            if (preg_match('/^([\d.]+)/', trim((string) $line->custom_measurement), $m)) {
                $len = (float) $m[1];
                if ($len > 0 && $cutParts === []) {
                    return $qty * $len;
                }
            }
        }

        return null;
    };
    $sumTotalLm = 0.0;
    $sumLineTotal = 0.0;
    foreach ($items as $line) {
        $lm = $lineTotalLm($line);
        if ($lm !== null) {
            $sumTotalLm += $lm;
        }
        $sumLineTotal += (float) $line->quantity * (float) $line->cost_price;
    }
    $poGrandTotal = $sumLineTotal > 0 ? $sumLineTotal : (float) $purchaseOrder->total_cost;
    $formDisplay = str_pad((string) $purchaseOrder->id, 4, '0', STR_PAD_LEFT) . '-' . ($orderDate ? $orderDate->format('Y') : date('Y')) . '-' . str_pad((string) ($purchaseOrder->branch_id ?? 0), 4, '0', STR_PAD_LEFT);
@endphp

<div class="sheet">
<div class="doc-header">
    <img src="{{ asset('images/PSTLogoDoc.png') }}" alt="Polytech Steel Trading" width="420" height="90">
</div>

<div class="po-banner">PURCHASE ORDER</div>

<div class="meta-wrap">
    <div class="meta-col">
        <div><span class="meta-label">Customer:</span> {{ $branch?->name ?? '—' }}</div>
        <div style="margin-top:6px;">
            <span class="meta-label">Address:</span>
            {{ $branch?->location ?? ($purchaseOrder->ship_to ?? '—') }}
        </div>
    </div>
    <div class="meta-col right">
        <div class="form-num">Form#: {{ $formDisplay }}</div>
        <div><span class="meta-label">DATE:</span> {{ $orderDate ? $orderDate->format('F j, Y') : '—' }}</div>
        <div style="margin-top:4px;"><span class="meta-label">P.O No.:</span> {{ $purchaseOrder->po_number ?? ('#'.$purchaseOrder->id) }}</div>
    </div>
</div>

<table class="lines">
    <thead>
        <tr>
            <th class="cen" style="width:36px;">QTY</th>
            <th style="width:44px;">UNIT</th>
            <th>PROFILE</th>
            <th style="width:48px;">GAUGE</th>
            <th style="width:48px;">WIDTH</th>
            <th style="width:48px;">LENGTH</th>
            <th style="width:56px;">TOTAL LM</th>
            <th style="width:72px;">COLOR</th>
            <th style="width:64px;">UNIT PRICE</th>
            <th style="width:76px;">TOTAL</th>
        </tr>
    </thead>
    <tbody>
    @foreach($items as $i => $line)
        @php
            $p = $line->product;
            $qty = (float) $line->quantity;
            $cutParts = array_values(array_filter([
                $line->cut_length,
                $line->cut_width,
                $line->cut_height,
            ], fn ($v) => $v !== null && (float) $v > 0));
            $cutStr = null;
            if ($cutParts !== []) {
                $cutStr = implode(' × ', array_map(
                    fn ($v) => rtrim(rtrim(number_format((float) $v, 3), '0'), '.'),
                    $cutParts
                ));
                if ($line->cut_measurement_unit) {
                    $cutStr .= ' '.$line->cut_measurement_unit;
                }
            }
            if (! $p) {
                $name = $line->lineDisplayName();
                $gauge = $line->custom_thickness ? rtrim(rtrim((string) $line->custom_thickness, ' '), '.') : '—';
                $width = $line->is_long_span && $line->printLongSpanCoverage()
                    ? $line->printLongSpanCoverage()
                    : '—';
                $length = $line->is_long_span
                    ? 'LS'
                    : ($line->custom_measurement ? rtrim(rtrim((string) $line->custom_measurement, ' '), '.') : '—');
                if ($cutStr && ! $line->is_long_span) {
                    $length = $cutStr.' (cut)';
                }
                $color = $line->custom_color ?: '—';
                $unit = $line->is_long_span ? 'lmtrs' : 'pcs';
                $totalLm = $lineTotalLm($line);
                $lineTotal = $qty * (float) $line->cost_price;
            } else {
                $w = $p->default_width;
                $l = $p->default_length;
                $h = $p->default_height;
                $name = $p->name;
                $gauge = ($h !== null && (float) $h > 0) ? rtrim(rtrim(number_format((float) $h, 2), '0'), '.') : '—';
                if ($line->is_long_span) {
                    $gauge = $line->printThicknessLabel() ?: $gauge;
                    $width = $line->printLongSpanCoverage() ?: (($w !== null && (float) $w > 0) ? rtrim(rtrim(number_format((float) $w, 2), '0'), '.') : '—');
                    $length = 'LS';
                    $unit = 'lmtrs';
                } else {
                    $width = ($w !== null && (float) $w > 0) ? rtrim(rtrim(number_format((float) $w, 2), '0'), '.') : '—';
                    $length = ($l !== null && (float) $l > 0) ? rtrim(rtrim(number_format((float) $l, 2), '0'), '.') : '—';
                    if ($cutStr) {
                        $length = $cutStr.' (cut)';
                    }
                    $unit = $unitLabel($p, $line);
                }
                $totalLm = $lineTotalLm($line);
                $lineTotal = $qty * (float) $line->cost_price;
                $color = $p->color ?: '—';
            }
        @endphp
        <tr>
            <td class="cen">{{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</td>
            <td class="cen">{{ $unit }}</td>
            <td>{{ $name }}</td>
            <td class="cen">{{ $gauge }}</td>
            <td class="cen">{{ $width }}</td>
            <td class="cen">{{ $length }}</td>
            <td class="num">{{ isset($totalLm) && $totalLm !== null ? rtrim(rtrim(number_format($totalLm, 2), '0'), '.') : '—' }}</td>
            <td class="color-cell">{{ $color }}</td>
            <td class="num">{{ rtrim(rtrim(number_format((float) $line->cost_price, 2), '0'), '.') }}</td>
            <td class="num">{{ $fmtPhp($lineTotal) }}</td>
        </tr>
    @endforeach
    <tr class="total-lm-row">
        <td colspan="6" class="num" style="text-align:right;padding-right:8px;">Total LM</td>
        <td class="num">{{ $sumTotalLm > 0 ? rtrim(rtrim(number_format($sumTotalLm, 2), '0'), '.') : '—' }}</td>
        <td colspan="2"></td>
        <td class="num" style="text-align:right;padding-right:6px;">PO Total</td>
        <td class="num">{{ $fmtPhp($poGrandTotal) }}</td>
    </tr>
    </tbody>
</table>

@if($purchaseOrder->ship_to || $purchaseOrder->note)
    <div class="pickup-note">
        @if($purchaseOrder->ship_to)
            {{ $purchaseOrder->ship_to }}
        @else
            {{ $purchaseOrder->note }}
        @endif
    </div>
@elseif($orderDate)
    <div class="pickup-note">
        To be pick-up on {{ $orderDate->format('F j, Y') }} ({{ $orderDate->format('l') }})
    </div>
@endif

<div class="supplier-block">
    <span class="meta-label">Supplier Name:</span>
    <div class="supplier-name">{{ $purchaseOrder->supplier_name }}</div>
</div>

<div class="grand-wrap">
    <div class="grand-spacer"></div>
    <div class="grand-bar">
        <span class="lbl">Total Grand Cost of Materials</span>
        <span class="amt">{{ $fmtPhp($poGrandTotal) }}</span>
    </div>
</div>

<div class="sign">
    <div class="sign-row"><strong>Cash receive for</strong> <span style="display:inline-block;width:14px;height:14px;border:1px solid #000;vertical-align:middle;margin-left:6px;"></span></div>
    <div class="sign-row" style="margin-top:20px;">
        <span class="sign-line" style="margin-right:48px;">Name</span>
        <span class="sign-line">Signature</span>
    </div>
</div>
</div>

<script>
    window.addEventListener('load', function () {
        setTimeout(function () {
            window.print();
        }, 300);
    });
</script>
</body>
</html>
