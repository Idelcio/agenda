<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaMessageItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_message_id',
        'cliente_id',
        'telefone',
        'status',
        'erro_mensagem',
        'enviado_em',
    ];

    protected $casts = [
        'enviado_em' => 'datetime',
    ];

    public function mediaMessage()
    {
        return $this->belongsTo(MediaMessage::class);
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }
}
