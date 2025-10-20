<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'user_id',
        'mercadopago_payment_id',
        'mercadopago_preference_id',
        'status',
        'status_detail',
        'transaction_amount',
        'payment_method_id',
        'payment_type_id',
        'metadata',
        'approved_at',
    ];

    protected $casts = [
        'transaction_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relacionamento com Subscription
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Relacionamento com User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Verifica se o pagamento foi aprovado
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    // Verifica se o pagamento está pendente
    public function isPending()
    {
        return $this->status === 'pending' || $this->status === 'in_process';
    }

    // Verifica se o pagamento foi rejeitado
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    // Scope para pagamentos aprovados
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // Scope para pagamentos pendentes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_process', 'authorized']);
    }
}
