<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anexo extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero',
        'clave',
        'nombre',
        'descripcion',
        'formato',
        'estatus',
    ];

    protected $casts = [
        'formato' => 'array',
    ];

    public function scopeFiltros($query, array $filtros)
    {
        $query->when($filtros['nombre'] ?? null, function ($query, $nombre) {
            return $query->where('nombre', $nombre);
        })->when($filtros['descripcion'] ?? null, function ($query, $descripcion) {
            return $query->where('descripcion', $descripcion);
        })->when($filtros['clave'] ?? null, function ($query, $clave) {
            return $query->where('clave', $clave);
        })->when($filtros['numero'] ?? null, function ($query, $numero) {
            return $query->where('numero', $numero);
        })->when($filtros['estatus'] ?? null, function ($query, $estatus) {
            return $query->where('estatus', $estatus);
        })->when($filtros['formato'] ?? null, function ($query, $formato) {
            return $query->where('formato', $formato);
        })->when($filtros['id'] ?? null, function ($query, $id) {
            return $query->where('id', $id);
        });
    }

    public function scopeFiltro($query, $key)
    {
        return $query->orWhere('nombre', 'LIKE', "%{$key}%")
                ->orWhere('descripcion', 'LIKE', "%{$key}%")
                ->orWhere('clave', 'LIKE', "%{$key}%")
                ->orWhere('numero', 'LIKE', "%{$key}%")
                ->orWhere('estatus', 'LIKE', "%{$key}%")
                ->orWhere('formato', 'LIKE', "%{$key}%")
                ->orWhere('id', 'LIKE', "%{$key}%");
    }
}
