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
     
     $fecha = date('j') . ' de ' . $meses[date('n')] . ' del ' . date('Y') . ' a las ' . date('H:i:s');
     
     $nombreContra = $configuraciones->where('clave', 'Reporte / Firma / Nombre Contralor(a)')->first();
     $cargoContra = $configuraciones->where('clave', 'Reporte / Firma / Cargo Contralor(a)')->first();
     $leyendaContra = $configuraciones->where('clave', 'Reporte / Firma / Leyenda Contralor(a)')->first();
 @endphp

 <head>
     <meta charset="UTF-8" />
     <title>Reporte Footer</title>
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

         .w40 {
             width: 40%;
         }

         .w20 {
             width: 20%;
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
             @if (true)
                 <tr>
                     <td class="w40 text-center text-xs">
                         Reportó
                     </td>
                     <td class="w20 text-center text-xs">

                     </td>
                     <td class="w40 text-center text-xs">
                         Revisó
                     </td>
                 </tr>
                 <tr>
                     <td class="w40 text-center">
                         <div class="firma_linea"></div>
                     </td>
                     <td class="w20 text-center">

                     </td>
                     <td class="w40 text-center">
                         <div class="firma_linea"></div>
                     </td>
                 </tr>
                 <tr>
                     <td class="w40 text-center text-sm">
                         <div class="font-bold">{{ $area->enlace ? $area->enlace->titulo : '' }}
                             {{ $area->enlace ? $area->enlace->name : '--' }}</div>
                         <div class="text-sm">
                             {{ $area->enlace_cargo && $area->enlace_cargo != '' ? $area->enlace_cargo : '-' }}</div>
                         <div class="text-xs">Enlace</div>
                     </td>
                     <td class="w20 text-center text-sm">

                     </td>
                     <td class="w40 text-center text-sm">
                         <div class="font-bold">{{ $area->titular ? $area->titular->titulo : '' }}
                             {{ $area->titular ? $area->titular->name : '--' }}</div>
                         <div class="text-sm">
                             {{ $area->titular_cargo && $area->titular_cargo != '' ? $area->titular_cargo : '-' }}</div>
                         <div class="text-xs">Revisó</div>
                     </td>
                 </tr>
             @else
                 <tr>
                     <td class="w40 text-center">
                     </td>
                     <td class="w20 text-center">
                         <div class="firma_linea"></div>
                     </td>
                     <td class="w40 text-center">
                     </td>
                 </tr>
                 <tr>
                     <td class="w40 text-center text-sm">
                         <div class="font-bold"></div>
                         <div class="text-sm"></div>
                         <div class="text-xs"></div>
                     </td>
                     <td class="w20 text-center text-sm">
                         <div class="font-bold"> </div>
                         <div class="text-sm"> </div>
                         <div class="text-xs">RESPONSALBE DE INFORMACIÓN</div>
                     </td>
                     <td class="w40 text-center text-sm">
                         <div class="font-bold"></div>
                         <div class="text-sm"></div>
                         <div class="text-xs"></div>
                     </td>
                 </tr>
             @endif
         </table>
     </div>
 </body>

 </html>
