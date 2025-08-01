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
            padding: 20px;
            background: white;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 1px solid #ccc;
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
            font-size: 14px;
            color: #333;
        }
        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .delivery-info {
            margin-bottom: 20px;
        }
        .delivery-row {
            display: flex;
            margin-bottom: 10px;
        }
        .delivery-label {
            font-weight: bold;
            width: 120px;
        }
        .delivery-value {
            flex: 1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
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
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
        }
        @media print {
            body {
                padding: 0;
            }
            .receipt {
                border: none;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="company-info">
                <div class="company-name">{{ $sale->branch->name ?? 'RVJ GLASS AND ALUMINUM SUPPLY' }}</div>
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
                <div class="delivery-value">
                    @if($sale->delivery_address)
                        {{ $sale->delivery_address }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div class="delivery-row">
                <div class="delivery-label">DATE:</div>
                <div class="delivery-value">
                    {{ date('F j, Y', strtotime($sale->delivery_date)) }}
                </div>
            </div>
            @if($sale->delivery_note)
            <div class="delivery-row">
                <div class="delivery-label">NOTES:</div>
                <div class="delivery-value">{{ $sale->delivery_note }}</div>
            </div>
            @endif
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
                    @php
                        // Determine if item is cut
                        $isCut = $item->cut_length || $item->cut_width || $item->cut_height;
                        
                        // Determine unit based on product base_unit and cut status
                        if ($isCut) {
                            $unit = 'PC'; // Cut items always use PC (piece)
                        } else {
                            // For non-cut items, use the product's base_unit
                            if ($item->product->base_unit === 'per kg') {
                                $unit = 'KG';
                            } elseif ($item->product->base_unit === 'per liter') {
                                $unit = 'ltr';
                            } elseif ($item->product->base_unit === 'per sq ft') {
                                $unit = 'sq ft';
                            } elseif ($item->product->base_unit === 'per set') {
                                $unit = 'SET';
                            } elseif ($item->product->base_unit === 'per length') {
                                $unit = 'L';
                            } elseif ($item->product->base_unit === 'per feet') {
                                $unit = 'FT';
                            } elseif ($item->product->base_unit === 'per pc') {
                                $unit = 'PC';
                            } else {
                                $unit = 'PC'; // Default fallback
                            }
                        }
                        
                        // Build product description
                        $description = $item->product->name;
                        
                        // Add color if available
                        if ($item->product->color) {
                            $description .= ' ' . $item->product->color;
                        }
                        
                        // Add original dimensions for non-cut items
                        if (!$isCut) {
                            if ($item->product->base_unit === 'per sq ft' && $item->product->default_width && $item->product->default_height) {
                                $description .= ' ' . $item->product->default_width . ' x ' . $item->product->default_height;
                            } elseif ($item->product->base_unit === 'per length' && $item->product->default_length) {
                                $description .= ' ' . $item->product->default_length . 'ft';
                            }
                        }
                        
                        // Add cut information with improved formatting
                        if ($isCut) {
                            $cutDimensions = [];
                            
                            // Handle length cuts (most common for per length products)
                            if ($item->cut_length) {
                                $cutDimensions[] = $item->cut_length . 'ft';
                            }
                            
                            // Handle width and height cuts (for sheet products)
                            if ($item->cut_width) {
                                $cutDimensions[] = $item->cut_width . 'w';
                            }
                            if ($item->cut_height) {
                                $cutDimensions[] = $item->cut_height . 'h';
                            }
                            
                            if (!empty($cutDimensions)) {
                                $description .= ' CUT ' . implode(' x ', $cutDimensions);
                            }
                        }
                        
                        // Add SKU if available
                        if ($item->product->sku) {
                            $description .= ' (SKU: ' . $item->product->sku . ')';
                        }
                    @endphp
                    <tr>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $unit }}</td>
                        <td>{{ $description }}</td>
                        <td>₱{{ number_format($item->unit_price, 2) }}</td>
                        <td>₱{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="4">TOTAL</td>
                    <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">Proposed by</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Approved by</div>
            </div>
            <div class="signature-box">
                <div class="signature-line">Checked by</div>
            </div>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html> 