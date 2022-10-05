<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class AreasController extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        if ($user->can("areas.index")) {
            $filtro = null;
            if ($request->has("orderBy")) {
                $orderBy = $request->orderBy;
                $areas = Area::orderBy($orderBy["field"], $orderBy["sort"])
                    ->with("parent", "children")
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            } else {
                $orderBy = ["field" => "id", "sort" => "asc"];
                $areas = Area::with("parent", "children", "enlace", "titular")
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            }
            $todas_las_areas = Area::all();
            $todos_los_enlaces = User::all();

            return Inertia::render(
                "Areas",
                compact(
                    "areas",
                    "filtro",
                    "orderBy",
                    "todas_las_areas",
                    "todos_los_enlaces"
                )
            );
        } else {
            $request
                ->session()
                ->flash(
                    "error",
                    "No cuenta con los permisos necesarios para realizar esta acción."
                );

            return redirect()->back();
        }
    }

    public function search($filtro, Request $request)
    {
        $user = \Auth::user();
        if ($user->can("areas.index")) {
            if ($request->has("orderBy")) {
                $orderBy = $request->orderBy;
                $areas = Area::filtro($filtro)
                    ->orderBy($orderBy["field"], $orderBy["sort"])
                    ->with("parent", "children", "enlace", "titular")
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            } else {
                $orderBy = ["field" => "id", "sort" => "asc"];
                $areas = Area::filtro($filtro)
                    ->with("parent", "children")
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            }
            $todas_las_areas = Area::all();
            $todos_los_enlaces = User::all();

            return Inertia::render(
                "Areas",
                compact(
                    "areas",
                    "filtro",
                    "orderBy",
                    "todas_las_areas",
                    "todos_los_enlaces"
                )
            );
        } else {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "No cuenta con los permisos necesarios para realizar esta acción."
                );
        }
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->can("areas.store")) {
            $datos = $request->all();
            $validado = Validator::make($datos, [
                "nombre" => ["required", "string", "max:255"],
            ])->validate();
            $area = Area::create([
                "nombre" => $datos["nombre"],
            ]);

            return redirect()
                ->back()
                ->with("success", "Área creado con éxito")
                ->with("passData", $area);
        } else {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "No cuenta con los permisos necesarios para realizar esta acción."
                );
        }
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can("areas.update")) {
            $datos = $request->all();
            $validado = Validator::make($datos, [
                "nombre" => ["required", "string", "max:255"],
            ])->validate();
            $area = Area::find($id);
            $area->nombre = request("nombre");
            $area->enlace_cargo = request("enlace_cargo");
            $area->titular_cargo = request("titular_cargo");
            $area_padre = request("area_padre");
            if ($area_padre != null) {
                if ($area_padre["value"] == $area->id) {
                    return redirect()
                        ->back()
                        ->with(
                            "error",
                            "Un área no puede depender de si misma."
                        );
                }

                $ancestro_padre = Area::where("id", $area_padre["value"])
                    ->with("ancestry")
                    ->first();

                if ($ancestro_padre) {
                    while ($ancestro_padre->ancestry) {
                        $ancestro_padre = $ancestro_padre->ancestry;
                        if ($ancestro_padre->id == $id) {
                            return redirect()
                                ->back()
                                ->with(
                                    "error",
                                    "Un área no puede depender de algún área en su árbol de dependencias."
                                );
                        }
                    }
                }

                $area->area_padre = $area_padre["value"];
            }

            $enlace = request("enlace");
            if ($enlace != null) {
                $area->enlace_id = $enlace["value"];
            }
            $titular = request("titular");
            if ($titular != null) {
                $area->titular_id = $titular["value"];
            }
            $area->save();

            return redirect()
                ->back()
                ->with("success", "Área actualizada con éxito")
                ->with("passData", $area);
        } else {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "No cuenta con los permisos necesarios para realizar esta acción."
                );
        }
    }

    public function destroy($id)
    {
        $user = \Auth::user();
        if ($user->can("areas.destroy")) {
            $user = Area::findOrFail($id);
            $user->delete();

            return redirect()
                ->back()
                ->with("success", "Área eliminado con éxito");
        } else {
            return redirect()
                ->back()
                ->with(
                    "error",
                    "No cuenta con los permisos necesarios para realizar esta acción."
                );
        }
    }
}