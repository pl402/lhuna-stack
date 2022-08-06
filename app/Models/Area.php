<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'nombre',
        'descripcion',
        'estatus',
    ];

    public function scopeFiltros($query, array $filtros)
    {
        $query->when($filtros['nombre'] ?? null, function ($query, $nombre) {
            return $query->where('nombre', $nombre);
        })->when($filtros['descripcion'] ?? null, function ($query, $descripcion) {
            return $query->where('descripcion', $descripcion);
        })->when($filtros['estatus'] ?? null, function ($query, $estatus) {
            return $query->where('estatus', $estatus);
        })->when($filtros['id'] ?? null, function ($query, $id) {
            return $query->where('id', $id);
        });
    }

    public function scopeFiltro($query, $key)
    {
        return $query->orWhere('nombre', 'LIKE', "%{$key}%")
                ->orWhere('descripcion', 'LIKE', "%{$key}%")
                ->orWhere('estatus', 'LIKE', "%{$key}%")
                ->orWhere('id', 'LIKE', "%{$key}%");
    }

    public function children()
    {
        return $this->hasMany(Area::class, 'area_padre');
    }

    public function parent()
    {
        return $this->belongsTo(Area::class, 'area_padre');
    }

    public function ancestry()
    {
        return $this->belongsTo(Area::class, 'area_padre')->with('ancestry');
    }
}
