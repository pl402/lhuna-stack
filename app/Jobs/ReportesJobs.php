<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Collection;
Use App\Models\Anexo;
Use App\Models\Entrega;
Use App\Models\Area;
use App\Models\Reporte;

use App\Http\Helpers\PDFN;

class ReportesJobs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $anexo;
    public $configuraciones;
    public $entrega;
    public $area;
    public $registros;
    public $reporte;
    
    public function __construct(Anexo $anexo, Collection $configuraciones, Entrega $entrega, Area $area, Collection $registros, Reporte $reporte)
    {
        $this->anexo = $anexo;
        $this->configuraciones = $configuraciones;
        $this->entrega = $entrega;
        $this->area = $area;
        $this->registros = $registros;
        $this->reporte = $reporte;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $anexo = $this->anexo;
        $configuraciones = $this->configuraciones;
        $entrega = $this->entrega;
        $area = $this->area;
        $registros = $this->registros;
        $uuid = $entrega->uuid;
        $id = $anexo->id;
        $reporte = $this->reporte;
        $snappy = \App::make('snappy.pdf');
        $rand = \Str::random(5);
        $meses = [
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

        $header = view('Reportes.Header', compact('anexo', 'entrega', 'area', 'configuraciones'));
        $footer = view('Reportes.Footer', compact('anexo', 'entrega', 'area', 'configuraciones'));
        // fecha de creacion del pdf en formato dia del mes del año en español
        $fecha = date('j') . ' de ' . $meses[date('n') - 1] . ' del ' . date('Y');
        $fecha_corta = date('Y-m-d_H:i:s');
        $snappy = \PDF::loadView('Reportes.Anexo', compact('anexo', 'uuid', 'id', 'registros', 'entrega'))
            ->setOption('encoding', 'utf-8')
            ->setOption('page-size', 'letter')
            ->setOption('orientation', 'landscape')
            ->setOption('header-html', $header)
            ->setOption('footer-font-size', 6)
            ->setOption('margin-left', '10')
            ->setOption('margin-right', '10')
            ->setOption('margin-top', '35')
            ->setOption('margin-bottom', '40')
            ->setOption('footer-html', $footer);
        $path = storage_path('pdfs/anexos/'.$uuid.'_'.$id.'_'.$fecha_corta.'_'.$rand.'.pdf');
        $pathDB = 'pdfs/anexos/'.$uuid.'_'.$id.'_'.$fecha_corta.'_'.$rand.'.pdf';
        if(is_writable($path)){
            unlink($path);
        }
        $snappy->save($path);
        $pdfi = new PDFN();
        $pageCount = $pdfi->setSourceFile($path);
        $pdfi->AliasNbPages();
        for ($i=1; $i <= $pageCount; $i++) {
            $tplId = $pdfi->importPage($i);
            $pdfi->AddPage();
            $pdfi->useTemplate($tplId, ['adjustPageSize' => true]);
        }
        unlink($path);
        $pdfi->Output($path, 'F');

        $reporte_g = Reporte::find($reporte->id);
        $reporte_g->archivo = $pathDB;
        $reporte_g->estatus = 'Generado';
        $reporte_g->save();
    }
}