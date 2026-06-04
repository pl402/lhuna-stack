<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\CodeGeneratorService;

class DesignerSeedDemoCommand extends Command
{
    protected $signature = 'designer:demo';
    protected $description = 'Crea un esquema de prueba (Curso y Materia) con relaciones Muchos a Muchos, genera su código y aplica las migraciones';

    public function handle()
    {
        $this->info('Iniciando carga de base de datos de prueba del diseñador...');

        // Reset anterior para limpiar archivos viejos
        $this->call('designer:reset');

        // 1. Definir esquema demo
        $demoSchema = [
            [
                "id" => "system_user",
                "name" => "User",
                "table" => "users",
                "plural_label" => "Usuarios",
                "icon" => "users",
                "position" => [
                    "x" => 50,
                    "y" => 200
                ],
                "fields" => [
                    [
                        "name" => "id",
                        "type" => "id",
                        "label" => "ID",
                        "required" => true,
                        "unique" => true,
                        "show_in_table" => true
                    ],
                    [
                        "name" => "name",
                        "type" => "string",
                        "label" => "Nombre",
                        "show_in_table" => true
                    ],
                    [
                        "name" => "email",
                        "type" => "string",
                        "label" => "Email",
                        "show_in_table" => true
                    ],
                    [
                        "name" => "titulo",
                        "type" => "string",
                        "label" => "Título",
                        "show_in_table" => true
                    ]
                ],
                "relations" => [
                    [
                        "type" => "belongsToMany",
                        "target" => "Curso",
                        "foreign_key" => null,
                        "relation_name" => "cursos"
                    ]
                ],
                "is_system" => true,
                "ui_layout" => null
            ],
            [
                "id" => "demo_curso",
                "name" => "Curso",
                "table" => "cursos",
                "plural_label" => "Cursos",
                "icon" => "book",
                "position" => [
                    "x" => 400,
                    "y" => 100
                ],
                "fields" => [
                    [
                        "name" => "id",
                        "type" => "id",
                        "label" => "ID",
                        "required" => true,
                        "unique" => true,
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "nombre",
                        "type" => "string",
                        "label" => "Nombre del Curso",
                        "required" => true,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "text",
                        "show_in_table" => true,
                        "searchable" => true,
                        "sortable" => true
                    ],
                    [
                        "name" => "imagen",
                        "type" => "string",
                        "label" => "Imagen de Portada",
                        "required" => false,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "file",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => false
                    ],
                    [
                        "name" => "cupo",
                        "type" => "integer",
                        "label" => "Cupo Máximo",
                        "required" => false,
                        "unique" => false,
                        "default" => 30,
                        "input_type" => "number",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "activo",
                        "type" => "boolean",
                        "label" => "Curso Activo",
                        "required" => false,
                        "unique" => false,
                        "default" => true,
                        "input_type" => "toggle",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "fecha_inicio",
                        "type" => "date",
                        "label" => "Fecha de Inicio",
                        "required" => false,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "date",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ]
                ],
                "relations" => [
                    [
                        "type" => "belongsToMany",
                        "target" => "Materia",
                        "foreign_key" => null,
                        "relation_name" => "materias"
                    ],
                    [
                        "type" => "belongsToMany",
                        "target" => "User",
                        "foreign_key" => null,
                        "relation_name" => "users"
                    ]
                ],
                "is_system" => false,
                "ui_layout" => [
                    "sections" => [
                        [
                            "id" => "sec-info",
                            "title" => "Información General",
                            "columns" => 2,
                            "fields" => [
                                "nombre",
                                "cupo",
                                "imagen"
                            ]
                        ],
                        [
                            "id" => "sec-settings",
                            "title" => "Ajustes y Relaciones",
                            "columns" => 1,
                            "fields" => [
                                "activo",
                                "fecha_inicio",
                                "materias",
                                "users"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "id" => "demo_materia",
                "name" => "Materia",
                "table" => "materias",
                "plural_label" => "Materias",
                "icon" => "table",
                "position" => [
                    "x" => 800,
                    "y" => 150
                ],
                "fields" => [
                    [
                        "name" => "id",
                        "type" => "id",
                        "label" => "ID",
                        "required" => true,
                        "unique" => true,
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "nombre",
                        "type" => "string",
                        "label" => "Nombre de la Materia",
                        "required" => true,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "text",
                        "show_in_table" => true,
                        "searchable" => true,
                        "sortable" => true
                    ],
                    [
                        "name" => "codigo",
                        "type" => "string",
                        "label" => "Código de Materia",
                        "required" => true,
                        "unique" => true,
                        "default" => null,
                        "input_type" => "text",
                        "show_in_table" => true,
                        "searchable" => true,
                        "sortable" => true
                    ],
                    [
                        "name" => "temario",
                        "type" => "string",
                        "label" => "Temario / Syllabus",
                        "required" => false,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "file",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => false
                    ]
                ],
                "relations" => [
                    [
                        "type" => "belongsToMany",
                        "target" => "Curso",
                        "foreign_key" => null,
                        "relation_name" => "cursos"
                    ]
                ],
                "is_system" => false,
                "ui_layout" => [
                    "sections" => [
                        [
                            "id" => "sec-materia-info",
                            "title" => "Datos de la Materia",
                            "columns" => 2,
                            "fields" => [
                                "nombre",
                                "codigo",
                                "temario"
                            ]
                        ],
                        [
                            "id" => "sec-materia-rel",
                            "title" => "Asociación",
                            "columns" => 1,
                            "fields" => [
                                "cursos"
                            ]
                        ]
                    ]
                ]
            ],
            [
                "id" => "demo_calificacion",
                "name" => "Calificacion",
                "table" => "calificaciones",
                "plural_label" => "Calificaciones",
                "icon" => "star",
                "position" => [
                    "x" => 600,
                    "y" => 400
                ],
                "fields" => [
                    [
                        "name" => "id",
                        "type" => "id",
                        "label" => "ID",
                        "required" => true,
                        "unique" => true,
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "nota",
                        "type" => "decimal",
                        "label" => "Calificación (Nota)",
                        "required" => true,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "number",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "fecha",
                        "type" => "date",
                        "label" => "Fecha de Registro",
                        "required" => true,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "date",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "comentario",
                        "type" => "string",
                        "label" => "Comentarios/Observaciones",
                        "required" => false,
                        "unique" => false,
                        "default" => null,
                        "input_type" => "textarea",
                        "show_in_table" => true,
                        "searchable" => true,
                        "sortable" => false
                    ],
                    [
                        "name" => "user_id",
                        "type" => "foreignId",
                        "label" => "Alumno",
                        "required" => true,
                        "relation_target" => "users",
                        "input_type" => "select",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "materia_id",
                        "type" => "foreignId",
                        "label" => "Materia",
                        "required" => true,
                        "relation_target" => "materias",
                        "input_type" => "select",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ],
                    [
                        "name" => "curso_id",
                        "type" => "foreignId",
                        "label" => "Curso",
                        "required" => true,
                        "relation_target" => "cursos",
                        "input_type" => "select",
                        "show_in_table" => true,
                        "searchable" => false,
                        "sortable" => true
                    ]
                ],
                "relations" => [
                    [
                        "type" => "belongsTo",
                        "target" => "User",
                        "foreign_key" => "user_id",
                        "relation_name" => "student"
                    ],
                    [
                        "type" => "belongsTo",
                        "target" => "Materia",
                        "foreign_key" => "materia_id",
                        "relation_name" => "materia"
                    ],
                    [
                        "type" => "belongsTo",
                        "target" => "Curso",
                        "foreign_key" => "curso_id",
                        "relation_name" => "curso"
                    ]
                ],
                "is_system" => false,
                "ui_layout" => [
                    "sections" => [
                        [
                            "id" => "sec-calif-info",
                            "title" => "Datos de la Calificación",
                            "columns" => 2,
                            "fields" => [
                                "nota",
                                "fecha",
                                "materia_id"
                            ]
                        ],
                        [
                            "id" => "sec-calif-asoc",
                            "title" => "Asociación y Comentarios",
                            "columns" => 1,
                            "fields" => [
                                "curso_id",
                                "user_id",
                                "comentario"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        // 2. Guardar esquema en entities.json
        $metadataPath = database_path('metadata/entities.json');
        File::ensureDirectoryExists(dirname($metadataPath));
        File::put($metadataPath, json_encode($demoSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->line('- Esquema de prueba guardado en entities.json.');

        // Limpieza de tablas previas para evitar colisiones
        \Illuminate\Support\Facades\Schema::dropIfExists('curso_materia');
        \Illuminate\Support\Facades\Schema::dropIfExists('curso_user');
        \Illuminate\Support\Facades\Schema::dropIfExists('calificaciones');
        \Illuminate\Support\Facades\Schema::dropIfExists('cursos');
        \Illuminate\Support\Facades\Schema::dropIfExists('materias');
        
        \Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%_create_cursos_table')->delete();
        \Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%_create_materias_table')->delete();
        \Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%_create_calificaciones_table')->delete();
        \Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%_create_curso_user_table')->delete();
        \Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%_create_curso_materia_table')->delete();

        // 3. Generar archivos
        $this->line('- Generando modelos, controladores y vistas en el sistema...');
        $generator = new CodeGeneratorService();
        $result = $generator->generateAll($demoSchema, []);

        if ($result['success'] ?? false) {
            $this->info('¡Código y vistas generados con éxito!');
        } else {
            $this->error('Error al generar código.');
            return Command::FAILURE;
        }

        // 4. Aplicar migraciones
        $this->line('- Ejecutando migraciones del diseñador...');
        $this->call('migrate', [
            '--path' => 'database/migrations/designer'
        ]);

        // 5. Crear archivos demo (Imágenes y PDFs)
        $this->line('- Generando archivos mock para las pruebas...');
        $uploadsDir = storage_path('app/public/uploads');
        File::ensureDirectoryExists($uploadsDir);

        $generateImage = function($filename, $text, $bgColor) use ($uploadsDir) {
            $path = $uploadsDir . '/' . $filename;
            if (extension_loaded('gd')) {
                $im = imagecreatetruecolor(600, 400);
                list($r, $g, $b) = sscanf($bgColor, "#%02x%02x%02x");
                $bg = imagecolorallocate($im, $r, $g, $b);
                imagefill($im, 0, 0, $bg);
                $white = imagecolorallocate($im, 255, 255, 255);
                imagerectangle($im, 10, 10, 590, 390, $white);
                
                // Simple title drawing
                $font = 5;
                $textWidth = imagefontwidth($font) * strlen($text);
                $textHeight = imagefontheight($font);
                $x = (600 - $textWidth) / 2;
                $y = (400 - $textHeight) / 2;
                imagestring($im, $font, $x, $y, $text, $white);
                
                imagepng($im, $path);
                imagedestroy($im);
            } else {
                $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
                File::put($path, base64_decode($pngBase64));
            }
        };

        $generatePdf = function($filename, $title) use ($uploadsDir) {
            $path = $uploadsDir . '/' . $filename;
            $pdfContent = "%PDF-1.4\n";
            $pdfContent .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";
            $pdfContent .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";
            $pdfContent .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << >> /Contents 4 0 R >>\nendobj\n";
            $pdfContent .= "4 0 obj\n<< /Length " . strlen("BT /F1 24 Tf 50 750 Td ({$title}) Tj ET") . " >>\nstream\nBT /F1 24 Tf 50 750 Td ({$title}) Tj ET\nendstream\nendobj\n";
            $pdfContent .= "xref\n0 5\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000210 00000 n\n";
            $pdfContent .= "trailer\n<< /Size 5 /Root 1 0 R >>\nstartxref\n310\n%%EOF";
            File::put($path, $pdfContent);
        };

        $generateImage('laravel.png', 'Laravel Course', '#f05340');
        $generateImage('vue.png', 'Vue.js Course', '#41b883');
        $generateImage('database.png', 'Database Course', '#2f3542');

        $generatePdf('temario_laravel.pdf', 'Syllabus Laravel Basico');
        $generatePdf('temario_vue.pdf', 'Syllabus Vue.js Avanzado');
        $generatePdf('temario_database.pdf', 'Syllabus Bases de Datos Relacionales');

        // 6. Insertar datos demo
        $this->line('- Insertando datos demo...');

        $user1 = \App\Models\User::firstOrCreate(
            ['email' => 'elias@demo.com'],
            ['name' => 'Elías Alumno', 'password' => bcrypt('password'), 'titulo' => 'Alumno']
        );
        $user2 = \App\Models\User::firstOrCreate(
            ['email' => 'juan@demo.com'],
            ['name' => 'Juan Pérez', 'password' => bcrypt('password'), 'titulo' => 'Alumno']
        );
        $user3 = \App\Models\User::firstOrCreate(
            ['email' => 'maria@demo.com'],
            ['name' => 'María Gómez', 'password' => bcrypt('password'), 'titulo' => 'Alumno']
        );
        $user4 = \App\Models\User::firstOrCreate(
            ['email' => 'carlos@demo.com'],
            ['name' => 'Carlos Mendoza', 'password' => bcrypt('password'), 'titulo' => 'Alumno']
        );
        $user5 = \App\Models\User::firstOrCreate(
            ['email' => 'ana@demo.com'],
            ['name' => 'Ana Rivera', 'password' => bcrypt('password'), 'titulo' => 'Alumno']
        );
        $user6 = \App\Models\User::firstOrCreate(
            ['email' => 'luis@demo.com'],
            ['name' => 'Luis Pérez', 'password' => bcrypt('password'), 'titulo' => 'Alumno']
        );
        $user7 = \App\Models\User::firstOrCreate(
            ['email' => 'sofia@demo.com'],
            ['name' => 'Sofía Castro', 'password' => bcrypt('password'), 'titulo' => 'Alumno']
        );

        $materiaLaravel = \App\Models\Designer\Materia::create([
            'nombre' => 'Laravel Básico',
            'codigo' => 'LARA-101',
            'temario' => 'uploads/temario_laravel.pdf'
        ]);
        $materiaVue = \App\Models\Designer\Materia::create([
            'nombre' => 'Vue.js Avanzado',
            'codigo' => 'VUE-202',
            'temario' => 'uploads/temario_vue.pdf'
        ]);
        $materiaDatabase = \App\Models\Designer\Materia::create([
            'nombre' => 'Bases de Datos Relacionales',
            'codigo' => 'DB-303',
            'temario' => 'uploads/temario_database.pdf'
        ]);
        $materiaReact = \App\Models\Designer\Materia::create([
            'nombre' => 'React Native Móvil',
            'codigo' => 'REACT-301',
            'temario' => 'uploads/temario_database.pdf'
        ]);
        $materiaGit = \App\Models\Designer\Materia::create([
            'nombre' => 'Git y Control de Versiones',
            'codigo' => 'GIT-102',
            'temario' => 'uploads/temario_laravel.pdf'
        ]);

        $cursoFullstack = \App\Models\Designer\Curso::create([
            'nombre' => 'Desarrollo Fullstack Laravel & Vue',
            'imagen' => 'uploads/laravel.png',
            'cupo' => 25,
            'activo' => true,
            'fecha_inicio' => '2026-07-01'
        ]);
        $cursoFrontend = \App\Models\Designer\Curso::create([
            'nombre' => 'Especialidad en Frontend con Vue.js',
            'imagen' => 'uploads/vue.png',
            'cupo' => 20,
            'activo' => true,
            'fecha_inicio' => '2026-08-15'
        ]);
        $cursoDatabase = \App\Models\Designer\Curso::create([
            'nombre' => 'Administración de Bases de Datos',
            'imagen' => 'uploads/database.png',
            'cupo' => 15,
            'activo' => false,
            'fecha_inicio' => '2026-09-01'
        ]);

        $cursoFullstack->materias()->sync([$materiaLaravel->id, $materiaVue->id, $materiaDatabase->id, $materiaGit->id]);
        $cursoFrontend->materias()->sync([$materiaVue->id, $materiaReact->id]);
        $cursoDatabase->materias()->sync([$materiaDatabase->id]);

        $cursoFullstack->users()->sync([$user1->id, $user2->id, $user4->id, $user5->id, $user6->id]);
        $cursoFrontend->users()->sync([$user2->id, $user3->id, $user6->id, $user7->id]);
        $cursoDatabase->users()->sync([$user1->id, $user3->id, $user5->id]);

        // Calificaciones para Laravel Básico
        \App\Models\Designer\Calificacion::create([
            'nota' => 9.5,
            'fecha' => '2026-06-03',
            'comentario' => 'Excelente desempeño en el proyecto final.',
            'user_id' => $user1->id,
            'materia_id' => $materiaLaravel->id,
            'curso_id' => $cursoFullstack->id
        ]);
        \App\Models\Designer\Calificacion::create([
            'nota' => 8.5,
            'fecha' => '2026-06-03',
            'comentario' => 'Buen proyecto e integracion.',
            'user_id' => $user2->id,
            'materia_id' => $materiaLaravel->id,
            'curso_id' => $cursoFullstack->id
        ]);
        \App\Models\Designer\Calificacion::create([
            'nota' => 7.0,
            'fecha' => '2026-06-04',
            'comentario' => 'Satisfecho, necesita pulir detalles en base de datos.',
            'user_id' => $user4->id,
            'materia_id' => $materiaLaravel->id,
            'curso_id' => $cursoFullstack->id
        ]);
        \App\Models\Designer\Calificacion::create([
            'nota' => 9.8,
            'fecha' => '2026-06-04',
            'comentario' => 'Trabajo impecable.',
            'user_id' => $user5->id,
            'materia_id' => $materiaLaravel->id,
            'curso_id' => $cursoFullstack->id
        ]);

        // Calificaciones para Vue.js Avanzado
        \App\Models\Designer\Calificacion::create([
            'nota' => 8.0,
            'fecha' => '2026-06-03',
            'comentario' => 'Buen dominio de los componentes reactivos.',
            'user_id' => $user2->id,
            'materia_id' => $materiaVue->id,
            'curso_id' => $cursoFrontend->id
        ]);
        \App\Models\Designer\Calificacion::create([
            'nota' => 9.0,
            'fecha' => '2026-06-04',
            'comentario' => 'Excelente uso de Pinia y Composition API.',
            'user_id' => $user3->id,
            'materia_id' => $materiaVue->id,
            'curso_id' => $cursoFrontend->id
        ]);
        \App\Models\Designer\Calificacion::create([
            'nota' => 10.0,
            'fecha' => '2026-06-04',
            'comentario' => 'Perfecta entrega de SPA optimizada.',
            'user_id' => $user7->id,
            'materia_id' => $materiaVue->id,
            'curso_id' => $cursoFrontend->id
        ]);

        // Calificaciones para Bases de Datos Relacionales
        \App\Models\Designer\Calificacion::create([
            'nota' => 8.7,
            'fecha' => '2026-06-04',
            'comentario' => 'Bien estructurado el diagrama entidad relación.',
            'user_id' => $user1->id,
            'materia_id' => $materiaDatabase->id,
            'curso_id' => $cursoFullstack->id
        ]);
        \App\Models\Designer\Calificacion::create([
            'nota' => 9.2,
            'fecha' => '2026-06-04',
            'comentario' => 'Muy buenas consultas complejas y optimizaciones.',
            'user_id' => $user3->id,
            'materia_id' => $materiaDatabase->id,
            'curso_id' => $cursoDatabase->id
        ]);
        \App\Models\Designer\Calificacion::create([
            'nota' => 7.8,
            'fecha' => '2026-06-04',
            'comentario' => 'Buen rendimiento general.',
            'user_id' => $user5->id,
            'materia_id' => $materiaDatabase->id,
            'curso_id' => $cursoDatabase->id
        ]);

        // Calificaciones para React Native Móvil
        \App\Models\Designer\Calificacion::create([
            'nota' => 9.1,
            'fecha' => '2026-06-04',
            'comentario' => 'Excelente aplicación móvil híbrida.',
            'user_id' => $user6->id,
            'materia_id' => $materiaReact->id,
            'curso_id' => $cursoFrontend->id
        ]);

        // Calificaciones para Git y Control de Versiones
        \App\Models\Designer\Calificacion::create([
            'nota' => 10.0,
            'fecha' => '2026-06-04',
            'comentario' => 'Perfecto entendimiento de ramas y merge conflicts.',
            'user_id' => $user1->id,
            'materia_id' => $materiaGit->id,
            'curso_id' => $cursoFullstack->id
        ]);

        // Seed the grouped report with totals
        \App\Models\Report::updateOrCreate(
            ['name' => 'Calificaciones por Materia con Totales'],
            [
                'entity_name' => 'Calificacion',
                'fields' => ['user_id', 'nota'],
                'filters' => ['type' => 'group', 'operator' => 'and', 'rules' => []],
                'filters_operator' => 'and',
                'sort_by' => 'materia_id',
                'sort_order' => 'asc',
                'group_by' => 'materia_id',
                'aggregations' => [
                    [
                        'field' => 'nota',
                        'function' => 'AVG',
                        'label' => 'Promedio de Notas'
                    ]
                ],
                'user_id' => $user1->id
            ]
        );

        $this->info('¡Entidades de prueba cargadas, archivos creados y base de datos sincronizada correctamente!');
        return Command::SUCCESS;
    }
}
