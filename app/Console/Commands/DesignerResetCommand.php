<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DesignerResetCommand extends Command
{
    protected $signature = 'designer:reset';
    protected $description = 'Elimina de forma completa todos los archivos y configuraciones inyectados por el Diseñador Visual';

    public function handle()
    {
        $this->info('Iniciando limpieza del Diseñador Visual...');

        // 1. Eliminar modelos generados
        $modelsPath = app_path('Models/Designer');
        if (File::isDirectory($modelsPath)) {
            File::cleanDirectory($modelsPath);
            File::put("{$modelsPath}/.gitkeep", "");
            $this->line('- Modelos de diseñador eliminados.');
        }

        // 2. Eliminar controladores generados
        $controllersPath = app_path('Http/Controllers/Designer');
        if (File::isDirectory($controllersPath)) {
            File::cleanDirectory($controllersPath);
            File::put("{$controllersPath}/.gitkeep", "");
            $this->line('- Controladores de diseñador eliminados.');
        }

        // 3. Eliminar vistas generadas
        $viewsPath = resource_path('js/Pages/Designer');
        if (File::isDirectory($viewsPath)) {
            File::cleanDirectory($viewsPath);
            File::put("{$viewsPath}/.gitkeep", "");
            $this->line('- Vistas de Vue de diseñador eliminadas.');
        }

        // 4. Eliminar migraciones generadas (manteniendo .gitignore)
        $migrationsPath = database_path('migrations/designer');
        if (File::isDirectory($migrationsPath)) {
            $gitignoreExists = File::exists("{$migrationsPath}/.gitignore");
            File::cleanDirectory($migrationsPath);
            if ($gitignoreExists) {
                File::put("{$migrationsPath}/.gitignore", "*\n!.gitignore\n");
            }
            $this->line('- Migraciones de diseñador eliminadas.');
        }

        // 5. Eliminar rutas generadas
        $routesPath = base_path('routes/designer.php');
        if (File::exists($routesPath)) {
            File::delete($routesPath);
            $this->line('- Archivo de rutas de diseñador eliminado.');
        }

        // 6. Eliminar metadatos / esquemas
        $metadataPath = database_path('metadata/entities.json');
        if (File::exists($metadataPath)) {
            File::delete($metadataPath);
        }
        $metadataOldPath = database_path('metadata/entities_old.json');
        if (File::exists($metadataOldPath)) {
            File::delete($metadataOldPath);
        }
        $this->line('- Esquemas y metadatos JSON eliminados.');

        // 7. Limpiar relaciones en modelos del sistema (User, Configuracion, etc.)
        $protectedModels = ['User', 'Configuracion', 'Role', 'Permission'];
        $startComment = "// -- ENTITY DESIGNER RELATIONS START --";
        $endComment = "// -- ENTITY DESIGNER RELATIONS END --";
        $newBlock = "    {$startComment}\n    {$endComment}";

        foreach ($protectedModels as $name) {
            $path = app_path("Models/{$name}.php");
            if (File::exists($path)) {
                $content = File::get($path);
                if (str_contains($content, $startComment) && str_contains($content, $endComment)) {
                    $pattern = '/' . preg_quote($startComment, '/') . '.*?' . preg_quote($endComment, '/') . '/s';
                    $newContent = preg_replace($pattern, $newBlock, $content);
                    File::put($path, $newContent);
                    $this->line("- Relaciones limpiadas en el modelo {$name}.");
                }
            }
        }

        $this->info('¡Limpieza del Diseñador completada con éxito!');
        return Command::SUCCESS;
    }
}
