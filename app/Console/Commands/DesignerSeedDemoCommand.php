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
                                "cupo"
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
                                "codigo"
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

        $this->info('¡Entidades de prueba cargadas y base de datos sincronizada correctamente!');
        return Command::SUCCESS;
    }
}
