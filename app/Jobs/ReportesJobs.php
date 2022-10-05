<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Area;
use App\Models\Reporte;
use App\Models\PDI;
use App\Models\Configuracion;

use App\Http\Helpers\PDFN;

class ReportesJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $uuid;
    public $id;
    public $reporte;

    public function __construct(string $uuid, int $id, Reporte $reporte)
    {
        $this->uuid = $uuid;
        $this->id = $id;
        $this->reporte = $reporte;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uuid = $this->uuid;
        $id = $this->id;
        $reporte = $this->reporte;
        $configuraciones = Configuracion::all();
        $snappy = \App::make("snappy.pdf");
        $borrar_archivos = [];
        $meses = [
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
        $fecha =
            date("j") . " de " . $meses[date("n") - 1] . " del " . date("Y");
        $fecha_corta = date("Y-m-d_H:i:s");
        $pdfi = new PDFN();

        $pathDB = "pdfs/" . $uuid . "_" . $fecha_corta . ".pdf";
        $path_all = storage_path("pdfs/" . $uuid . "_" . $fecha_corta . ".pdf");
        $path = storage_path(
            "pdfs/" . $uuid . "_" . $id . "_" . $fecha_corta . ".pdf"
        );
        $titulo = "";
        if ($id == 0) {
            // Reporte de PDI

            $pdi = PDI::where("uuid", $uuid)
                ->with(
                    "ejes.premisas.estrategias.lineas_accion.asignaciones.tareas.actividades",
                    "ejes.premisas.estrategias.lineas_accion.asignaciones.resp_evidencias"
                )
                ->first();
            $titulo = "Todas las áreas - PDI " . $pdi->nombre;

            $header = view(
                "Reportes.HeaderPDI",
                compact("configuraciones", "pdi", "fecha")
            );
            $footer = view(
                "Reportes.FooterPDI",
                compact("configuraciones", "pdi", "fecha")
            );
            $snappy = \PDF::loadView(
                "Reportes.PDI",
                compact("configuraciones", "pdi")
            )
                ->setOption("encoding", "utf-8")
                ->setOption("page-size", "letter")
                ->setOption("orientation", "landscape")
                ->setOption("header-html", $header)
                ->setOption("footer-font-size", 6)
                ->setOption("margin-left", "10")
                ->setOption("margin-right", "10")
                ->setOption("margin-top", "35")
                ->setOption("margin-bottom", "46.5")
                ->setOption("footer-html", $footer);

            if (\File::exists($path)) {
                \File::delete($path);
            }

            $snappy->save($path);
            $pageCount = $pdfi->setSourceFile($path);
            $pdfi->AliasNbPages();
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $pdfi->importPage($i);
                $pdfi->AddPage();
                $pdfi->useTemplate($tplId, ["adjustPageSize" => true]);
            }
            if (\File::exists($path)) {
                \File::delete($path);
            }

            if (\File::exists($path_all)) {
                \File::delete($path_all);
            }
            $pdfi->Output($path_all, "F");
        } else {
            // Reporte por área
            $area = Area::where("id", $id)
                ->with(
                    "asignaciones.tareas.actividades",
                    "asignaciones.pdi",
                    "asignaciones.eje",
                    "asignaciones.premisa",
                    "asignaciones.estrategia",
                    "asignaciones.lineaDeAccion",
                    "ancestry",
                    "enlace",
                    "titular"
                )
                ->first();
            $pdi = PDI::where("uuid", $uuid)->first();
            $titulo = $area->nombre . " - PDI " . $pdi->nombre;
            $id = $area->id;
            $header = view(
                "Reportes.Header",
                compact("configuraciones", "area", "pdi", "fecha")
            );
            $footer = view(
                "Reportes.Footer",
                compact("configuraciones", "area", "pdi", "fecha")
            );

            $snappy = \PDF::loadView(
                "Reportes.Area",
                compact("configuraciones", "area")
            )
                ->setOption("encoding", "utf-8")
                ->setOption("page-size", "letter")
                ->setOption("orientation", "landscape")
                ->setOption("header-html", $header)
                ->setOption("footer-font-size", 6)
                ->setOption("margin-left", "10")
                ->setOption("margin-right", "10")
                ->setOption("margin-top", "35")
                ->setOption("margin-bottom", "46.5")
                ->setOption("footer-html", $footer);

            if (\File::exists($path)) {
                \File::delete($path);
            }

            $path = storage_path(
                "pdfs/" . $uuid . "_" . $id . "_" . $fecha_corta . ".pdf"
            );

            $snappy->save($path);
            $pageCount = $pdfi->setSourceFile($path);
            $pdfi->AliasNbPages();
            for ($i = 1; $i <= $pageCount; $i++) {
                $tplId = $pdfi->importPage($i);
                $pdfi->AddPage();
                $pdfi->useTemplate($tplId, ["adjustPageSize" => true]);
            }
            if (\File::exists($path)) {
                \File::delete($path);
            }

            if (\File::exists($path_all)) {
                \File::delete($path_all);
            }
            $pdfi->Output($path_all, "F");
        }

        $reporte_g = Reporte::find($reporte->id);
        $reporte_g->archivo = $pathDB;
        $reporte_g->titulo = $titulo;
        $reporte_g->estatus = "Generado";
        $reporte_g->save();
    }
}