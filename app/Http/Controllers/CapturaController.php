<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\Entrega;
use App\Models\Registro;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CapturaController extends Controller
{
    public function index(Request $request, $uuid)
    {
        $usuario_id = \Auth::id();
        $entrega = Entrega::where('uuid', $uuid)
                    ->with('area', 'titular', 'enlace', 'receptor', 'reportes')
                    ->first();
        $formatos_activos = [];
        $formatos_activos[$entrega->id] = [];
        foreach ($entrega->formatos as $this_formato) {
            $este_anexo_id = $this_formato['value'];
            $este_anexo = Anexo::find($este_anexo_id);
            array_push($formatos_activos[$entrega->id], $este_anexo);
        }

        return Inertia::render('Captura', compact('entrega', 'formatos_activos'));
    }

    public function anexo(Request $request, $uuid, $id)
    {
        $anexo = Anexo::find($id);
        $entrega = Entrega::where('uuid', $uuid)->with('area', 'titular', 'enlace', 'receptor', 'reportes')->first();
        $registros = Registro::where('entrega_uuid', $uuid)->where('anexo_id', $id)->paginate(10)->onEachSide(1)->appends(request()->query());

        return Inertia::render('Captura/Anexo', compact('anexo', 'uuid', 'id', 'registros', 'entrega'));
    }

    public function search(Request $request, $uuid, $id, $filtro)
    {
        $anexo = Anexo::find($id);
        $entrega = Entrega::where('uuid', $uuid)->with('area', 'titular', 'enlace', 'receptor', 'reportes')->first();
        $registros = Registro::whereRaw("entrega_uuid = '$uuid' AND anexo_id = $id  AND lower(data) like '%". mb_strtolower($filtro) ."%'")->paginate(10)->onEachSide(1)->appends(request()->query());

        return Inertia::render('Captura/Anexo', compact('anexo', 'uuid', 'id', 'registros', 'entrega', 'filtro'));
    }

    public function reportes(Request $request, $uuid, $id)
    {
        $entrega = Entrega::where('uuid', $uuid)->select('id')->with('reportes')->first();
        $reportes = Reporte::where('entrega_id', $entrega->id)->where('anexo_id', $id)->orderBy('id', 'desc')->limit(5)->get();
        //Regresa los reportes en json
        return response()->json($reportes);
    }

    public function reportesEntrega(Request $request, $uuid)
    {
        $entrega = Entrega::where('uuid', $uuid)->select('id')->with('reportes')->first();
        $reportes = Reporte::where('entrega_id', $entrega->id)->whereNull('anexo_id')->orderBy('id', 'desc')->limit(5)->get();
        //Regresa los reportes en json
        return response()->json($reportes);
    }

    public function store(Request $request, $uuid, $id)
    {
        $user = \Auth::user();
        $datos = $request->all();
        $registro = Registro::create([
            'entrega_uuid' => $uuid,
            'anexo_id' => $id,
            'estatus' => 'Capturado',
            'data' => $datos['anexo'],
            'user_id' => $user->id,
        ]);

        return redirect()->back()->with('success', 'Registro creado con éxito')->with('passData', $registro);
    }

    public function update(Request $request, $uuid, $id, $registro_id)
    {
        $user = \Auth::user();
        $datos = $request->all();
        $registro = Registro::where('entrega_uuid', $uuid)->where('anexo_id', $id)->where('id', $registro_id)->first();
        $registro->data = $datos['anexo'];
        $registro->user_id = $user->id;
        $registro->save();

        return redirect()->back()->with('success', 'Registro actualizado con éxito')->with('passData', $registro);
    }

    public function destroy(Request $request, $uuid, $id, $registro_id)
    {
        $registro = Registro::where('entrega_uuid', $uuid)->where('anexo_id', $id)->where('id', $registro_id)->first();
        $registro->delete();

        return redirect()->back()->with('success', 'Registro eliminado con éxito');
    }
}