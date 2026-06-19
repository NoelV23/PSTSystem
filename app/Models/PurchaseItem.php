<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'description',
        'custom_item_name',
        'custom_color',
        'custom_thickness',
        'custom_measurement',
        'quantity',
        'total_linear_meters',
        'cost_price',
        'cut_length',
        'cut_width',
        'cut_height',
        'cut_measurement_unit',
        'is_long_span',
    ];

    protected $casts = [
        'quantity' => 'float',
        'total_linear_meters' => 'decimal:4',
        'cost_price' => 'decimal:2',
        'is_long_span' => 'boolean',
        'cut_length' => 'decimal:3',
        'cut_width' => 'decimal:3',
        'cut_height' => 'decimal:3',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        $lm = $this->totalLinearMeters();
        if ($lm !== null && $lm > 0) {
            return round($lm * (float) $this->cost_price, 2);
        }

        return round((float) $this->quantity * (float) $this->cost_price, 2);
    }

    public function usesLmPricing(): bool
    {
        $lm = $this->totalLinearMeters();

        return $lm !== null && $lm > 0;
    }

    /** Length per piece (m) — user cut length or catalog default for long-span lines. */
    public function lengthPerPiece(): ?float
    {
        if ($this->cut_length !== null && (float) $this->cut_length > 0) {
            return (float) $this->cut_length;
        }
        $p = $this->product;
        if ($p && $this->isLongSpanLine() && $p->default_length !== null && (float) $p->default_length > 0) {
            return (float) $p->default_length;
        }
        if ($this->isLongSpanLine()) {
            $m = trim((string) ($this->custom_measurement ?? ''));
            if ($m !== '' && preg_match('/^([\d.]+)/', $m, $match)) {
                $len = (float) $match[1];

                return $len > 0 ? $len : null;
            }
        }

        return null;
    }

    /** Total LM — manual entry, or qty × length; legacy long-span rows may store LM in quantity. */
    public function totalLinearMeters(): ?float
    {
        if ($this->total_linear_meters !== null && (float) $this->total_linear_meters > 0) {
            return (float) $this->total_linear_meters;
        }
        $len = $this->lengthPerPiece();
        $qty = (float) $this->quantity;
        if ($len !== null && $len > 0 && $qty > 0) {
            return round($qty * $len, 4);
        }
        if ($this->isLongSpanLine() && $qty > 0 && $len === null) {
            return $qty;
        }

        return null;
    }

    public function printGaugeLabel(): string
    {
        $t = $this->printThicknessLabel();
        if ($t !== null && $t !== '' && preg_match('/([\d.]+)/', $t, $match)) {
            return rtrim(rtrim(number_format((float) $match[1], 2), '0'), '.');
        }

        return ($t !== null && $t !== '') ? $t : '—';
    }

    public function printUnitLabel(): string
    {
        if ($this->usesLmPricing()) {
            return 'pcs';
        }
        $p = $this->product;
        if (! $p) {
            return 'pcs';
        }
        $u = strtolower(preg_replace('/^per\s+/i', '', (string) ($p->base_unit ?? '')) ?: 'pcs');
        if (in_array($u, ['sheet', 'sheets'], true)) {
            return 'sheet';
        }
        if (in_array($u, ['pc', 'pcs', 'piece', 'pieces'], true)) {
            return 'pcs';
        }

        return $u;
    }

    protected function formatPoDim(?float $value, ?string $unit = null): ?string
    {
        if ($value === null || $value <= 0) {
            return null;
        }
        $formatted = rtrim(rtrim(number_format($value, 2), '0'), '.');
        $u = trim((string) ($unit ?? ''));

        return $u !== '' ? $formatted.' '.$u : $formatted;
    }

    public function printWidthLabel(): string
    {
        if ($this->isSquareMeasurementLine()) {
            return '—';
        }
        if ($this->usesLmPricing()) {
            $cov = $this->printLongSpanCoverage();
            if ($cov) {
                return $cov;
            }
        }
        $p = $this->product;
        if ($p && $p->default_width !== null && (float) $p->default_width > 0) {
            $unit = strtolower((string) ($p->measurement_unit ?? ''));
            if ($unit === 'sq ft' || $unit === 'sqft') {
                return $this->formatPoDim((float) $p->default_width, 'sq ft') ?? '—';
            }

            return $this->formatPoDim((float) $p->default_width, $p->measurement_unit ?: 'm') ?? '—';
        }

        return '—';
    }

    public function isSquareMeasurementLine(): bool
    {
        $p = $this->product;
        if ($p) {
            $mu = strtolower((string) ($p->measurement_unit ?? ''));

            return ($mu === 'sq ft' || $mu === 'sqft')
                && $p->default_width !== null
                && (float) $p->default_width > 0
                && $p->default_height !== null
                && (float) $p->default_height > 0;
        }
        $cm = strtolower(trim((string) ($this->custom_measurement ?? '')));

        return str_contains($cm, 'sq ft') || str_contains($cm, 'sqft');
    }

    /** Combined WIDTH+LENGTH for sq ft sheet lines (e.g. 4FTX8FT). */
    public function printSquareSizeLabel(): ?string
    {
        if (! $this->isSquareMeasurementLine()) {
            return null;
        }
        $cut = $this->lineCutLabel();
        if ($cut) {
            return $cut;
        }
        $p = $this->product;
        if ($p && $p->default_width && $p->default_height) {
            $w = rtrim(rtrim(number_format((float) $p->default_width, 0), '0'), '.');
            $h = rtrim(rtrim(number_format((float) $p->default_height, 0), '0'), '.');

            return strtoupper($w.'FTX'.$h.'FT');
        }
        $cm = trim((string) ($this->custom_measurement ?? ''));

        return $cm !== '' ? $cm : null;
    }

    public function printLengthLabel(): string
    {
        if ($this->isSquareMeasurementLine()) {
            return '—';
        }
        $len = $this->lengthPerPiece();
        if ($len !== null && $len > 0) {
            return $this->formatPoDim($len, 'm') ?? '—';
        }
        $p = $this->product;
        if ($p) {
            if ($p->default_length !== null && (float) $p->default_length > 0 && ! $this->usesLmPricing()) {
                return $this->formatPoDim((float) $p->default_length, $p->measurement_unit) ?? '—';
            }
        }
        $cut = $this->lineCutLabel();
        if ($cut) {
            return $cut.' (cut)';
        }
        $cm = trim((string) ($this->custom_measurement ?? ''));

        return $cm !== '' ? $cm : '—';
    }

    public function isCustomLine(): bool
    {
        return $this->product_id === null;
    }

    public function isLongSpanLine(): bool
    {
        return (bool) $this->is_long_span;
    }

    public function printThicknessLabel(): ?string
    {
        $t = trim((string) ($this->custom_thickness ?? ''));
        if ($t === '' && $this->relationLoaded('product') && $this->product) {
            $t = trim((string) ($this->product->thickness ?? ''));
        }

        return $t !== '' ? $t : null;
    }

    public function printLongSpanCoverage(): ?string
    {
        $m = trim((string) ($this->custom_measurement ?? ''));
        if ($m !== '') {
            return $m;
        }
        if ($this->product && $this->product->default_width) {
            $w = (float) $this->product->default_width;

            return rtrim(rtrim(number_format($w, 3), '0'), '.').'m';
        }

        return null;
    }

    public function lineDisplayName(): string
    {
        if ($this->isCustomLine()) {
            $name = trim((string) ($this->custom_item_name ?: $this->description ?: ''));

            return $name !== '' ? $name : 'Custom item';
        }

        return $this->product?->name ?? trim((string) ($this->description ?: 'Custom item'));
    }

    public function lineSpecLabel(): string
    {
        if ($this->isCustomLine()) {
            return collect([$this->custom_thickness, $this->custom_measurement, $this->custom_color])
                ->map(fn ($v) => trim((string) ($v ?? '')))
                ->filter(fn ($v) => $v !== '')
                ->implode(' · ');
        }

        $p = $this->product;
        if (! $p) {
            return '';
        }

        $parts = [];
        if ($this->custom_thickness) {
            $parts[] = $this->custom_thickness;
        } elseif ($p->thickness) {
            $parts[] = $p->thickness;
        }
        if ($this->custom_measurement) {
            $parts[] = $this->custom_measurement;
        } elseif ($p->default_length) {
            $unit = $p->measurement_unit ?: preg_replace('/^per\s+/i', '', (string) ($p->base_unit ?? ''));
            $parts[] = $p->default_length.' '.$unit;
        }
        $color = $this->custom_color ?: $p->color;
        if ($color) {
            $parts[] = $color;
        }

        return collect($parts)->filter()->implode(' · ');
    }

    public function lineCutLabel(): ?string
    {
        $parts = [];
        if ($this->cut_length > 0) {
            $parts[] = number_format((float) $this->cut_length, 2);
        }
        if ($this->cut_width > 0) {
            $parts[] = number_format((float) $this->cut_width, 2);
        }
        if ($this->cut_height > 0) {
            $parts[] = number_format((float) $this->cut_height, 2);
        }
        if ($parts === []) {
            return null;
        }
        $text = implode('×', $parts);
        if ($this->cut_measurement_unit) {
            $text .= ' '.$this->cut_measurement_unit;
        }

        return $text;
    }

    public function reportGroupKey(): string
    {
        if ($this->product_id) {
            return 'p:'.$this->product_id;
        }

        $parts = [
            mb_strtolower(trim($this->custom_item_name ?: $this->description ?: 'custom')),
            mb_strtolower(trim($this->custom_color ?? '')),
            mb_strtolower(trim($this->custom_thickness ?? '')),
            mb_strtolower(trim($this->custom_measurement ?? '')),
        ];

        return 'c:'.implode('|', $parts);
    }

    public function reportProductName(): string
    {
        if ($this->product_id && $this->product) {
            return $this->product->name;
        }

        return $this->lineDisplayName();
    }

    public function reportProductSku(): string
    {
        if ($this->product_id && $this->product) {
            return $this->product->sku ?? '—';
        }

        return 'Custom';
    }
}
