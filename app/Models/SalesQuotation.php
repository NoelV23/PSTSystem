<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesQuotation extends Model
{
    protected $fillable = [
        'branch_id',
        'user_id',
        'quotation_number',
        'customer_name',
        'customer_company',
        'customer_email',
        'customer_phone',
        'customer_address',
        'status',
        'subtotal',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'grand_total',
        'notes',
        'terms',
        'valid_until',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'sale_id',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesQuotationItem::class)->orderBy('sort_order');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'rejected'], true);
    }
}
