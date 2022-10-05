<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $fillable = [
        "proyecto_uuid",
        "area_id",
        "user_id",
        "titulo",
        "estatus",
        "archivo",
    ];

    public function scopeFiltros($query, array $filtros)
    {
        $query
            ->when($filtros["titulo"] ?? null, function ($query, $titulo) {
                return $query->where("titulo", $titulo);
            })
            ->when($filtros["estatus"] ?? null, function ($query, $estatus) {
                return $query->where("estatus", $estatus);
            })
            ->when($filtros["id"] ?? null, function ($query, $id) {
                return $query->where("id", $id);
            });
    }

    public function scopeFiltro($query, $key)
    {
        return $query
            ->orWhere("titulo", "LIKE", "%{$key}%")
            ->orWhere("estatus", "LIKE", "%{$key}%")
            ->orWhere("id", "LIKE", "%{$key}%");
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class, "proyecto_uuid", "uuid");
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}