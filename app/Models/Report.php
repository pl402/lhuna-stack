<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    protected $fillable = [
        'name',
        'entity_name',
        'fields',
        'filters',
        'sort_by',
        'sort_order',
        'user_id',
    ];

    protected $casts = [
        'fields' => 'array',
        'filters' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFiltros($query, array $filtros)
    {
        foreach ($filtros as $filtro) {
            $campo = $filtro["campo"];
            $condicion = $filtro["condicion"];
            $valor = $filtro["valor"];
            $conjuncion = $filtro["conjuncion"] ?? "AND";

            if ($condicion == "LIKE" || $condicion == "NOT LIKE") {
                $valor = "%{$valor}%";
            }

            if ($condicion == "IS NULL") {
                if ($conjuncion == "AND") {
                    $query->whereNull($campo);
                } else {
                    $query->orWhereNull($campo);
                }
                continue;
            }

            if ($condicion == "IS NOT NULL") {
                if ($conjuncion == "AND") {
                    $query->whereNotNull($campo);
                } else {
                    $query->orWhereNotNull($campo);
                }
                continue;
            }

            if ($conjuncion == "AND") {
                $query->where($campo, $condicion, $valor);
            } else {
                $query->orWhere($campo, $condicion, $valor);
            }
        }

        return $query;
    }

    public function scopeFiltro($query, $key)
    {
        return $query->where('name', 'LIKE', "%{$key}%")
                     ->orWhere('entity_name', 'LIKE', "%{$key}%");
    }
}
