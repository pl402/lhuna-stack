<?php

namespace App\Http\Controllers;

use App\Models\Anexo;
use App\Models\Area;
use App\Models\Entrega;
use App\Models\User;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;

class EntregasController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('entregas.index')) {
            $filtro = null;
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $entregas = Entrega::orderBy($orderBy['field'], $orderBy['sort'])
                    ->with('area', 'titular', 'enlace', 'receptor')
                    ->paginate(10)->onEachSide(1)
                    ->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $entregas = Entrega::with('area', 'titular', 'enlace', 'receptor')
                    ->paginate(10)->onEachSide(1)
                    ->appends(request()->query());
            }
            $todas_areas = Area::where('estatus', 'Activo')->get();
            $todos_formatos = Anexo::where('estatus', 'Activo')->get();
            $todos_usuarios = User::all();

            return Inertia::render('Entregas', compact('entregas', 'filtro', 'orderBy', 'todas_areas', 'todos_formatos', 'todos_usuarios'));
        } else {
            $request->session()->flash('error', 'No cuenta con los permisos necesarios para realizar esta acción.');

            return redirect()->back();
        }
    }

    public function search($filtro, Request $request)
    {
        $user = \Auth::user();
        if ($user->can('entregas.index')) {
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $entregas = $entregas = Entrega::filtro($filtro)
                    ->orderBy($orderBy['field'], $orderBy['sort'])
                    ->with('area', 'titular', 'enlace', 'receptor')
                    ->paginate(10)->onEachSide(1)
                    ->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $entregas = $entregas = Entrega::filtro($filtro)
                    ->with('area', 'titular', 'enlace', 'receptor')
                    ->paginate(10)->onEachSide(1)
                    ->appends(request()->query());
            }
            $todas_areas = Area::where('estatus', 'Activo')->get();
            $todos_formatos = Anexo::where('estatus', 'Activo')->get();
            $todos_usuarios = User::all();

            return Inertia::render('Entregas', compact('entregas', 'filtro', 'orderBy', 'todas_areas', 'todos_formatos', 'todos_usuarios'));
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('entregas.store')) {
            $datos = $request->all();
            $area = $datos['area'];
            if ($area != null) {
                $entrega_area_id = $area['value'];
            }
            //Checa si el area tiene una entrega en activa
            $entrega_activa = Entrega::where('area_id', $entrega_area_id)->where('estatus', 'Activo')->first();
            if ($entrega_activa != null) {
                return redirect()->back()->with('error', 'Ya existe una entrega activa para el área seleccionada.');
            }

            if (request('tipo') == 'Informe') {
                $validado = Validator::make($datos, [
                    'cargo_enlace' => ['string', 'max:255'],
                    'cargo_titular' => ['string', 'max:255'],
                    'mes' => ['required', 'integer', 'min:1', 'max:12'],
                    'anio' => ['required', 'integer', 'min:1900', 'max:2099'],
                    'tipo' => ['required', 'string', 'max:255'],
                ])->validate();
            } else {
                $validado = Validator::make($datos, [
                    'cargo_enlace' => ['string', 'max:255'],
                    'cargo_receptor' => ['string', 'max:255'],
                    'cargo_titular' => ['string', 'max:255'],
                    'mes' => ['required', 'integer', 'min:1', 'max:12'],
                    'anio' => ['required', 'integer', 'min:1900', 'max:2099'],
                    'tipo' => ['required', 'string', 'max:255'],
                ])->validate();
            }

            $titular = $datos['titular'];
            if ($titular != null) {
                $entrega_titular_id = $titular['value'];
            }
            $enlace = $datos['enlace'];
            if ($enlace != null) {
                $entrega_enlace_id = $enlace['value'];
            }
            $receptor = $datos['receptor'];
            if ($receptor != null) {
                $entrega_receptor_id = $receptor['value'];
            }
            $formatos = $datos['formatos'];
            if ($formatos != null) {
                $entrega_formatos = $formatos;
            } else {
                $entrega_formatos = [];
            }

            $entrega = Entrega::create([
                'cargo_enlace' => $datos['cargo_enlace'],
                'cargo_receptor' => $datos['cargo_receptor'],
                'cargo_titular' => $datos['cargo_titular'],
                'tipo' => $datos['tipo'],
                'mes' => $datos['mes'],
                'anio' => $datos['anio'],
                'area_id' => $entrega_area_id,
                'titular_id' => $entrega_titular_id,
                'enlace_id' => $entrega_enlace_id,
                'receptor_id' => $entrega_receptor_id,
                'formatos' => $entrega_formatos,
                'estatus' => 'Activo',
            ]);

            return redirect()->back()->with('success', 'Entrega creado con éxito')->with('passData', $entrega);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can('entregas.store')) {
            $datos = $request->all();
            if (request('tipo') == 'Informe') {
                $validado = Validator::make($datos, [
                    'cargo_enlace' => ['string', 'max:255'],
                    'cargo_titular' => ['string', 'max:255'],
                    'mes' => ['required', 'integer', 'min:1', 'max:12'],
                    'anio' => ['required', 'integer', 'min:1900', 'max:2099'],
                    'tipo' => ['required', 'string', 'max:255'],
                ])->validate();
            } else {
                $validado = Validator::make($datos, [
                    'cargo_enlace' => ['string', 'max:255'],
                    'cargo_receptor' => ['string', 'max:255'],
                    'cargo_titular' => ['string', 'max:255'],
                    'mes' => ['required', 'integer', 'min:1', 'max:12'],
                    'anio' => ['required', 'integer', 'min:1900', 'max:2099'],
                    'tipo' => ['required', 'string', 'max:255'],
                ])->validate();
            }
            $entrega = Entrega::find($id);
            $entrega->cargo_enlace = request('cargo_enlace');
            $entrega->cargo_receptor = request('cargo_receptor');
            $entrega->cargo_titular = request('cargo_titular');
            $entrega->mes = request('mes');
            $entrega->anio = request('anio');
            $entrega->tipo = request('tipo');

            $area = request('area');
            if ($area != null) {
                $entrega->area_id = $area['value'];
            }
            $titular = request('titular');
            if ($titular != null) {
                $entrega->titular_id = $titular['value'];
            }
            $enlace = request('enlace');
            if ($enlace != null) {
                $entrega->enlace_id = $enlace['value'];
            }
            $receptor = request('receptor');
            if ($receptor != null) {
                $entrega->receptor_id = $receptor['value'];
            }
            $formatos = request('formatos');
            if ($formatos != null) {
                $entrega->formatos = $formatos;
            } else {
                $entrega->formatos = [];
            }

            $entrega->save();

            return redirect()->back()->with('success', 'Entrega actualizado con éxito')->with('passData', $entrega);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function clone(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can('entregas.store')) {
            //Clona los datos de esta entrega en una entrega nueva con un nuevo uuid
            $datos = $request->all();
            if (request('tipo') == 'Informe') {
                $validado = Validator::make($datos, [
                    'cargo_enlace' => ['string', 'max:255'],
                    'cargo_titular' => ['string', 'max:255'],
                    'mes' => ['required', 'integer', 'min:1', 'max:12'],
                    'anio' => ['required', 'integer', 'min:1900', 'max:2099'],
                    'tipo' => ['required', 'string', 'max:255'],
                ])->validate();
            } else {
                $validado = Validator::make($datos, [
                    'cargo_enlace' => ['string', 'max:255'],
                    'cargo_receptor' => ['string', 'max:255'],
                    'cargo_titular' => ['string', 'max:255'],
                    'mes' => ['required', 'integer', 'min:1', 'max:12'],
                    'anio' => ['required', 'integer', 'min:1900', 'max:2099'],
                    'tipo' => ['required', 'string', 'max:255'],
                ])->validate();
            }

            $entrega = Entrega::find($id);
            $entrega_nueva = $entrega->replicate();

            $entrega_nueva->cargo_enlace = request('cargo_enlace');
            $entrega_nueva->cargo_receptor = request('cargo_receptor');
            $entrega_nueva->cargo_titular = request('cargo_titular');
            $entrega_nueva->mes = request('mes');
            $entrega_nueva->anio = request('anio');
            $entrega_nueva->tipo = request('tipo');

            $area = request('area');
            if ($area != null) {
                $entrega_nueva->area_id = $area['value'];
            }
            $titular = request('titular');
            if ($titular != null) {
                $entrega_nueva->titular_id = $titular['value'];
            }
            $enlace = request('enlace');
            if ($enlace != null) {
                $entrega_nueva->enlace_id = $enlace['value'];
            }
            $receptor = request('receptor');
            if ($receptor != null) {
                $entrega_nueva->receptor_id = $receptor['value'];
            }
            $formatos = request('formatos');
            if ($formatos != null) {
                $entrega_nueva->formatos = $formatos;
            } else {
                $entrega_nueva->formatos = [];
            }
            $entrega_nueva->uuid = \Str::uuid();
            $entrega_nueva->estatus = 'Activo';
            $entrega_nueva->save();
            //Busca los registros de la entrega original y los clona en la entrega nueva con el mismo uuid
            $registros = Registro::where('entrega_uuid', $entrega->uuid)->get();
            foreach ($registros as $registro) {
                $registro_nuevo = $registro->replicate();
                $registro_nuevo->entrega_uuid = $entrega_nueva->uuid;
                $registro_nuevo->save();
            }

            return redirect()->back()->with('success', 'Entrega actualizado con éxito')->with('passData', $entrega);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function destroy($id)
    {
        $user = \Auth::user();
        if ($user->can('entregas.destroy')) {
            $user = Entrega::findOrFail($id);
            $user->delete();

            return redirect()->back()->with('success', 'Entrega eliminado con éxito');
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function finaliza_enlace($id)
    {
        $user = \Auth::user();
        $entrega = Entrega::where('id', $id)->where('estatus', 'Activo')->where('enlace_id', $user->id)->first();
        if ($entrega != null) {
            $entrega->estatus = 'Finalizado';
            $entrega->fecha_entrega_enlace = date('Y-m-d H:i:s');
            $entrega->save();
            return \Redirect::route('captura', ['uuid' => $entrega->uuid])->with('success', 'Entrega finalizada con éxito')->with('passData', $entrega);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }

    }

    public function finaliza_titular($id)
    {
        $user = \Auth::user();
        $entrega = Entrega::where('id', $id)->where('estatus', 'Finalizado')->where('titular_id', $user->id)->first();
        if ($entrega != null) {
            $entrega->estatus = 'Entregado';
            $entrega->fecha_entrega_titular = date('Y-m-d H:i:s');
            $entrega->save();
            return \Redirect::route('captura', ['uuid' => $entrega->uuid])->with('success', 'Entrega finalizada con éxito')->with('passData', $entrega);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }

    }
}