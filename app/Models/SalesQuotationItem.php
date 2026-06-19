<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesQuotationItem extends Model
{
    protected $fillable = [
        'sales_quotation_id',
        'product_id',
        'description',
        'custom_item_name',
        'custom_color',
        'custom_thickness',
        'custom_measurement',
        'cut_length',
        'cut_width',
        'cut_height',
        'cut_measurement_unit',
        'quantity',
        'line_unit',
        'unit_price',
        'retail_unit_price',
        'line_total',
        'is_free',
        'is_long_span',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'retail_unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'is_free' => 'boolean',
        'is_long_span' => 'boolean',
        'cut_length' => 'decimal:3',
        'cut_width' => 'decimal:3',
        'cut_height' => 'decimal:3',
    ];

    public function salesQuotation(): BelongsTo
    {
        return $this->belongsTo(SalesQuotation::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLongSpanLine(): bool
    {
        return (bool) $this->is_long_span;
    }

    /** Thickness label for print grouping (e.g. 0.60mm). */
    public function printThicknessLabel(): ?string
    {
        $t = trim((string) ($this->custom_thickness ?? ''));
        if ($t === '' && $this->relationLoaded('product') && $this->product) {
            $t = trim((string) ($this->product->thickness ?? ''));
        }

        return $t !== '' ? $t : null;
    }

    /** Coverage / profile width for long-span dimension (e.g. 1.040m). */
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

    public function printCategoryName(): string
    {
        $cat = $this->product?->category?->name;

        return $cat ? strtoupper($cat) : 'OTHER MATERIALS';
    }

    public function displayLineUnit(): string
    {
        $u = trim((string) ($this->line_unit ?? ''));
        if ($u !== '') {
            return $u;
        }
        if ($this->is_long_span) {
            return 'lmtrs';
        }
        $p = $this->product;
        if (! $p) {
            return 'pc';
        }
        $base = strtolower(preg_replace('/^per\s+/i', '', (string) ($p->base_unit ?? '')) ?: 'pc');
        if (in_array($base, ['ls'], true)) {
            return 'LS';
        }
        if (in_array($base, ['meter', 'meters', 'metre', 'metres', 'm', 'length'], true)) {
            return 'lmtrs';
        }

        return $base ?: 'pc';
    }
}
