<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'titulo',
        'mensagem',
        'arquivo_path',
        'tipo_arquivo',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(MediaMessageItem::class);
    }
}
