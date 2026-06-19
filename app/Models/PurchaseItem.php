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
        'cost_price',
        'cut_length',
        'cut_width',
        'cut_height',
        'cut_measurement_unit',
        'is_long_span',
    ];

    protected $casts = [
        'quantity' => 'float',
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
        return $this->quantity * $this->cost_price;
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
