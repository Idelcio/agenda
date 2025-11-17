<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'whatsapp_number',
        'is_admin',
        'tipo',
        'user_id',
        'plano',
        'acesso_ativo',
        'acesso_liberado_ate',
        'limite_requisicoes_mes',
        'requisicoes_mes_atual',
        'valor_pago',
        'data_ultimo_pagamento',
        'observacoes_admin',
        'apibrasil_device_token',
        'apibrasil_device_name',
        'apibrasil_device_id',
        'apibrasil_qrcode_status',
        'apibrasil_setup_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
        'acesso_ativo' => 'boolean',
        'acesso_liberado_ate' => 'datetime',
        'valor_pago' => 'decimal:2',
        'data_ultimo_pagamento' => 'datetime',
        'apibrasil_setup_completed' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function (self $user) {
            if (!empty($user->apibrasil_device_id) && empty($user->apibrasil_device_token)) {
                $user->apibrasil_device_token = $user->apibrasil_device_id;
            }
        });
    }

    /**
     * Compromissos vinculados ao usuário.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Envios em massa cadastrados pelo usuário.
     */
    public function massMessages()
    {
        return $this->hasMany(MassMessage::class);
    }

    /**
     * Mensagens de WhatsApp salvas pelo usuário para uso rápido.
     */
    public function quickMessageTemplates(): HasMany
    {
        return $this->hasMany(WhatsAppMessageTemplate::class);
    }

    /**
     * Registros de chatbot associados ao usuário.
     */
    public function chatbotMessages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }

    /**
     * Indica se o usuario possui privilegios de administrador.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * Indica se o usuário é um cliente.
     */
    public function isCliente(): bool
    {
        return $this->tipo === 'cliente';
    }

    /**
     * Scope para filtrar apenas clientes.
     */
    public function scopeClientes($query)
    {
        return $query->where('tipo', 'cliente');
    }

    public function empresa()
    {
        // Usuário pai (empresa)
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clientes()
    {
        // Usuários filhos (clientes vinculados à empresa)
        return $this->hasMany(User::class, 'user_id');
    }

    /**
     * Assinaturas do usuário
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Assinatura ativa do usuário
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    /**
     * Pagamentos do usuário
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Verifica se o usuário tem assinatura ativa
     */
    public function hasActiveSubscription()
    {
        return $this->activeSubscription()->exists();
    }
}
