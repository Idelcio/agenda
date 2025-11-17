<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'mensagem',
        'total_destinatarios',
        'enviados',
        'falhas',
        'status',
        'iniciado_em',
        'concluido_em',
        'scheduled_for',
    ];

    protected $casts = [
        'iniciado_em' => 'datetime',
        'concluido_em' => 'datetime',
        'scheduled_for' => 'datetime',
    ];

    /**
     * Empresa que criou o envio em massa
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Itens individuais de envio
     */
    public function items()
    {
        return $this->hasMany(MassMessageItem::class);
    }
}
