<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrega extends Model
{
    use HasFactory;

    protected $fillable = [
        'area_id',
        'formatos',
        'tipo',
        'mes',
        'anio',
        'cargo_titular',
        'cargo_enlace',
        'cargo_receptor',
        'fecha_entrega_enlace',
        'fecha_entrega_titular',
        'titular_id',
        'enlace_id',
        'receptor_id',
        'estatus',
    ];

    protected $casts = [
        'formatos' => 'array',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function titular()
    {
        return $this->belongsTo(User::class);
    }

    public function enlace()
    {
        return $this->belongsTo(User::class);
    }

    public function receptor()
    {
        return $this->belongsTo(User::class);
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class)->orderBy('id', 'desc')->limit(5);
    }

    public function scopeFiltros($query, array $filtros)
    {
    }

    public function scopeFiltro($query, $key)
    {
    }
}