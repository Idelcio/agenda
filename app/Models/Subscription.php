<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_type',
        'amount',
        'status',
        'is_lifetime',
        'starts_at',
        'expires_at',
        'mercadopago_preference_id',
        'mercadopago_payment_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'amount' => 'decimal:2',
        'is_lifetime' => 'boolean',
    ];

    // Relacionamento com User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento com Payments
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Verifica se a assinatura está ativa
    public function isActive()
    {
        // Assinatura vitalícia sempre está ativa
        if ($this->is_lifetime && $this->status === 'active') {
            return true;
        }

        return $this->status === 'active' &&
               $this->expires_at &&
               $this->expires_at->isFuture();
    }

    // Verifica se a assinatura está expirada
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Ativa a assinatura
    public function activate()
    {
        $this->status = 'active';
        $this->starts_at = now();
        $this->expires_at = $this->calculateExpirationDate();
        $this->save();
    }

    // Cancela a assinatura
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    // Calcula a data de expiração baseada no plano
    private function calculateExpirationDate()
    {
        $startDate = $this->starts_at ?? now();

        return match($this->plan_type) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'semiannual' => $startDate->copy()->addMonths(6),
            'annual' => $startDate->copy()->addYear(),
            default => $startDate->copy()->addMonth(),
        };
    }

    // Scope para assinaturas ativas
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(function($q) {
                         $q->where('is_lifetime', true)
                           ->orWhere('expires_at', '>', now());
                     });
    }

    // Scope para assinaturas expiradas
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                     ->where('status', 'active');
    }
}
