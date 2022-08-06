 <!DOCTYPE html>
 <html>
 @php
     $css = file_get_contents(public_path('css/app.css'));
     
     $meses = [
         '1' => 'Enero',
         '2' => 'Febrero',
         '3' => 'Marzo',
         '4' => 'Abril',
         '5' => 'Mayo',
         '6' => 'Junio',
         '7' => 'Julio',
         '8' => 'Agosto',
         '9' => 'Septiembre',
         '10' => 'Octubre',
         '11' => 'Noviembre',
         '12' => 'Diciembre',
     ];
     
     $actual = $area;
     $ancestros = [];
     $ancestros[] = $actual->nombre;
     
     while ($actual->ancestry) {
         $actual = $actual->ancestry;
         $ancestros[] = $actual->nombre;
     }
     $fecha = date('j') . ' de ' . $meses[date('n')] . ' del ' . date('Y') . ' a las ' . date('H:i:s');
     
     $nombreContra = $configuraciones->where('clave', 'Reporte / Firma / Nombre Contralor(a)')->first();
     $cargoContra = $configuraciones->where('clave', 'Reporte / Firma / Cargo Contralor(a)')->first();
     $leyendaContra = $configuraciones->where('clave', 'Reporte / Firma / Leyenda Contralor(a)')->first();
 @endphp

 <head>
     <meta charset="UTF-8" />
     <title>Reporte Anexo {{ $anexo->clave }}</title>
     <style>
         {{ $css }}

         /* */
         thead {
             display: table-header-group;
         }

         tfoot {
             display: table-row-group;
         }

         tr {
             page-break-inside: avoid;
         }

         table td,
         table td * {
             vertical-align: top;
         }

         .firma_linea {
             border-bottom: solid 1px #000;
             margin-bottom: 5px;
             min-height: 50px;
             width: 90%;
             margin-left: auto;
             margin-right: auto;
         }

         .wtercio {
             width: 33%;
         }

         .footer_footer {
             vertical-align: bottom;
             font-size: 7pt;
             color: #333333;
         }
     </style>
 </head>

 <body>
     <div class="w-full">
         <table class="w-full whitespace-no-wrap m-0 p-0">
             @if ($entrega->tipo == 'Entrega')
                 <tr>
                     <td class="wtercio text-center">
                         <div class="firma_linea"></div>
                     </td>
                     <td class="wtercio text-center">
                         <div class="firma_linea"></div>
                     </td>
                     <td class="wtercio text-center">
                         <div class="firma_linea"></div>
                     </td>
                 </tr>
                 <tr>
                     <td class="wtercio text-center text-sm">
                         <div class="font-bold">{{ $entrega->titular->name }}</div>
                         <div class="text-sm">{{ $entrega->cargo_titular }}</div>
                     </td>
                     <td class="wtercio text-center text-sm">
                         <div class="font-bold">{{ $nombreContra->valor }}</div>
                         <div class="text-sm">{{ $cargoContra->valor }}</div>
                         <div class="text-xs">{{ $leyendaContra->valor }}</div>
                     </td>
                     <td class="wtercio text-center text-sm">
                         <div class="font-bold">{{ $entrega->receptor->name }}</div>
                         <div class="text-sm">{{ $entrega->cargo_receptor }}</div>
                     </td>
                 </tr>
             @else
                 <tr>
                     <td class="wtercio text-center">
                     </td>
                     <td class="wtercio text-center">
                         <div class="firma_linea"></div>
                     </td>
                     <td class="wtercio text-center">
                     </td>
                 </tr>
                 <tr>
                     <td class="wtercio text-center text-sm">
                         <div class="font-bold"></div>
                         <div class="text-sm"></div>
                     </td>
                     <td class="wtercio text-center text-sm">
                         <div class="font-bold">{{ $entrega->titular->name }}</div>
                         <div class="text-sm">{{ $entrega->cargo_titular }}</div>
                         <div class="text-xs">RESPONSALBE DE INFORMACIÓN</div>
                     </td>
                     <td class="wtercio text-center text-sm">
                         <div class="font-bold"></div>
                         <div class="text-sm"></div>
                     </td>
                 </tr>
             @endif
             <tr>
                 <td class="wtercio text-left text-xs footer_footer">
                     <br />
                     Página:
                 </td>
                 <td class="wtercio text-center text-xs footer_footer">
                     Generado el {{ $fecha }}
                 </td>
                 <td class="wtercio text-right text-xs footer_footer">
                     Sistema Entrega Recepción
                 </td>
             </tr>
         </table>
     </div>
 </body>

 </html>
