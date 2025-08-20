<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Receipt - {{ $sale->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
        }
        .page-wrapper {
            display: flex;
            flex-direction: row;
            width: 100%;
            height: 100%;
        }
        .left-half {
            width: 50%;
            padding: 10mm;
            box-sizing: border-box;
        }
        .right-half {
            width: 50%;
            padding: 20mm;
            box-sizing: border-box;
        }
        .receipt {
            width: 100%;
            background: white;
            border: 1px solid #ccc;
            padding: 10px;
            box-sizing: border-box;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            font-size: 12px;
            color: #666;
        }
        .ref-number {
            font-size: 13px;
            color: #333;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 12px 0;
            text-transform: uppercase;
        }
        .delivery-info {
            margin-bottom: 20px;
        }
        .delivery-row {
            display: flex;
            margin-bottom: 8px;
        }
        .delivery-label {
            font-weight: bold;
            width: 100px;
            font-size: 13px;
        }
        .delivery-value {
            flex: 1;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            text-align: right;
        }
        .signatures {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 20px;
            padding-top: 5px;
            font-size: 12px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .page-wrapper {
                width: 100%;
                height: 100%;
            }
            .left-half {
                width: 50%;
                padding: 10mm;
            }
            .right-half {
                width: 50%;
            }
            .receipt {
                border: none;
                width: 100%;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="left-half"></div>
        <div class="right-half">
            <div class="receipt">
                <div class="header">
                    <div class="company-info">
                        <div class="company-name">{{ $sale->branch->name ?? 'RV Glass & Aluminum Supply' }}</div>
                        <div class="company-address">{{ $sale->branch->location ?? 'National Highway Mabua, Tandag City' }}</div>
                        <div class="company-address">TEL.No. {{ $sale->branch->phone ?? '09399277927' }}</div>
                    </div>
                    <div class="ref-number">
                        REF: {{ str_pad($sale->id, 7, '0', STR_PAD_LEFT) }}
                    </div>
                </div>

                <div class="title">DELIVERY RECEIPT</div>

                <div class="delivery-info">
                    <div class="delivery-row">
                        <div class="delivery-label">DELIVERED TO:</div>
                        <div class="delivery-value">{{ $sale->delivered_to ?? 'N/A' }}</div>
                    </div>
                    <div class="delivery-row">
                        <div class="delivery-label">ADDRESS:</div>
                        <div class="delivery-value">{{ $sale->delivery_address ?? 'N/A' }}</div>
                    </div>
                    <div class="delivery-row">
                        <div class="delivery-label">DATE:</div>
                        <div class="delivery-value">{{ date('F j, Y', strtotime($sale->delivery_date)) }}</div>
                    </div>
                    <div class="delivery-row">
                        <div class="delivery-label">SALE DATE:</div>
                        <div class="delivery-value">{{ date('F j, Y', strtotime($sale->created_at)) }}</div>
                    </div>
                    <div class="delivery-row">
                        <div class="delivery-label">SOLD BY:</div>
                        <div class="delivery-value">{{ $sale->user->name ?? 'N/A' }}</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>QTY</th>
                            <th>UNIT</th>
                            <th>DESCRIPTION</th>
                            <th>UNIT PRICE</th>
                            <th>PRICE</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleItems as $item)
                            <tr>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->product->base_unit }}</td>
                                <td>
                                    @php
                                        $p = $item->product;
                                        $measurementText = '';
                                        if (($p->measurement_unit === 'sq ft') && $p->default_width && $p->default_height) {
                                            $measurementText = $p->default_width . '×' . $p->default_height . ' sq ft';
                                        } elseif ($p->default_length) {
                                            $unit = $p->measurement_unit ?: (str_replace('per ', '', $p->base_unit));
                                            $measurementText = $p->default_length . ' ' . $unit;
                                        }
                                    @endphp
                                    {{ $p->name }}@if($p->color) {{ ' ' . $p->color }}@endif @if($measurementText) ({{ $measurementText }})@endif
                                </td>
                                <td>₱{{ number_format($item->unit_price, 2) }}</td>
                                <td>₱{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                        @endforeach
                        @if(($sale->delivery_fee ?? 0) > 0)
                            <tr>
                                <td>1</td>
                                <td>fee</td>
                                <td>Delivery Fee</td>
                                <td>₱{{ number_format($sale->delivery_fee, 2) }}</td>
                                <td>₱{{ number_format($sale->delivery_fee, 2) }}</td>
                            </tr>
                        @endif
                        <tr class="total-row">
                            <td colspan="4">TOTAL</td>
                            <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="signatures">
                    <div class="signature-box">
                        <div class="signature-line">Prepared by</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">Approved by</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line">Checked by</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
