<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'clave',
        'valor',
        'tipo',
    ];

    protected $table = 'configuraciones';

    public function scopeFiltros($query, array $filtros)
    {
        foreach ($filtros as $filtro) {
            $campo = $filtro["campo"];
            $condicion = $filtro["condicion"];
            $valor = $filtro["valor"];
            $conjuncion = $filtro["conjuncion"];

            if ($condicion == "LIKE") {
                $valor = "%{$valor}%";
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
        return $query->orWhere('clave', 'LIKE', "%{$key}%")
                ->orWhere('valor', 'LIKE', "%{$key}%")
                ->orWhere('tipo', 'LIKE', "%{$key}%");
    }
}