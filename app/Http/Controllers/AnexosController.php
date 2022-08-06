<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AnexosController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('anexos.index')) {
            $filtro = null;
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $anexos = Anexo::where('estatus', 'Activo')->orderBy($orderBy['field'], $orderBy['sort'])->paginate(10)->onEachSide(1)->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $anexos = Anexo::where('estatus', 'Activo')->paginate(10)->onEachSide(1)->appends(request()->query());
            }

            return Inertia::render('Anexos', compact('anexos', 'filtro', 'orderBy'));
        } else {
            $request->session()->flash('error', 'No cuenta con los permisos necesarios para realizar esta acción.');

            return redirect()->back();
        }
    }

    public function search($filtro, Request $request)
    {
        $user = \Auth::user();
        if ($user->can('anexos.index')) {
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $anexos = Anexo::where('estatus', 'Activo')->filtro($filtro)->orderBy($orderBy['field'], $orderBy['sort'])->paginate(10)->onEachSide(1)->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $anexos = Anexo::where('estatus', 'Activo')->filtro($filtro)->paginate(10)->onEachSide(1)->appends(request()->query());
            }

            return Inertia::render('Anexos', compact('anexos', 'filtro', 'orderBy'));
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('anexos.store')) {
            $datos = $request->all();
            $validado = Validator::make($datos, [
                'numero' => ['required', 'integer'],
                'clave' => ['required', 'string', 'max:50'],
                'nombre' => ['required', 'string', 'max:512'],
                'descripcion' => ['required', 'string', 'max:2048'],
            ])->validate();
            $anexo = Anexo::create([
                'numero' => $datos['numero'],
                'clave' => $datos['clave'],
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'],
            ]);
            $anexo->origen_id = $anexo->id;
            $anexo->save();
            return redirect()->back()->with('success', 'Anexo creado con éxito')->with('passData', $anexo);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can('anexos.update')) {
            $datos = $request->all();
            $validado = Validator::make($datos, [
                'numero' => ['required', 'integer'],
                'clave' => ['required', 'string', 'max:50'],
                'nombre' => ['required', 'string', 'max:512'],
                'descripcion' => ['required', 'string', 'max:2048'],
            ])->validate();

            $anexoHistorico = Anexo::find($id);
            $anexoHistorico->estatus = "Historico";

            $anexo = new Anexo();
            $anexo->numero = request('numero');
            $anexo->clave = request('clave');
            $anexo->nombre = request('nombre');
            $anexo->descripcion = request('descripcion');
            $anexo->formato = $anexoHistorico->formato;
            $anexo->estatus = "Activo";
            $anexo->origen_id = $anexoHistorico->origen_id;

            if ($anexo->numero == $anexoHistorico->numero
                && $anexo->clave == $anexoHistorico->clave
                && $anexo->nombre == $anexoHistorico->nombre
                && $anexo->descripcion == $anexoHistorico->descripcion
                && $anexo->formato == $anexoHistorico->formato) {
                return redirect()->back()->with('success', 'Anexo actualizado con éxito')->with('passData', $anexo);
            } else {
                $anexo->save();
                $anexoHistorico->save();
                return redirect()->back()->with('success', 'Anexo actualizado con éxito')->with('passData', $anexo);
            }

            return redirect()->back()->with('success', 'Anexo actualizado con éxito')->with('passData', $anexo);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function destroy($id)
    {
        $user = \Auth::user();
        if ($user->can('anexos.destroy')) {
            $user = Anexo::findOrFail($id);
            $user->delete();

            return redirect()->back()->with('success', 'Anexo eliminado con éxito');
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function actualiza_formato(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can('anexos.update')) {
            $anexoHistorico = Anexo::find($id);
            $anexoHistorico->estatus = "Historico";

            $anexo = new Anexo();
            $anexo->numero =$anexoHistorico->numero;
            $anexo->clave =$anexoHistorico->clave;
            $anexo->nombre =$anexoHistorico->nombre;
            $anexo->descripcion =$anexoHistorico->descripcion;
            $anexo->formato = $request->formato;
            $anexo->estatus = "Activo";
            $anexo->origen_id = $anexoHistorico->origen_id;

            if ($anexo->numero == $anexoHistorico->numero
                && $anexo->clave == $anexoHistorico->clave
                && $anexo->nombre == $anexoHistorico->nombre
                && $anexo->descripcion == $anexoHistorico->descripcion
                && $anexo->formato == $anexoHistorico->formato) {
                return redirect()->back()->with('success', 'Anexo actualizado con éxito')->with('passData', $anexo);
            } else {
                $anexo->save();
                $anexoHistorico->save();
                return redirect()->back()->with('success', 'Anexo actualizado con éxito')->with('passData', $anexo);
            }

            return redirect()->back()->with('success', 'Formato actualizado con éxito');
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }
}