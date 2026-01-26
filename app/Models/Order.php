<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code', 'user_id', 'jastiper_id', 'total_amount',
        'status', 'delivery_address', 'notes', 'jastiper_commission',
        'accepted_at', 'delivered_at', 'completed_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'jastiper_commission' => 'decimal:2',
        'accepted_at' => 'datetime',
        'delivered_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jastiper()
    {
        return $this->belongsTo(User::class, 'jastiper_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryHistory()
    {
        return $this->hasOne(DeliveryHistory::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query, $jastiperId = null)
    {
        $query = $query->whereIn('status', ['accepted', 'delivered']);
        
        if ($jastiperId) {
            $query->where('jastiper_id', $jastiperId);
        }
        
        return $query;
    }

    public function scopeCompleted($query, $jastiperId = null)
    {
        $query = $query->where('status', 'completed');
        
        if ($jastiperId) {
            $query->where('jastiper_id', $jastiperId);
        }
        
        return $query;
    }
}
