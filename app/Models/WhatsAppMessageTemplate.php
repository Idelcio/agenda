<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessageTemplate extends Model
{
    use HasFactory;

    public const MAX_PER_USER = 5;

    protected $table = 'whatsapp_message_templates';

    protected $fillable = [
        'user_id',
        'message',
    ];

    /**
     * Owner of the saved message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
