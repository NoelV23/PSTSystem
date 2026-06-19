<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'description',
        'custom_item_name',
        'custom_color',
        'custom_thickness',
        'custom_measurement',
        'quantity',
        'unit_price',
        'cut_length',
        'cut_width',
        'cut_height',
        'cut_measurement_unit',
        'total_price',
        'fulfillment_source',
        'created_remainder_id',
        'remainder_before_json',
        'remainder_after_id',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isCustomLine(): bool
    {
        return $this->product_id === null || ($this->fulfillment_source ?? '') === 'custom';
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

        if (($p->measurement_unit ?? '') === 'sq ft' && $p->default_width && $p->default_height) {
            return $p->default_width.'×'.$p->default_height.' sq ft';
        }
        if ($p->default_length) {
            $unit = $p->measurement_unit ?: preg_replace('/^per\s+/i', '', (string) ($p->base_unit ?? ''));

            return $p->default_length.' '.$unit;
        }

        return '';
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