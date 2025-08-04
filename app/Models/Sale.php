<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'total_amount',
        'payment_method',
        'delivery_address',
        'is_installation',
        'installation_address',
        'description',
        'status',
        'created_at',
        'is_delivered',
        'delivered_to',
        'delivery_date',
        'delivery_note',
    ];

    protected $casts = [
        'is_installation' => 'boolean',
        'total_amount' => 'decimal:2',
        'is_delivered' => 'boolean',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }
} 