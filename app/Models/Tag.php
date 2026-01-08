<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nome',
        'cor',
    ];

    /**
     * Empresa dona da tag
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Clientes que possuem esta tag
     */
    public function clientes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cliente_tag', 'tag_id', 'cliente_id')
            ->withTimestamps();
    }

    /**
     * Scope para filtrar tags do usuário logado
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
