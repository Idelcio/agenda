<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'inicio',
        'fim',
        'dia_inteiro',
        'status',
        'notificar_whatsapp',
        'whatsapp_numero',
        'whatsapp_mensagem',
        'antecedencia_minutos',
        'lembrar_em',
        'lembrete_enviado_em',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'inicio' => 'datetime',
        'fim' => 'datetime',
        'dia_inteiro' => 'boolean',
        'notificar_whatsapp' => 'boolean',
        'lembrar_em' => 'datetime',
        'lembrete_enviado_em' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function whatsappMessages()
    {
        return $this->hasMany(WhatsAppMessage::class);
    }

    /**
     * Define se o compromisso está concluído.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'concluido';
    }

    /**
     * Escopo para compromissos futuros.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('inicio', '>=', now())->orderBy('inicio');
    }

    /**
     * Escopo para compromissos que precisam de lembrete.
     */
    public function scopeDueForReminder($query)
    {
        return $query->where('notificar_whatsapp', true)
            ->whereNull('lembrete_enviado_em')
            ->whereNotNull('lembrar_em')
            ->where('lembrar_em', '<=', now());
    }

    /**
     * Define a data de lembrete com base na antecedência.
     */
    public function computeReminderTime(?int $antecedenciaMinutos = null): void
    {
        if ($antecedenciaMinutos !== null && $this->inicio instanceof CarbonInterface) {
            $this->lembrar_em = $this->inicio->copy()->subMinutes($antecedenciaMinutos);
            $this->antecedencia_minutos = $antecedenciaMinutos;
        }
    }

    /**
     * Marca o compromisso como lembrado.
     */
    public function markAsReminded(): void
    {
        $this->lembrete_enviado_em = Carbon::now();
        $this->saveQuietly();
    }
}
