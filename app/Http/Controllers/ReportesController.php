<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\proyecto;
use App\Models\Configuracion;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Jobs\ReportesJobs;

class ReportesController extends Controller
{
    public $meses = [
        "Enero",
        "Febrero",
        "Marzo",
        "Abril",
        "Mayo",
        "Junio",
        "Julio",
        "Agosto",
        "Septiembre",
        "Octubre",
        "Noviembre",
        "Diciembre",
    ];

    public function index(Request $request)
    {
        $user = \Auth::user();
        if ($user->can("reportes.index")) {
            $filtro = null;
            if ($request->has("orderBy")) {
                $orderBy = $request->orderBy;
                $reportes = Reporte::with("proyecto", "area", "user")
                    ->orderBy($orderBy["field"], $orderBy["sort"])
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            } else {
                $orderBy = ["field" => "id", "sort" => "asc"];
                $reportes = Reporte::with("proyecto", "area", "user")
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            }

            return Inertia::render(
                "Reportes",
                compact("reportes", "filtro", "orderBy")
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
        if ($user->can("reportes.index")) {
            if ($request->has("orderBy")) {
                $orderBy = $request->orderBy;
                $reportes = Reporte::filtro($filtro)
                    ->orderBy($orderBy["field"], $orderBy["sort"])
                    ->with("proyecto", "area", "user")
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            } else {
                $orderBy = ["field" => "id", "sort" => "asc"];
                $reportes = Reporte::filtro($filtro)
                    ->with("proyecto", "area", "user")
                    ->paginate(10)
                    ->onEachSide(1)
                    ->appends(request()->query());
            }

            return Inertia::render(
                "Reportes",
                compact("reportes", "filtro", "orderBy")
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

    public function area(Request $request, $uuid, $id)
    {
        $titulo = "Reporte pendiente...";
        $user = \Auth::user();
        $reporte = Reporte::create([
            "proyecto_uuid" => $uuid,
            "area_id" => $id,
            "estatus" => "Generando",
            "titulo" => $titulo,
            "user_id" => $user->id,
        ]);

        ReportesJobs::dispatch($uuid, $id, $reporte);
        return redirect()
            ->back()
            ->with("success", "Generando reporte...");
    }

    public function area_list(Request $request, $uuid, $id)
    {
        $reportes_area = Reporte::where("proyecto_uuid", $uuid)
            ->where("area_id", $id)
            ->orderBy("id", "desc")
            ->limit(5)
            ->get();

        return response()->json($reportes_area);
    }

    public function descarga(Request $request, $uuid)
    {
        $reporte = Reporte::where("uuid", $uuid)
            ->where("Estatus", "Generado")
            ->first();
        $archivo = $reporte->archivo;
        $titulo = $reporte->titulo;

        return response()->download(storage_path($archivo), $titulo . ".pdf");
    }
}