<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registro extends Model
{
    use HasFactory;

    protected $fillable = [
        'entrega_uuid',
        'anexo_id',
        'estatus',
        'data',
        'user_id',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function scopeFiltros($query, array $filtros)
    {
    }

    public function scopeFiltro($query, $key)
    {
    }
}
