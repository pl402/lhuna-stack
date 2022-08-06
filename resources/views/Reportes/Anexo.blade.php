 <!DOCTYPE html>
 <html>
 @php
     $css = file_get_contents(public_path('css/app.css'));
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

         th {
             background: rgba(220, 220, 220, 1);
         }

         th,
         td {
             border: 1px solid rgba(210, 210, 210, 1);
         }
     </style>
 </head>

 <body>
     <div class="w-full">
         <table class="w-full whitespace-no-wrap">
             <thead>
                 <tr class="text-xs font-semibold text-left bg-gray-900">
                     <th class="p-1 pb-0 text-sm m-0 w-16 bg-gray-900">ID</th>
                     @foreach ($anexo->formato as $campo)
                         <th class=" text-slate-700 p-1 pb-0 text-sm m-0">
                             {{ $campo['nombre'] }}
                         </th>
                     @endforeach

                 </tr>
             </thead>
             <tbody class="bg-white">
                 @foreach ($registros as $registro)
                     <tr class="p-1 pb-0 text-sm m-0">
                         <td class="p-1 pb-0 text-sm m-0">{{ $registro->id }}</td>
                         @foreach ($anexo->formato as $campo)
                             <td class="p-1 pb-0 text-sm m-0">
                                 {{ $registro->data[$campo['id']] ?? '' }}</td>
                         @endforeach
                     </tr>
                 @endforeach
             </tbody>
         </table>
     </div>

 </body>

 </html>
