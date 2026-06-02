<?php

namespace App\Http\Controllers;

use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DesignerController extends Controller
{
    protected $generator;

    public function __construct(CodeGeneratorService $generator)
    {
        $this->generator = $generator;
    }

    public function index()
    {
        $user = \Auth::user();
        if ($user && ($user->can("configuraciones.index") || $user->hasRole("Administrador"))) {
            $entitiesPath = database_path('metadata/entities.json');
            $entities = [];
            if (File::exists($entitiesPath)) {
                $entities = json_decode(File::get($entitiesPath), true);
            }

            // Filter out Configuracion if present from old designs
            $entities = array_values(array_filter($entities, function($e) {
                return $e['name'] !== 'Configuracion';
            }));

            // Ensure system entities are present in schema for design reference
            $hasUser = false;
            foreach ($entities as $e) {
                if ($e['name'] === 'User') {
                    $hasUser = true;
                    break;
                }
            }

            if (!$hasUser) {
                $entities[] = [
                    'id' => 'system_user',
                    'name' => 'User',
                    'plural_label' => 'Usuarios',
                    'table' => 'users',
                    'icon' => 'users',
                    'position' => ['x' => 50, 'y' => 50],
                    'fields' => [
                        ['name' => 'id', 'type' => 'id', 'label' => 'ID', 'required' => true, 'unique' => true, 'show_in_table' => true],
                        ['name' => 'name', 'type' => 'string', 'label' => 'Nombre', 'show_in_table' => true],
                        ['name' => 'email', 'type' => 'string', 'label' => 'Email', 'show_in_table' => true],
                        ['name' => 'titulo', 'type' => 'string', 'label' => 'Título', 'show_in_table' => true]
                    ],
                    'relations' => [],
                    'is_system' => true
                ];
            }

            // Get all migration files
            $migrationFiles = array_merge(
                glob(database_path('migrations/*.php')) ?: [],
                glob(database_path('migrations/designer/*.php')) ?: []
            );
            $fileNames = array_map(function($f) {
                return basename($f, '.php');
            }, $migrationFiles);

            // Get ran migrations from database
            $ranMigrations = [];
            try {
                if (\Schema::hasTable('migrations')) {
                    $ranMigrations = \DB::table('migrations')->pluck('migration')->toArray();
                }
            } catch (\Exception $e) {
                // handle database connection errors silently
            }

            // Build status list
            $migrationsList = [];
            foreach ($fileNames as $name) {
                $ran = in_array($name, $ranMigrations);
                $migrationsList[] = [
                    'name' => $name,
                    'status' => $ran ? 'ran' : 'pending',
                    'file_exists' => true,
                    'timestamp' => substr($name, 0, 17)
                ];
            }

            // Check for missing files in database
            foreach ($ranMigrations as $ranName) {
                $found = false;
                foreach ($migrationsList as $m) {
                    if ($m['name'] === $ranName) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $migrationsList[] = [
                        'name' => $ranName,
                        'status' => 'missing_file',
                        'file_exists' => false,
                        'timestamp' => substr($ranName, 0, 17)
                    ];
                }
            }

            // Sort chronologically by name desc (newest first)
            usort($migrationsList, function($a, $b) {
                return strcmp($b['name'], $a['name']);
            });

            return Inertia::render('Designer', compact('entities', 'migrationsList'));
        } else {
            return redirect()->back()->with("error", "No cuenta con los permisos necesarios para realizar esta acción.");
        }
    }

    public function saveSchema(Request $request)
    {
        $user = \Auth::user();
        if ($user && ($user->can("configuraciones.index") || $user->hasRole("Administrador"))) {
            $entities = $request->input('entities', []);
            
            // Backup old schema first to allow diffing
            $entitiesPath = database_path('metadata/entities.json');
            $oldEntities = [];
            if (File::exists($entitiesPath)) {
                $oldEntities = json_decode(File::get($entitiesPath), true);
                File::put(database_path('metadata/entities_old.json'), json_encode($oldEntities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
            
            File::ensureDirectoryExists(database_path('metadata'));
            File::put($entitiesPath, json_encode($entities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return response()->json(['ok' => true, 'message' => 'Esquema guardado exitosamente.']);
        } else {
            return response()->json(['ok' => false, 'message' => 'No cuenta con los permisos necesarios.'], 403);
        }
    }

    public function generateFiles(Request $request)
    {
        $user = \Auth::user();
        if ($user && ($user->can("configuraciones.index") || $user->hasRole("Administrador"))) {
            $entities = $request->input('entities', []);
            
            // Get old entities
            $entitiesPath = database_path('metadata/entities.json');
            $oldEntities = [];
            if (File::exists(database_path('metadata/entities_old.json'))) {
                $oldEntities = json_decode(File::get(database_path('metadata/entities_old.json')), true);
            } elseif (File::exists($entitiesPath)) {
                $oldEntities = json_decode(File::get($entitiesPath), true);
            }

            try {
                // Generate all code artifacts
                $result = $this->generator->generateAll($entities, $oldEntities);
                
                // Clear caches so Laravel discovers new classes and routes
                Artisan::call('route:clear');
                Artisan::call('cache:clear');

                return response()->json(['ok' => true, 'message' => 'Código fuente generado exitosamente.', 'generated' => count($result['generated'])]);
            } catch (\Exception $e) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['ok' => false, 'message' => 'No cuenta con los permisos necesarios.'], 403);
        }
    }

    public function runMigrations(Request $request)
    {
        $user = \Auth::user();
        if ($user && ($user->can("configuraciones.index") || $user->hasRole("Administrador"))) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $output = trim(Artisan::output());
                return response()->json(['ok' => true, 'message' => 'Migración ejecutada con éxito.', 'output' => $output]);
            } catch (\Exception $e) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['ok' => false, 'message' => 'No cuenta con los permisos necesarios.'], 403);
        }
    }
}
