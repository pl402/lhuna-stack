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
     
     $primeraLinea = $configuraciones->where('clave', 'Reporte / Titulo / Primera Línea')->first();
     $segundaLinea = $configuraciones->where('clave', 'Reporte / Titulo / Segunda Línea')->first();
     $terceraLinea = $configuraciones->where('clave', 'Reporte / Titulo / Tercera Línea')->first();
     $logoIzquierda = $configuraciones->where('clave', 'Reporte / Logo / Izquierda')->first();
     $logoDerecha = $configuraciones->where('clave', 'Reporte / Logo / Derecha')->first();
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
     </style>
 </head>

 <body>
     <div class="w-full">
         <table class="w-full whitespace-no-wrap m-0 p-0">
             <tr>
                 <td rowspan="4" class="w-24 m-0 p-0">
                     <img src="{{ public_path($logoIzquierda->valor) }}" alt="Logo" class="w-24 m-0 p-0 mb-2">
                 </td>
                 <td colspan='2' class="text-sm font-bold text-center m-0 p-0">
                     {{ $primeraLinea->valor }}
                 </td>
                 <td rowspan="4" class="w-24 m-0 p-0 text-right">
                     <img src="{{ public_path($logoDerecha->valor) }}" alt="Logo"
                         class="w-24 m-0 p-0 mb-2 ml-auto">
                 </td>
             </tr>
             <tr>
                 <td colspan='2' class="text-sm font-bold text-center">{{ $segundaLinea->valor }}</td>
             </tr>
             <tr>
                 <td colspan='2' class="text-sm font-bold text-center">{{ $terceraLinea->valor }}</td>
                 </td>
             </tr>
             <tr>
                 <td colspan='2' class="text-sm font-semibold text-center">
                     @foreach ($ancestros as $ancestro)
                         {{ $ancestro }}
                         @if (!$loop->last)
                             /
                         @endif
                     @endforeach
                 </td>
             </tr>
             <tr>
                 <td colspan='2' class="text-sm text-left pb-2"><span class="font-bold">ANEXO:
                     </span>{{ $anexo->numero }} /
                     {{ $anexo->clave }} /
                     {{ $anexo->nombre }}
                 </td>
                 <td colspan='2' class="text-sm text-right pb-2"><span class="font-bold">{{ $entrega->tipo }}:
                     </span>
                     {{ $meses[$entrega->mes] }} /
                     {{ $entrega->anio }}
                 </td>
             </tr>
         </table>
     </div>
 </body>

 </html>
