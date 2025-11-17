<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassMessageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'mass_message_id',
        'cliente_id',
        'telefone',
        'status',
        'erro_mensagem',
        'enviado_em',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
    ];

    /**
     * Mensagem em massa pai
     */
    public function massMessage()
    {
        return $this->belongsTo(MassMessage::class);
    }

    /**
     * Cliente que recebeu
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }
}
