 <!DOCTYPE html>
 <html>
 @php
     $css = file_get_contents(public_path('css/app.css'));
     
     $ejes = [];
     $sumatorias_terea = [];
     $sumatorias_linea = [];
     $sumatorias_estrategia = [];
     $sumatorias_premisa = [];
     $sumatorias_eje = [];
     $sumatoria_area = 0;
     foreach ($area->asignaciones as $asignacion) {
         $ejes[$asignacion->eje->id]['eje'] = $asignacion->eje;
         $ejes[$asignacion->eje->id]['premisas'][$asignacion->premisa->id]['premisa'] = $asignacion->premisa;
         $ejes[$asignacion->eje->id]['premisas'][$asignacion->premisa->id]['estrategias'][$asignacion->estrategia->id]['estrategia'] = $asignacion->estrategia;
         $ejes[$asignacion->eje->id]['premisas'][$asignacion->premisa->id]['estrategias'][$asignacion->estrategia->id]['lineas'][$asignacion->lineaDeAccion->id]['linea'] = $asignacion->lineaDeAccion;
         $ejes[$asignacion->eje->id]['premisas'][$asignacion->premisa->id]['estrategias'][$asignacion->estrategia->id]['lineas'][$asignacion->lineaDeAccion->id]['tareas'] = $asignacion->tareas;
         $ejes[$asignacion->eje->id]['premisas'][$asignacion->premisa->id]['estrategias'][$asignacion->estrategia->id]['lineas'][$asignacion->lineaDeAccion->id]['avance'] = 0;
     }
     
     foreach ($ejes as $eje) {
         $sumatorias_eje[$eje['eje']->id] = 0;
         foreach ($eje['premisas'] as $premisa) {
             $sumatorias_premisa[$premisa['premisa']->id] = 0;
             foreach ($premisa['estrategias'] as $estrategia) {
                 $sumatorias_estrategia[$estrategia['estrategia']->id] = 0;
                 foreach ($estrategia['lineas'] as $linea) {
                     $sumatorias_linea[$linea['linea']->id] = 0;
                     foreach ($linea['tareas'] as $tarea) {
                         $sumatorias_terea[$tarea->id] = 0;
                         foreach ($tarea->actividades as $actividad) {
                             if ($actividad->revisado) {
                                 $sumatorias_terea[$tarea->id] += $actividad->avance;
                                 $sumatorias_linea[$linea['linea']->id] += ($actividad->avance * $tarea->ponderacion) / 100;
                             }
                         }
                     }
                     $sumatorias_estrategia[$estrategia['estrategia']->id] += $sumatorias_linea[$linea['linea']->id] / count($estrategia['lineas']);
                 }
                 $sumatorias_premisa[$premisa['premisa']->id] += $sumatorias_estrategia[$estrategia['estrategia']->id] / count($premisa['estrategias']);
             }
             $sumatorias_eje[$eje['eje']->id] += $sumatorias_premisa[$premisa['premisa']->id] / count($eje['premisas']);
         }
         $sumatoria_area += $sumatorias_eje[$eje['eje']->id] / count($ejes);
     }
 @endphp

 <head>
     <meta charset="UTF-8" />
     <title>Datos</title>
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

         th {
             background: rgba(220, 220, 220, 1);
         }

         th,
         td {
             border: 1px solid rgba(210, 210, 210, 1);
         }

         .text-green-500 {
             color: #38c172;
         }

         .text-red-500 {
             color: #e3342f;
         }

         .text-right {
             text-align: right;
         }

         .text-center {
             text-align: center;
         }

         .text-md {
             font-size: 1.2rem;
         }

         .text-sm {
             font-size: 0.875rem;
         }

         .text-xs {
             font-size: 0.75rem;
         }

         .w-10 {
             width: 2.5rem;
         }

         .w-16 {
             width: 4rem;
         }

         .bg-gray-600 {
             background: rgba(200, 200, 200, 1);
         }

         .bg-gray-500 {
             background: rgba(210, 210, 210, 1);
         }

         .bg-gray-400 {
             background: rgba(220, 220, 220, 1);
         }

         .bg-gray-300 {
             background: rgba(230, 230, 230, 1);
         }

         .bg-gray-200 {
             background: rgba(240, 240, 240, 1);
         }

         .bg-gray-100 {
             background: rgba(250, 250, 250, 1);
         }

         .bg-green-50 {
             background: rgba(252, 252, 252, 1);
         }
     </style>
 </head>

 <body>
     <div class="w-full">
         <!-- tabla con cabecera de 5 columnas -->
         <table class="w-full whitespace-no-wrap m-0 p-0">
             <thead>
                 <tr class="text-xs font-semibold text-left bg-gray-600">
                     <th class="p-1 pb-0 text-sm m-0 w-16 bg-gray-600">Clave</th>
                     <th class="p-1 pb-0 text-sm m-0 bg-gray-600" colspan="2">Descripción</th>
                     <th class="p-1 pb-0 text-sm m-0 w-16 bg-gray-600">Ponderación</th>
                     <th class="p-1 pb-0 text-sm m-0 w-16 bg-gray-600">Avance</th>
                 </tr>
             </thead>
             <tbody>
                 <tr class="p-1 pb-0 text-sm m-0 bg-gray-600">
                     <td class="p-1 pb-0 text-sm m-0 text-right"></td>
                     <td class="p-1 pb-0 text-sm m-0 " colspan="2">
                         <b>Área:</b>
                         {{ $area->nombre }}
                     </td>
                     <td class="p-1 pb-0 text-sm m-0 text-center">
                     </td>
                     <td class="p-1 pb-0 text-sm m-0 text-center">
                         <b>{{ round($sumatoria_area, 2) }}%</b>
                     </td>
                 </tr>
                 @foreach ($ejes as $eje)
                     <tr class="p-1 pb-0 text-sm m-0 bg-gray-500">
                         <td class="p-1 pb-0 text-sm m-0 text-right">{{ $eje['eje']->nombre }}</td>
                         <td class="p-1 pb-0 text-sm m-0 " colspan="2">
                             <b>Eje:</b>
                             {{ $eje['eje']->descripcion }}
                         </td>
                         <td class="p-1 pb-0 text-sm m-0 text-center">
                             {{ round(100 / count($ejes), 2) }}%
                         </td>
                         <td class="p-1 pb-0 text-sm m-0 text-center">
                             <b>{{ round($sumatorias_eje[$eje['eje']->id], 2) }}%</b>
                         </td>
                     </tr>
                     @foreach ($eje['premisas'] as $premisa)
                         <tr class="p-1 pb-0 text-sm m-0 bg-gray-400">
                             <td class="p-1 pb-0 text-sm m-0 text-right">{{ $premisa['premisa']->nombre }}</td>
                             <td class="p-1 pb-0 text-sm m-0" colspan="2">
                                 <b>Premisa:</b>
                                 {{ $premisa['premisa']->descripcion }}
                             </td>
                             <td class="p-1 pb-0 text-sm m-0 text-center">
                                 {{ round(100 / count($eje['premisas']), 2) }}%
                             </td>
                             <td class="p-1 pb-0 text-sm m-0 text-center">
                                 <b>{{ round($sumatorias_premisa[$premisa['premisa']->id], 2) }}%</b>
                             </td>
                         </tr>
                         @foreach ($premisa['estrategias'] as $estrategia)
                             <tr class="p-1 pb-0 text-sm m-0 bg-gray-300">
                                 <td class="p-1 pb-0 text-sm m-0 text-right">{{ $estrategia['estrategia']->nombre }}
                                 </td>
                                 <td class="p-1 pb-0 text-sm m-0" colspan="2">
                                     <b>Estrategia:</b>
                                     {{ $estrategia['estrategia']->descripcion }}
                                 </td>
                                 <td class="p-1 pb-0 text-sm m-0 text-center">
                                     {{ round(100 / count($premisa['estrategias']), 2) }}%
                                 </td>
                                 <td class="p-1 pb-0 text-sm m-0 text-center">
                                     <b>{{ round($sumatorias_estrategia[$estrategia['estrategia']->id], 2) }}%</b>
                                 </td>
                             </tr>
                             @foreach ($estrategia['lineas'] as $linea)
                                 <tr class="p-1 pb-0 text-sm m-0 bg-gray-200">
                                     <td class="p-1 pb-0 text-sm m-0 text-right">
                                         {{ $linea['linea']->nombre }}
                                     </td>
                                     <td class="p-1 pb-0 text-sm m-0" colspan="2">
                                         <b>Linea de Acción:</b>
                                         {{ $linea['linea']->descripcion }}
                                     </td>
                                     <td class="p-1 pb-0 text-sm m-0 text-center">
                                         {{ round(100 / count($estrategia['lineas']), 2) }}%</td>
                                     <td class="p-1 pb-0 text-sm m-0 text-center">
                                         <b>{{ round($sumatorias_linea[$linea['linea']->id], 2) }}%</b>
                                     </td>
                                 </tr>
                                 @foreach ($linea['tareas'] as $tarea)
                                     <tr class="p-1 pb-0 text-sm m-0 bg-gray-100">
                                         <td class="p-1 pb-0 text-sm m-0">

                                         </td>
                                         <td class="p-1 pb-0 text-sm m-0" colspan="2">
                                             <b>Tarea:</b>
                                             {{ $tarea->nombre }}
                                         </td>
                                         <td class="p-1 pb-0 text-sm m-0 text-center">
                                             {{ round($tarea->ponderacion, 2) }}%
                                         </td>
                                         <td class="p-1 pb-0 text-sm m-0 text-center">
                                             {{ round($sumatorias_terea[$tarea->id], 2) }}%
                                         </td>
                                     </tr>
                                     @foreach ($tarea->actividades as $actividad)
                                         <tr class="p-1 pb-0 text-sm m-0">
                                             <td class="p-1 pb-0 text-sm m-0 w-16">

                                             </td>
                                             <td class="p-1 pb-0 text-sm m-0 w-10">

                                             </td>
                                             <td class="p-1 pb-0 text-sm m-0">
                                                 <b>Actividad:</b>
                                                 {{ $actividad->nombre }}
                                             </td>
                                             <td class="p-1 pb-0 text-sm m-0 text-center">
                                                 {{ round($actividad->avance, 2) }}%
                                             </td>
                                             <td class="p-1 pb-0 text-md m-0 text-center">
                                                 @if ($actividad->revisado)
                                                     <span class="text-green-500">&#10004;</span>
                                                 @else
                                                     <span class="text-red-500">&#10006;</span>
                                                 @endif
                                             </td>
                                         </tr>
                                     @endforeach
                                 @endforeach
                             @endforeach
                         @endforeach
                     @endforeach
                 @endforeach
             </tbody>
         </table>

     </div>

 </body>

 </html>
