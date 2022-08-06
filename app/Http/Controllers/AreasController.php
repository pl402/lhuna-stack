<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AreasController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('areas.index')) {
            $filtro = null;
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $areas = Area::orderBy($orderBy['field'], $orderBy['sort'])->with('parent', 'children')->paginate(10)->onEachSide(1)->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $areas = Area::with('parent', 'children')->paginate(10)->onEachSide(1)->appends(request()->query());
            }
            $todas_las_areas = Area::all();

            return Inertia::render('Areas', compact('areas', 'filtro', 'orderBy', 'todas_las_areas'));
        } else {
            $request->session()->flash('error', 'No cuenta con los permisos necesarios para realizar esta acción.');

            return redirect()->back();
        }
    }

    public function search($filtro, Request $request)
    {
        $user = \Auth::user();
        if ($user->can('areas.index')) {
            if ($request->has('orderBy')) {
                $orderBy = $request->orderBy;
                $areas = Area::filtro($filtro)->orderBy($orderBy['field'], $orderBy['sort'])->with('parent', 'children')->paginate(10)->onEachSide(1)->appends(request()->query());
            } else {
                $orderBy = ['field' => 'id', 'sort' => 'asc'];
                $areas = Area::filtro($filtro)->with('parent', 'children')->paginate(10)->onEachSide(1)->appends(request()->query());
            }
            $todas_las_areas = Area::all();

            return Inertia::render('Areas', compact('areas', 'filtro', 'orderBy', 'todas_las_areas'));
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->can('areas.store')) {
            $datos = $request->all();
            $validado = Validator::make($datos, [
                'nombre' => ['required', 'string', 'max:255'],
                'descripcion' => ['required', 'string', 'max:255'],
            ])->validate();
            $area = Area::create([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'],
            ]);

            return redirect()->back()->with('success', 'Area creado con éxito')->with('passData', $area);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can('areas.update')) {
            $datos = $request->all();
            $validado = Validator::make($datos, [
                'nombre' => ['required', 'string', 'max:255'],
                'descripcion' => ['required', 'string', 'max:255'],
            ])->validate();
            $area = Area::find($id);
            $area->nombre = request('nombre');
            $area->descripcion = request('descripcion');
            $area_padre = request('area_padre');
            if ($area_padre != null) {
                $area->area_padre = $area_padre['value'];
            }
            $area->save();

            return redirect()->back()->with('success', 'Área actualizada con éxito')->with('passData', $area);
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function destroy($id)
    {
        $user = \Auth::user();
        if ($user->can('areas.destroy')) {
            $user = Area::findOrFail($id);
            $user->delete();

            return redirect()->back()->with('success', 'Area eliminado con éxito');
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }

    public function actualiza_formato(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can('areas.update')) {
            $area = Area::find($id);
            $area->formato = $request->formato;
            $area->save();

            return redirect()->back()->with('success', 'Formato actualizado con éxito');
        } else {
            return redirect()->back()->with('error', 'No cuenta con los permisos necesarios para realizar esta acción.');
        }
    }
}