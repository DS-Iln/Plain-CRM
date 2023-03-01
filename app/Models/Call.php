<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Call extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для создания и методов
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'telephone_number',
        'status',
    ];

    /**
     * Получить заказчика, привязанного к звонку
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
