<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Receipt - {{ $sale->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #111;
            font-size: 12px;
        }
        .sheet {
            max-width: 210mm;
            margin: 0 auto;
            padding: 10mm 12mm 12mm;
        }
        .brand-top {
            text-align: center;
            margin-bottom: 6px;
        }
        .brand-top img {
            max-height: 52px;
            width: auto;
        }
        .company-line {
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }
        .accent-p { color: #c41e3a; }
        .accent-t { color: #1a56a8; }
        .slogan {
            font-size: 11px;
            margin-top: 2px;
            font-style: italic;
        }
        .doc-title-bar {
            background: #1a56a8;
            color: #fff;
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 8px 6px;
            margin: 12px 0 10px;
        }
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-grid-row {
            display: table-row;
        }
        .info-grid-cell {
            display: table-cell;
            vertical-align: top;
            padding: 0;
        }
        .info-left {
            width: 58%;
            padding-right: 8px;
        }
        .kv {
            display: table;
            width: 100%;
            border: 1px solid #000;
            border-bottom: none;
        }
        .kv:last-of-type { border-bottom: 1px solid #000; }
        .kv-row { display: table-row; }
        .kv-label, .kv-val {
            display: table-cell;
            border-bottom: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }
        .kv-row:last-child .kv-label,
        .kv-row:last-child .kv-val { border-bottom: none; }
        .kv-label {
            font-weight: 700;
            width: 118px;
            border-right: 1px solid #000;
            background: #fafafa;
        }
        .note-box-wrap {
            border: 1px solid #000;
            height: 100%;
            min-height: 132px;
        }
        .note-label {
            font-weight: 700;
            padding: 5px 8px;
            border-bottom: 1px solid #000;
            background: #fafafa;
        }
        .note-body {
            padding: 10px 8px;
            text-align: center;
        }
        .note-banner {
            font-size: 15px;
            font-weight: 700;
            color: #c00000;
            letter-spacing: 0.5px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        table.items th,
        table.items td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: top;
        }
        table.items th {
            background: #e8e8e8;
            font-weight: 700;
            text-align: center;
            font-size: 11px;
        }
        table.items td:nth-child(1) { text-align: center; width: 36px; }
        table.items td:nth-child(2) { text-align: center; width: 44px; }
        table.items td:nth-child(3) { text-align: center; width: 52px; }
        table.items td.desc { text-align: left; }
        table.items td.color-col { text-align: left; width: 88px; font-weight: 600; }
        .tone-green { color: #0d6e3b; }
        .tone-brown { color: #5c3d1e; }
        .tone-free { color: #c00000; }
        tr.row-free td {
            color: #c00000;
            font-weight: 600;
        }
        .sign-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .sign-cell {
            display: table-cell;
            width: 25%;
            border: 1px solid #000;
            vertical-align: bottom;
            padding: 8px 6px 10px;
            text-align: center;
            min-height: 72px;
        }
        .sign-title { font-weight: 700; font-size: 11px; margin-bottom: 28px; }
        .sign-name { font-size: 11px; min-height: 32px; }
        .sign-role { font-size: 10px; color: #333; margin-top: 2px; }
        .sign-line { font-size: 10px; margin-top: 6px; border-top: 1px solid #000; padding-top: 4px; }

        @media print {
            @page { size: A4 portrait; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .sheet { padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
@php
    $receiptNote = ($sale->customer_pickup ?? false) ? 'PICK UP BY CLIENT' : 'DELIVERY TO CLIENT';
    $soRef = $sale->reference_number ? trim($sale->reference_number) : 'INHOUSE';
    $contact = trim((string) ($sale->delivery_contact_phone ?? ''));
    $deliveryWhen = $sale->delivery_date ? date('F j, Y', strtotime($sale->delivery_date)) : '—';

    $unitLabel = function ($product) {
        $u = $product->base_unit ?? 'PCS';
        $u = preg_replace('/^per\s+/i', '', $u);
        return strtoupper(trim($u)) ?: 'PCS';
    };

    $colorToneClass = function (?string $color) {
        $c = strtoupper(trim((string) $color));
        if ($c === '') {
            return '';
        }
        if (str_contains($c, 'FREE')) {
            return 'tone-free';
        }
        if (str_contains($c, 'GREEN')) {
            return 'tone-green';
        }
        if (str_contains($c, 'BROWN')) {
            return 'tone-brown';
        }
        return '';
    };

    $describeLine = function ($item) {
        $p = $item->product;
        $name = $p->name ?? '';
        $suffix = '';

        if (! is_null($item->cut_length)) {
            $cutUnit = $p->measurement_unit ?: 'm';
            $suffix = ' @ ' . number_format((float) $item->cut_length, 2) . ($cutUnit ? ' ' . $cutUnit : '');
        } elseif (($p->measurement_unit ?? '') === 'sq ft' && $p->default_width && $p->default_height) {
            $suffix = ' ' . $p->default_width . '×' . $p->default_height . ' sq ft';
        } elseif ($p->default_length) {
            $unit = $p->measurement_unit ?: preg_replace('/^per\s+/i', '', $p->base_unit ?? '');
            $suffix = ' @ ' . rtrim(rtrim(number_format((float) $p->default_length, 2), '0'), '.') . ($unit ? ' ' . $unit : '');
        }

        return trim($name . $suffix);
    };

    $itemsList = $sale->saleItems;
    $minRows = 10;
    $pad = max(0, $minRows - $itemsList->count());
@endphp

<div class="sheet">
    <div class="brand-top">
        <img src="{{ asset('images/PSTLogoDoc.png') }}" alt="Polytech Steel Trading">
        <div class="company-line">
            <span class="accent-p">P</span>OLYTECH STEEL <span class="accent-t">T</span>RADING
        </div>
        <div class="slogan">"We've got you COVERED"</div>
    </div>

    <div class="doc-title-bar">DELIVERY RECEIPT</div>

    <div class="info-grid">
        <div class="info-grid-row">
            <div class="info-grid-cell info-left">
                <div class="kv">
                    <div class="kv-row">
                        <div class="kv-label">Customer:</div>
                        <div class="kv-val">{{ $sale->delivered_to ?? '—' }}</div>
                    </div>
                    <div class="kv-row">
                        <div class="kv-label">Address:</div>
                        <div class="kv-val">{{ $sale->delivery_address ?? '—' }}</div>
                    </div>
                    <div class="kv-row">
                        <div class="kv-label">Delivery Date:</div>
                        <div class="kv-val">{{ $deliveryWhen }}</div>
                    </div>
                    <div class="kv-row">
                        <div class="kv-label">Contact Number:</div>
                        <div class="kv-val">{{ $contact !== '' ? $contact : '—' }}</div>
                    </div>
                    <div class="kv-row">
                        <div class="kv-label">SO:</div>
                        <div class="kv-val">{{ $soRef }}</div>
                    </div>
                </div>
            </div>
            <div class="info-grid-cell">
                <div class="note-box-wrap">
                    <div class="note-label">Note:</div>
                    <div class="note-body">
                        <div class="note-banner">{{ $receiptNote }}</div>
                        @if(trim((string) ($sale->delivery_note ?? '')))
                            <div style="margin-top:10px;font-size:11px;color:#222;text-align:left;white-space:pre-wrap;">{{ $sale->delivery_note }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Item #</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>DESCRIPTION</th>
                <th>Color</th>
            </tr>
        </thead>
        <tbody>
        @foreach($itemsList as $idx => $item)
            @php
                $p = $item->product;
                $isFree = (float) $item->unit_price <= 0 && (float) $item->total_price <= 0;
                $colorRaw = $p->color ?? '';
                $desc = $describeLine($item);
                if ($isFree && $desc === '') {
                    $desc = $p->name ?? '';
                }
            @endphp
            <tr class="{{ $isFree ? 'row-free' : '' }}">
                <td>{{ $idx + 1 }}</td>
                <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 2), '0'), '.') }}</td>
                <td>{{ $unitLabel($p) }}</td>
                <td class="desc">{{ $desc }}</td>
                <td class="color-col {{ $colorToneClass($colorRaw) }}">{{ $colorRaw !== '' ? $colorRaw : ($isFree ? 'FREE' : '—') }}</td>
            </tr>
        @endforeach
        @for($i = 0; $i < $pad; $i++)
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td class="desc"></td>
                <td class="color-col"></td>
            </tr>
        @endfor
        </tbody>
    </table>

    <div class="sign-grid">
        <div class="sign-cell">
            <div class="sign-title">Prepared By:</div>
            <div class="sign-name">Dhanril V. Bacasmot</div>
            <div class="sign-role">Office Engineer</div>
            <div class="sign-line">Name &amp; Signature</div>
        </div>
        <div class="sign-cell">
            <div class="sign-title">Driver:</div>
            <div class="sign-name">&nbsp;</div>
            <div class="sign-line">Name &amp; Signature</div>
        </div>
        <div class="sign-cell">
            <div class="sign-title">Helper:</div>
            <div class="sign-name">&nbsp;</div>
            <div class="sign-line">Name &amp; Signature</div>
        </div>
        <div class="sign-cell">
            <div class="sign-title">Receive By:</div>
            <div class="sign-name">&nbsp;</div>
            <div class="sign-line">Name &amp; Signature</div>
        </div>
    </div>
</div>

<script>
    window.onload = function () {
        window.print();
    };
</script>
</body>
</html>
