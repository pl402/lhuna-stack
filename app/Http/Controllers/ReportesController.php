<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\Area;
use App\Models\Entrega;
use App\Models\Registro;
use App\Models\Configuracion;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Jobs\ReportesJobs;
use App\Jobs\ReportesEntregaJobs;

class ReportesController extends Controller
{
    public $meses = [
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre',
        ];

    public function index(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('reportes.index')) {
            $filtro = null;
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $reportes = Reporte::with('entrega', 'anexo', 'user')->orderBy($orderBy['field'], $orderBy['sort'])->paginate(10)->onEachSide(1)->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $reportes = Reporte::with('entrega', 'anexo', 'user')->paginate(10)->onEachSide(1)->appends(request()->query());
            }

            return Inertia::render('Reportes', compact('reportes', 'filtro', 'orderBy'));
        } else {
            $request->session()->flash('error', 'No cuenta con los permisos necesarios para realizar esta acción.');

            return redirect()->back();
        }
    }

    public function search($filtro, Request $request)
    {
        $user = \Auth::user();
        if ($user->can('reportes.index')) {
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $reportes = Reporte::filtro($filtro)->orderBy($orderBy['field'], $orderBy['sort'])->with('entrega', 'anexo', 'user')->paginate(10)->onEachSide(1)->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $reportes = Reporte::filtro($filtro)->with('entrega', 'anexo', 'user')->paginate(10)->onEachSide(1)->appends(request()->query());
            }
           
            return Inertia::render('Reportes', compact('reportes', 'filtro', 'orderBy'));
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function anexo(Request $request, $uuid, $id)
    {
        $anexo = Anexo::find($id);
        $configuraciones = Configuracion::all();
        $entrega = Entrega::where('uuid', $uuid)->with('area', 'titular', 'enlace', 'receptor')->first();
        $area = Area::find($entrega->area_id)->with('ancestry')->first();
        $registros = Registro::where('entrega_uuid', $uuid)->where('anexo_id', $id)->get();

        $user = \Auth::user();
        $reporte = Reporte::create([
            'entrega_id' => $entrega->id,
            'anexo_id' => $anexo->id,
            'estatus' => 'Generando',
            'titulo' => $area->nombre.' - '.$entrega->tipo.' '.$entrega->anio.' '.$this->meses[$entrega->mes - 1].' - '.$anexo->numero.' '.$anexo->clave.' '.$anexo->nombre,
            'user_id' => $user->id,
        ]);
        
        ReportesJobs::dispatch($anexo, $configuraciones, $entrega, $area, $registros, $reporte);
        return redirect()->back()->with('success', 'Generando reporte...');
    }

    public function entrega(Request $request, $uuid)
    {
        $configuraciones = Configuracion::all();
        $entrega = Entrega::where('uuid', $uuid)->with('area', 'titular', 'enlace', 'receptor')->first();
        $area = Area::find($entrega->area_id)->with('ancestry')->first();
        $registros = [];
        $anexos = [];
        
        foreach ($entrega->formatos as $formato) {
            //Crea una colección de registros por cada formato
            $registros[$formato['value']] = Registro::where('entrega_uuid', $uuid)->where('anexo_id', $formato['value'])->get();
            $anexos[$formato['value']] = Anexo::find($formato['value']);
        }

        $user = \Auth::user();
        $reporte = Reporte::create([
            'entrega_id' => $entrega->id,
            'anexo_id' => null,
            'estatus' => 'Generando',
            'titulo' => $area->nombre.' - '.$entrega->tipo.' '.$entrega->anio.' '.$this->meses[$entrega->mes - 1],
            'user_id' => $user->id,
        ]);
        ReportesEntregaJobs::dispatch($anexos, $configuraciones, $entrega, $area, $registros, $reporte);
        return redirect()->back()->with('success', 'Generando reporte...');
    }

    public function descarga(Request $request, $uuid)
    {
        $reporte = Reporte::where('uuid', $uuid)->where('Estatus', 'Generado')->first();
        $archivo = $reporte->archivo;
        $titulo = $reporte->titulo;

        return response()->download(storage_path($archivo), $titulo.'.pdf');
    }
}