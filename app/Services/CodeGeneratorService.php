<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class CodeGeneratorService
{
    protected $protectedTables = [
        'users', 'configuraciones', 'roles', 'permissions', 'model_has_permissions',
        'model_has_roles', 'role_has_permissions', 'failed_jobs', 'personal_access_tokens',
        'sessions', 'password_resets', 'jobs', 'migrations'
    ];

    protected $protectedModels = [
        'User', 'Configuracion', 'Role', 'Permission'
    ];

    public function generateAll(array $entities, array $oldEntities)
    {
        // 1. Ensure directory exists
        File::ensureDirectoryExists(database_path('metadata'));
        File::ensureDirectoryExists(app_path('Services'));

        // Save new schema snapshot
        File::put(database_path('metadata/entities.json'), json_encode($entities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        File::put(database_path('metadata/entities_old.json'), json_encode($oldEntities, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $generated = [];
        $migrationCounter = 0;

        // Map old entities by name for easy comparison
        $oldEntitiesByName = [];
        foreach ($oldEntities as $oe) {
            $oldEntitiesByName[$oe['name']] = $oe;
        }

        // Map new entities by name
        $entitiesByName = [];
        foreach ($entities as $e) {
            $entitiesByName[$e['name']] = $e;
        }

        // Find and process deleted entities
        foreach ($oldEntities as $oe) {
            $name = $oe['name'];
            $table = $oe['table'] ?? Str::snake(Str::plural($name));

            // Skip protected/system entities
            if (in_array($table, $this->protectedTables) || in_array($name, $this->protectedModels)) {
                continue;
            }

            if (!isset($entitiesByName[$name])) {
                // Drop Table Migration
                $timestamp = date('Y_m_d_His', time() + $migrationCounter++);
                $filename = "{$timestamp}_drop_{$table}_table.php";
                $migrationsDir = database_path('migrations/designer');
                File::ensureDirectoryExists($migrationsDir);
                $path = "{$migrationsDir}/{$filename}";

                $template = <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('{{tableName}}');
    }

    public function down(): void
    {
    }
};
PHP;
                $code = str_replace('{{tableName}}', $table, $template);
                File::put($path, $code);

                // Delete files
                $modelPath = app_path("Models/{$name}.php");
                if (File::exists($modelPath)) {
                    File::delete($modelPath);
                }

                $controllerPath = app_path("Http/Controllers/{$name}Controller.php");
                if (File::exists($controllerPath)) {
                    File::delete($controllerPath);
                }

                $viewPath = resource_path("js/Pages/{$name}.vue");
                if (File::exists($viewPath)) {
                    File::delete($viewPath);
                }

                // Delete Spatie permissions
                if (class_exists(\Spatie\Permission\Models\Permission::class)) {
                    $permissions = ["{$table}.index", "{$table}.store", "{$table}.update", "{$table}.destroy"];
                    foreach ($permissions as $perm) {
                        $permission = \Spatie\Permission\Models\Permission::where('name', $perm)->first();
                        if ($permission) {
                            $permission->delete();
                        }
                    }
                }
            }
        }

        foreach ($entities as $entity) {
            $name = $entity['name'];
            $table = $entity['table'] ?? Str::snake(Str::plural($name));

            // Protection check
            if (in_array($table, $this->protectedTables) || in_array($name, $this->protectedModels)) {
                continue;
            }

            // A. Generate Model (into app/Models/Designer/)
            $modelPath = $this->generateModel($entity, $entities);

            // B. Generate Controller (into app/Http/Controllers/Designer/)
            $controllerPath = $this->generateController($entity);

            // C. Generate Vue View (into resources/js/Pages/Designer/)
            $viewPath = $this->generateView($entity);

            // D. Generate/Update Migrations (Incremental)
            $oldEntity = $oldEntitiesByName[$name] ?? null;
            $migrationPaths = $this->generateMigrations($entity, $oldEntity, $migrationCounter);

            $generated[] = [
                'entity' => $name,
                'table' => $table,
                'model' => $modelPath,
                'controller' => $controllerPath,
                'view' => $viewPath,
                'migrations' => $migrationPaths
            ];
        }

        // E. Generate Pivot Table Migrations for belongsToMany if defined
        $pivotMigrations = $this->generatePivotMigrations($entities, $oldEntities, $migrationCounter);

        // F. Update Routes
        $this->updateRoutes($entities);

        // G. Create/Assign Spatie Permissions
        $this->registerPermissions($entities);

        // H. Update relations for protected models (e.g. User)
        foreach ($this->protectedModels as $pm) {
            $entityObj = null;
            foreach ($entities as $e) {
                if ($e['name'] === $pm) {
                    $entityObj = $e;
                    break;
                }
            }

            if (!$entityObj) {
                $entityObj = [
                    'name' => $pm,
                    'table' => Str::snake(Str::plural($pm)),
                    'fields' => [],
                    'relations' => []
                ];
            }

            $pmRelationMethods = $this->generateRelationMethodsForEntity($entityObj, $entities);
            $this->updateProtectedModelRelations($pm, $pmRelationMethods);
        }

        return [
            'success' => true,
            'generated' => $generated,
            'pivot_migrations' => $pivotMigrations
        ];
    }

    protected function generateModel(array $entity, array $entities)
    {
        $name = $entity['name'];
        $table = $entity['table'];
        $fields = $entity['fields'];

        $fillable = [];
        $searchableFields = [];
        foreach ($fields as $field) {
            if ($field['name'] !== 'id' && $field['type'] !== 'id') {
                $fillable[] = "'" . $field['name'] . "'";
            }
            if ($field['searchable'] ?? false) {
                $searchableFields[] = $field['name'];
            }
        }
        $fillableStr = implode(",\n        ", $fillable);

        // Build search scope chain
        $searchChain = "";
        if (count($searchableFields) > 0) {
            $searchChainParts = [];
            foreach ($searchableFields as $index => $sf) {
                if ($index === 0) {
                    $searchChainParts[] = "->where('{$sf}', 'LIKE', \"%{\$key}%\")";
                } else {
                    $searchChainParts[] = "->orWhere('{$sf}', 'LIKE', \"%{\$key}%\")";
                }
            }
            $searchChain = implode("\n            ", $searchChainParts);
        } else {
            $searchChain = "->where('id', 'LIKE', \"%{\$key}%\")";
        }

        // Build relation methods (Direct and Inverse)
        $relationMethods = $this->generateRelationMethodsForEntity($entity, $entities);

        $templatePath = app_path('Services/Templates/Model.txt');
        $template = File::exists($templatePath) ? File::get($templatePath) : $this->getModelTemplate();
        $code = str_replace(
            ['{{modelName}}', '{{tableName}}', '{{fillableFields}}', '{{relationMethods}}', '{{searchChain}}'],
            [$name, $table, $fillableStr, $relationMethods, $searchChain],
            $template
        );

        $dir = app_path('Models/Designer');
        File::ensureDirectoryExists($dir);
        $path = "{$dir}/{$name}.php";
        File::put($path, $code);

        return $path;
    }

    protected function generateController(array $entity)
    {
        $name = $entity['name'];
        $table = $entity['table'];
        $fields = $entity['fields'];
        $relations = $entity['relations'] ?? [];

        $pluralVar = Str::camel(Str::plural($name));

        // Relations loading
        $relationsWith = "";
        $belongsToRelations = array_filter($relations, function($r) { return $r['type'] === 'belongsTo'; });
        if (count($belongsToRelations) > 0) {
            $relNames = array_map(function($r) {
                return "'" . ($r['relation_name'] ?? Str::camel($r['target'])) . "'";
            }, $belongsToRelations);
            $relationsWith = "\$query->with([" . implode(", ", $relNames) . "]);";
        }

        // Prefetch options for belongsTo relations
        $prefetchOptions = "";
        $compactExtraArr = [];
        $optionsProps = [];
        foreach ($belongsToRelations as $r) {
            $target = $r['target'];
            $pluralTarget = Str::plural($target);
            $optionsVar = Str::camel($pluralTarget) . 'Options';
            $compactExtraArr[] = "'{$optionsVar}'";

            // Determine suitable label field
            $labelFieldExpr = "\$item->nombre ?? \$item->name ?? \$item->titulo ?? \$item->title ?? \$item->id";
            $targetFqn = $this->getModelFQN($target);
            $prefetchOptions .= "            \${$optionsVar} = {$targetFqn}::all()->map(function(\$item) {\n";
            $prefetchOptions .= "                return [\n";
            $prefetchOptions .= "                    'name' => {$labelFieldExpr},\n";
            $prefetchOptions .= "                    'value' => \$item->id\n";
            $prefetchOptions .= "                ];\n";
            $prefetchOptions .= "            })->toArray();\n\n";
        }
        $compactExtra = count($compactExtraArr) > 0 ? ", " . implode(", ", $compactExtraArr) : "";

        // Validation Rules and Create fields
        $validationRules = [];
        $createFields = [];
        foreach ($fields as $field) {
            $fName = $field['name'];
            if ($fName === 'id') continue;

            $rules = [];
            if ($field['required'] ?? false) {
                $rules[] = "'required'";
            } else {
                $rules[] = "'nullable'";
            }

            if ($field['type'] === 'string') {
                $rules[] = "'string'";
                $rules[] = "'max:255'";
            } elseif ($field['type'] === 'integer') {
                $rules[] = "'integer'";
            } elseif ($field['type'] === 'decimal') {
                $rules[] = "'numeric'";
            } elseif ($field['type'] === 'boolean') {
                $rules[] = "'boolean'";
            }

            $validationRules[] = "'{$fName}' => [" . implode(", ", $rules) . "]";
            $createFields[] = "'{$fName}' => \$datos['{$fName}']";
        }
        $validationRulesStr = implode(",\n                ", $validationRules);
        $createFieldsStr = implode(",\n                ", $createFields);

        $template = $this->getControllerTemplate();
        $code = str_replace(
            [
                '{{modelName}}', '{{tableName}}', '{{pluralVar}}', '{{relationsWith}}',
                '{{prefetchOptions}}', '{{compactExtra}}', '{{validationRules}}', '{{createFields}}'
            ],
            [
                $name, $table, $pluralVar, $relationsWith,
                $prefetchOptions, $compactExtra, $validationRulesStr, $createFieldsStr
            ],
            $template
        );

        $dir = app_path('Http/Controllers/Designer');
        File::ensureDirectoryExists($dir);
        $path = "{$dir}/{$name}Controller.php";
        File::put($path, $code);

        return $path;
    }

    protected function generateView(array $entity)
    {
        $name = $entity['name'];
        $table = $entity['table'];
        $fields = $entity['fields'];
        $relations = $entity['relations'] ?? [];
        $pluralVar = Str::camel(Str::plural($name));
        $pluralLabel = $entity['plural_label'] ?? Str::plural($name);

        // Build columns for display in Table
        $tableCols = "";
        $tableRows = "";
        $formFields = [];
        $errorFields = [];
        $modalInputs = [];
        $optionsProps = [];
        $belongsToRelations = array_filter($relations, function($r) { return $r['type'] === 'belongsTo'; });

        // Build list of relation targets
        $relationTargetByFk = [];
        foreach ($belongsToRelations as $r) {
            $fk = $r['foreign_key'] ?? Str::snake($r['target']) . '_id';
            $relationTargetByFk[$fk] = $r;
        }

        foreach ($fields as $field) {
            $fName = $field['name'];
            $fLabel = $field['label'] ?? ucfirst($fName);
            $fType = $field['type'];

            // 1. Columns headers & cells
            if ($field['show_in_table'] ?? true) {
                if ($fName === 'id') {
                    $tableCols .= "              <th class=\"px-4 py-3 w-20\">\n";
                    $tableCols .= "                <Ordena\n";
                    $tableCols .= "                  v-model=\"orderByObject\"\n";
                    $tableCols .= "                  ruta=\"{$table}\"\n";
                    $tableCols .= "                  :buscar=\"buscar\"\n";
                    $tableCols .= "                  :filtros=\"filtros\"\n";
                    $tableCols .= "                  titulo=\"ID\"\n";
                    $tableCols .= "                  campo=\"id\"\n";
                    $tableCols .= "                />\n";
                    $tableCols .= "              </th>\n";

                    $tableRows .= "                <td class=\"px-4 py-3 text-sm text-slate-500 dark:text-slate-300\">{{ item.id }}</td>\n";
                } else {
                    $tableCols .= "              <th class=\"px-4 py-3\">\n";
                    if ($field['sortable'] ?? false) {
                        $tableCols .= "                <Ordena\n";
                        $tableCols .= "                  v-model=\"orderByObject\"\n";
                        $tableCols .= "                  ruta=\"{$table}\"\n";
                        $tableCols .= "                  :buscar=\"buscar\"\n";
                        $tableCols .= "                  :filtros=\"filtros\"\n";
                        $tableCols .= "                  titulo=\"{$fLabel}\"\n";
                        $tableCols .= "                  campo=\"{$fName}\"\n";
                        $tableCols .= "                />\n";
                    } else {
                        $tableCols .= "                {$fLabel}\n";
                    }
                    $tableCols .= "              </th>\n";

                    // cell rendering (relation check)
                    if (isset($relationTargetByFk[$fName])) {
                        $rel = $relationTargetByFk[$fName];
                        $relName = $rel['relation_name'] ?? Str::camel($rel['target']);
                        $tableRows .= "                <td class=\"px-4 py-3 text-sm text-slate-600 dark:text-slate-300 text-left\">\n";
                        $tableRows .= "                  {{ item.{$relName} ? (item.{$relName}.nombre || item.{$relName}.name || item.{$relName}.titulo || item.{$relName}.title || item.{$relName}.id) : item.{$fName} }}\n";
                        $tableRows .= "                </td>\n";
                    } else if ($fType === 'boolean') {
                        $tableRows .= "                <td class=\"px-4 py-3 text-sm text-left\">\n";
                        $tableRows .= "                  <span v-if=\"item.{$fName}\" class=\"px-2 py-0.5 rounded text-xs font-semibold bg-green-500/10 text-green-400 border border-green-500/20\">Sí</span>\n";
                        $tableRows .= "                  <span v-else class=\"px-2 py-0.5 rounded text-xs font-semibold bg-red-500/10 text-red-400 border border-red-500/20\">No</span>\n";
                        $tableRows .= "                </td>\n";
                    } else {
                        $tableRows .= "                <td class=\"px-4 py-3 text-sm text-slate-600 dark:text-slate-300 text-left\">{{ item.{$fName} }}</td>\n";
                    }
                }
            }

            // 2. Form definition
            if ($fName !== 'id') {
                $defaultVal = "''";
                if ($fType === 'integer' || $fType === 'decimal') {
                    $defaultVal = "0";
                } elseif ($fType === 'boolean') {
                    $defaultVal = "false";
                }
                $formFields[] = "  {$fName}: {$defaultVal}";
                $errorFields[] = "  {$fName}: ''";

                // 3. Modal Inputs
                $isRequired = $field['required'] ?? false;
                $reqAttr = $isRequired ? "required" : "";

                $inputType = $field['input_type'] ?? 'text';
                $modalInputs[] = "          <label class=\"block mb-3\">";
                $modalInputs[] = "            <JetLabel for=\"{$fName}\" value=\"{$fLabel}\" class=\"float-left mb-1\" />";

                if (isset($relationTargetByFk[$fName])) {
                    // It is a belongsTo relation select
                    $r = $relationTargetByFk[$fName];
                    $optionsName = Str::camel(Str::plural($r['target'])) . 'Options';
                    $optionsProps[] = "  {$optionsName}: Array,";
                    $modalInputs[] = "            <Select id=\"{$fName}\" v-model=\"form.{$fName}\" :value=\"form.{$fName}\" class=\"mt-1 block w-full\" :options=\"{$optionsName}\" {$reqAttr} />";
                } elseif ($inputType === 'textarea') {
                    $modalInputs[] = "            <Textarea id=\"{$fName}\" v-model=\"form.{$fName}\" class=\"mt-1 block w-full\" rows=\"3\" {$reqAttr} />";
                } elseif ($inputType === 'toggle' || $fType === 'boolean') {
                    $modalInputs[] = "            <div class=\"mt-2 text-left\"><Toggle id=\"{$fName}\" v-model=\"form.{$fName}\" /></div>";
                } elseif ($inputType === 'number' || $fType === 'integer' || $fType === 'decimal') {
                    $modalInputs[] = "            <JetInput id=\"{$fName}\" v-model=\"form.{$fName}\" type=\"number\" step=\"any\" class=\"mt-1 block w-full\" {$reqAttr} />";
                } elseif ($inputType === 'date') {
                    $modalInputs[] = "            <JetInput id=\"{$fName}\" v-model=\"form.{$fName}\" type=\"date\" class=\"mt-1 block w-full\" {$reqAttr} />";
                } else {
                    $modalInputs[] = "            <JetInput id=\"{$fName}\" v-model=\"form.{$fName}\" type=\"text\" class=\"mt-1 block w-full\" {$reqAttr} />";
                }
                $modalInputs[] = "            <JetInputError :message=\"error.{$fName}\" class=\"mt-1\" />";
                $modalInputs[] = "          </label>";
            }
        }

        $formFieldsStr = implode(",\n    ", $formFields);
        $errorFieldsStr = implode(",\n    ", $errorFields);
        $optionsPropsStr = implode("\n  ", $optionsProps);
        $modalInputsStr = implode("\n", $modalInputs);

        // Build campos list for Buscador component
        $camposProps = [];
        foreach ($fields as $field) {
            $fName = $field['name'];
            $fLabel = $field['label'] ?? ucfirst($fName);
            $fType = $field['type'];

            $cond = "LIKE";
            if ($fType === 'integer' || $fType === 'decimal' || $fType === 'id' || $fType === 'foreignId') {
                $cond = "=";
            }

            $camposProps[] = "  {$fName}: { label: '{$fLabel}', type: '" . ($fType === 'boolean' ? 'select' : 'text') . "', defaultCondition: '{$cond}' " . ($fType === 'boolean' ? ", options: [{name: 'Sí', value: 1}, {name: 'No', value: 0}]" : "") . " }";
        }
        $camposPropsStr = implode(",\n  ", $camposProps);

        // Edit mapping values to form
        $editMappingArr = [];
        foreach ($fields as $field) {
            $fName = $field['name'];
            if ($fName === 'id') continue;
            $editMappingArr[] = "  form.{$fName} = item.{$fName};";
        }
        $editMapping = implode("\n  ", $editMappingArr);

        // Reset error fields
        $resetErrorsArr = [];
        foreach ($fields as $field) {
            $fName = $field['name'];
            if ($fName === 'id') continue;
            $resetErrorsArr[] = "  error.{$fName} = '';";
        }
        $resetErrors = implode("\n  ", $resetErrorsArr);

        // Handle error responses in frontend
        $errorAssignmentsArr = [];
        foreach ($fields as $field) {
            $fName = $field['name'];
            if ($fName === 'id') continue;
            $errorAssignmentsArr[] = "      error.{$fName} = errors.{$fName} ? errors.{$fName} : null;";
        }
        $errorAssignments = implode("\n", $errorAssignmentsArr);

        $template = $this->getViewTemplate();
        $code = str_replace(
            [
                '{{modelName}}', '{{tableName}}', '{{pluralVar}}', '{{pluralLabel}}',
                '{{optionsProps}}', '{{formFields}}', '{{errorFields}}', '{{camposProps}}',
                '{{tableCols}}', '{{tableRows}}', '{{modalInputs}}', '{{editMapping}}',
                '{{resetErrors}}', '{{errorAssignments}}'
            ],
            [
                $name, $table, $pluralVar, $pluralLabel,
                $optionsPropsStr, $formFieldsStr, $errorFieldsStr, $camposPropsStr,
                $tableCols, $tableRows, $modalInputsStr, $editMapping,
                $resetErrors, $errorAssignments
            ],
            $template
        );

        $dir = resource_path('js/Pages/Designer');
        File::ensureDirectoryExists($dir);
        $path = "{$dir}/{$name}.vue";
        File::put($path, $code);

        return $path;
    }

    protected function generateMigrations(array $entity, ?array $oldEntity, &$migrationCounter)
    {
        $table = $entity['table'];
        $fields = $entity['fields'];
        $paths = [];
        
        $migrationsDir = database_path('migrations/designer');
        File::ensureDirectoryExists($migrationsDir);

        // Check if create table migration already exists physically
        $existingMigrations = $this->getExistingMigrations();
        $createMigrationExists = false;
        foreach ($existingMigrations as $em) {
            if (str_contains($em, "create_{$table}_table")) {
                $createMigrationExists = true;
                break;
            }
        }

        if (!$oldEntity || !$createMigrationExists) {
            // New entity or missing file: Create table
            $columnsCode = [];
            foreach ($fields as $field) {
                if ($field['name'] === 'id') continue;
                $colCode = $this->getMigrationColumnCode($field);
                if ($colCode) {
                    $columnsCode[] = "            " . $colCode;
                }
            }
            $columnsStr = implode("\n", $columnsCode);

            $template = $this->getCreateMigrationTemplate();
            $code = str_replace(
                ['{{tableName}}', '{{columnsCode}}'],
                [$table, $columnsStr],
                $template
            );

            // Generate chronological unique filename
            $timestamp = date('Y_m_d_His', time() + $migrationCounter++);
            $filename = "{$timestamp}_create_{$table}_table.php";
            $path = "{$migrationsDir}/{$filename}";
            File::put($path, $code);
            $paths[] = $path;
        } else {
            // Existing entity: Compare and generate alters
            $oldFieldsByName = [];
            foreach ($oldEntity['fields'] as $of) {
                $oldFieldsByName[$of['name']] = $of;
            }

            $newFields = [];
            $modifiedFields = [];
            $deletedFields = [];

            // Find new and modified fields
            foreach ($fields as $field) {
                $fName = $field['name'];
                if ($fName === 'id') continue;

                if (!isset($oldFieldsByName[$fName])) {
                    $newFields[] = $field;
                } else {
                    $oldF = $oldFieldsByName[$fName];
                    // Compare types or configurations
                    if ($oldF['type'] !== $field['type'] || 
                        ($oldF['required'] ?? false) !== ($field['required'] ?? false) ||
                        ($oldF['unique'] ?? false) !== ($field['unique'] ?? false) ||
                        ($oldF['default'] ?? null) !== ($field['default'] ?? null)) {
                        $modifiedFields[] = $field;
                    }
                }
            }

            // Find deleted fields
            foreach ($oldEntity['fields'] as $oldF) {
                $fName = $oldF['name'];
                if ($fName === 'id') continue;

                // check if exists in new fields
                $exists = false;
                foreach ($fields as $field) {
                    if ($field['name'] === $fName) {
                        $exists = true;
                        break;
                    }
                }

                if (!$exists) {
                    $deletedFields[] = $oldF;
                }
            }

            // A. Added columns migration
            if (count($newFields) > 0) {
                $columnsCode = [];
                $addedNames = [];
                foreach ($newFields as $f) {
                    $colCode = $this->getMigrationColumnCode($f);
                    if ($colCode) {
                        $columnsCode[] = "            " . $colCode;
                        $addedNames[] = $f['name'];
                    }
                }
                $columnsStr = implode("\n", $columnsCode);

                $template = $this->getAlterMigrationTemplate();
                $code = str_replace(
                    ['{{tableName}}', '{{upCode}}', '{{downCode}}'],
                    [
                        $table, 
                        $columnsStr, 
                        "            \$table->dropColumn([" . implode(", ", array_map(fn($n) => "'$n'", $addedNames)) . "]);"
                    ],
                    $template
                );

                $timestamp = date('Y_m_d_His', time() + $migrationCounter++);
                $filename = "{$timestamp}_add_columns_to_{$table}_table.php";
                $path = "{$migrationsDir}/{$filename}";
                File::put($path, $code);
                $paths[] = $path;
            }

            // B. Deleted columns migration
            if (count($deletedFields) > 0) {
                $dropNames = array_map(fn($f) => $f['name'], $deletedFields);
                $columnsStr = "            \$table->dropColumn([" . implode(", ", array_map(fn($n) => "'$n'", $dropNames)) . "]);";

                // Recreate code for down method
                $recreateCode = [];
                foreach ($deletedFields as $f) {
                    $colCode = $this->getMigrationColumnCode($f);
                    if ($colCode) {
                        $recreateCode[] = "            " . $colCode;
                    }
                }
                $recreateStr = implode("\n", $recreateCode);

                $template = $this->getAlterMigrationTemplate();
                $code = str_replace(
                    ['{{tableName}}', '{{upCode}}', '{{downCode}}'],
                    [$table, $columnsStr, $recreateStr],
                    $template
                );

                $timestamp = date('Y_m_d_His', time() + $migrationCounter++);
                $filename = "{$timestamp}_drop_columns_from_{$table}_table.php";
                $path = "{$migrationsDir}/{$filename}";
                File::put($path, $code);
                $paths[] = $path;
            }

            // C. Modified columns migration
            if (count($modifiedFields) > 0) {
                $columnsCode = [];
                $rollbackCode = [];
                foreach ($modifiedFields as $f) {
                    $colCode = $this->getMigrationColumnCode($f);
                    if ($colCode) {
                        // Append Laravel change modifier
                        $columnsCode[] = "            " . str_replace(';', '->change();', $colCode);
                    }
                    
                    // Down rollback
                    $oldF = $oldFieldsByName[$f['name']];
                    $oldColCode = $this->getMigrationColumnCode($oldF);
                    if ($oldColCode) {
                        $rollbackCode[] = "            " . str_replace(';', '->change();', $oldColCode);
                    }
                }
                $columnsStr = implode("\n", $columnsCode);
                $rollbackStr = implode("\n", $rollbackCode);

                $template = $this->getAlterMigrationTemplate();
                $code = str_replace(
                    ['{{tableName}}', '{{upCode}}', '{{downCode}}'],
                    [$table, $columnsStr, $rollbackStr],
                    $template
                );

                $timestamp = date('Y_m_d_His', time() + $migrationCounter++);
                $filename = "{$timestamp}_change_columns_in_{$table}_table.php";
                $path = "{$migrationsDir}/{$filename}";
                File::put($path, $code);
                $paths[] = $path;
            }
        }

        return $paths;
    }

    protected function generatePivotMigrations(array $entities, array $oldEntities, &$migrationCounter)
    {
        $generated = [];
        $migrationsDir = database_path('migrations/designer');
        File::ensureDirectoryExists($migrationsDir);

        $existingMigrations = $this->getExistingMigrations();

        foreach ($entities as $entity) {
            $relations = $entity['relations'] ?? [];
            foreach ($relations as $rel) {
                if ($rel['type'] === 'belongsToMany') {
                    $nameA = $entity['name'];
                    $nameB = $rel['target'];

                    $singularA = Str::snake(Str::singular($nameA));
                    $singularB = Str::snake(Str::singular($nameB));
                    $names = [$singularA, $singularB];
                    sort($names);
                    $pivotTable = $names[0] . '_' . $names[1];

                    // Check if pivot migration already exists
                    $exists = false;
                    foreach ($existingMigrations as $em) {
                        if (str_contains($em, "create_{$pivotTable}_table")) {
                            $exists = true;
                            break;
                        }
                    }

                    if (!$exists) {
                        $tableA = $entity['table'];
                        // Find B table
                        $tableB = Str::snake(Str::plural($nameB));
                        foreach ($entities as $e) {
                            if ($e['name'] === $nameB) {
                                $tableB = $e['table'];
                                break;
                            }
                        }

                        $foreignA = $singularA . '_id';
                        $foreignB = $singularB . '_id';

                        $template = $this->getPivotMigrationTemplate();
                        $code = str_replace(
                            ['{{pivotTable}}', '{{foreignA}}', '{{tableA}}', '{{foreignB}}', '{{tableB}}'],
                            [$pivotTable, $foreignA, $tableA, $foreignB, $tableB],
                            $template
                        );

                        $timestamp = date('Y_m_d_His', time() + $migrationCounter++);
                        $filename = "{$timestamp}_create_{$pivotTable}_table.php";
                        $path = "{$migrationsDir}/{$filename}";
                        File::put($path, $code);
                        $generated[] = $path;
                    }
                }
            }
        }
        return $generated;
    }

    protected function getMigrationColumnCode($field)
    {
        $name = $field['name'];
        $type = $field['type'];
        $required = $field['required'] ?? false;
        $unique = $field['unique'] ?? false;
        $default = $field['default'] ?? null;

        if ($type === 'id') {
            return ""; 
        }

        $code = "";
        if ($type === 'foreignId') {
            $targetTable = $field['relation_target'] ?? '';
            $code = "\$table->foreignId('{$name}')";
            if (!$required) {
                $code .= "->nullable()";
            }
            if ($targetTable) {
                $code .= "->constrained('{$targetTable}')";
                if ($required) {
                    $code .= "->cascadeOnDelete()";
                } else {
                    $code .= "->nullOnDelete()";
                }
            }
        } else {
            if ($type === 'decimal') {
                $code = "\$table->decimal('{$name}', 10, 2)";
            } elseif ($type === 'datetime') {
                $code = "\$table->dateTime('{$name}')";
            } else {
                $code = "\$table->{$type}('{$name}')";
            }

            if (!$required) {
                $code .= "->nullable()";
            }
            if ($unique) {
                $code .= "->unique()";
            }
            if ($default !== null && $default !== '') {
                if ($type === 'boolean') {
                    $val = ($default === 'true' || $default === true || $default === '1') ? 'true' : 'false';
                    $code .= "->default({$val})";
                } elseif (is_numeric($default)) {
                    $code .= "->default({$default})";
                } else {
                    $code .= "->default('{$default}')";
                }
            }
        }

        return $code . ";";
    }

    protected function updateRoutes(array $entities)
    {
        $routesPath = base_path('routes/designer.php');
        
        $routesCode = "<?php\n\n";
        $routesCode .= "use Illuminate\Support\Facades\Route;\n\n";
        $routesCode .= "// -- ENTITY DESIGNER ROUTES START --\n";
        foreach ($entities as $entity) {
            $name = $entity['name'];
            $table = $entity['table'];
            
            // Protection check
            if (in_array($table, $this->protectedTables) || in_array($name, $this->protectedModels)) {
                continue;
            }

            $routesCode .= "Route::group(['prefix' => '{$table}'], function () {\n";
            $routesCode .= "    Route::get('/', [\\App\\Http\\Controllers\\Designer\\{$name}Controller::class, 'index'])->name('{$table}.index');\n";
            $routesCode .= "    Route::post('/nuevo', [\\App\\Http\\Controllers\\Designer\\{$name}Controller::class, 'store'])->name('{$table}.store');\n";
            $routesCode .= "    Route::post('/edita/{id}', [\\App\\Http\\Controllers\\Designer\\{$name}Controller::class, 'update'])->name('{$table}.update');\n";
            $routesCode .= "    Route::post('/elimina/{id}', [\\App\\Http\\Controllers\\Designer\\{$name}Controller::class, 'destroy'])->name('{$table}.destroy');\n";
            $routesCode .= "    Route::get('/buscar/{filtro}', [\\App\\Http\\Controllers\\Designer\\{$name}Controller::class, 'search'])->name('{$table}.search');\n";
            $routesCode .= "    Route::get('/filtro', [\\App\\Http\\Controllers\\Designer\\{$name}Controller::class, 'filter'])->name('{$table}.filter');\n";
            $routesCode .= "});\n\n";
        }
        $routesCode .= "// -- ENTITY DESIGNER ROUTES END --";

        File::put($routesPath, $routesCode);
        $this->addToGitignore($routesPath);
    }

    protected function registerPermissions(array $entities)
    {
        if (!class_exists(\Spatie\Permission\Models\Permission::class)) {
            return;
        }

        foreach ($entities as $entity) {
            $table = $entity['table'];
            $name = $entity['name'];

            // Protection check
            if (in_array($table, $this->protectedTables) || in_array($name, $this->protectedModels)) {
                continue;
            }

            $permissions = [
                "{$table}.index",
                "{$table}.store",
                "{$table}.update",
                "{$table}.destroy",
            ];

            foreach ($permissions as $perm) {
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
                
                // Assign to Admin role if exists
                $adminRole = \Spatie\Permission\Models\Role::where('name', 'Administrador')->first();
                if ($adminRole) {
                    $adminRole->givePermissionTo($perm);
                }
            }
        }
    }

    // --- Template definitions ---

    protected function getModelTemplate()
    {
        return <<<'PHP'
<?php

namespace App\Models\Designer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class {{modelName}} extends Model
{
    use HasFactory;

    protected $table = '{{tableName}}';

    protected $fillable = [
        {{fillableFields}}
    ];

    {{relationMethods}}

    public function scopeFiltros($query, array $filtros)
    {
        foreach ($filtros as $filtro) {
            $campo = $filtro["campo"];
            $condicion = $filtro["condicion"];
            $valor = $filtro["valor"];
            $conjuncion = $filtro["conjuncion"];

            if ($condicion == "LIKE") {
                $valor = "%{$valor}%";
            }

            if ($conjuncion == "AND") {
                $query->where($campo, $condicion, $valor);
            } else {
                $query->orWhere($campo, $condicion, $valor);
            }
        }

        return $query;
    }

    public function scopeFiltro($query, $key)
    {
        return $query{{searchChain}};
    }
}
PHP;
    }

    protected function getControllerTemplate()
    {
        return <<<'PHP'
<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\Designer\{{modelName}};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class {{modelName}}Controller extends Controller
{
    public function index(Request $request)
    {
        $user = \Auth::user();
        if ($user->can("{{tableName}}.index")) {
            $filtro = null;
            $query = {{modelName}}::query();

            {{relationsWith}}

            if ($request->has("orderBy")) {
                $orderBy = $request->orderBy;
                $query->orderBy($orderBy["field"], $orderBy["sort"]);
            } else {
                $orderBy = ["field" => "id", "sort" => "asc"];
            }

            ${{pluralVar}} = $query->paginate(10)
                ->onEachSide(1)
                ->appends(request()->query());

            {{prefetchOptions}}

            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json(compact("{{pluralVar}}", "filtro", "orderBy"{{compactExtra}}));
            }

            return Inertia::render(
                "Designer/{{modelName}}",
                compact("{{pluralVar}}", "filtro", "orderBy"{{compactExtra}})
            );
        } else {
            $request->session()->flash("error", "No cuenta con los permisos necesarios para realizar esta acción.");
            return redirect()->back();
        }
    }

    public function search($filtro, Request $request)
    {
        $user = \Auth::user();
        if ($user->can("{{tableName}}.index")) {
            $query = {{modelName}}::filtro($filtro);
            
            {{relationsWith}}

            if ($request->has("orderBy")) {
                $orderBy = $request->orderBy;
                $query->orderBy($orderBy["field"], $orderBy["sort"]);
            } else {
                $orderBy = ["field" => "id", "sort" => "asc"];
            }

            ${{pluralVar}} = $query->paginate(10)
                ->onEachSide(1)
                ->appends(request()->query());

            {{prefetchOptions}}

            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json(compact("{{pluralVar}}", "filtro", "orderBy"{{compactExtra}}));
            }

            return Inertia::render(
                "Designer/{{modelName}}",
                compact("{{pluralVar}}", "filtro", "orderBy"{{compactExtra}})
            );
        } else {
            return redirect()->back()->with("error", "No cuenta con los permisos necesarios para realizar esta acción.");
        }
    }

    public function filter(Request $request)
    {
        $user = \Auth::user();
        if ($user->can("{{tableName}}.index")) {
            $filtros = $request->filtros;
            $query = {{modelName}}::filtros($filtros);

            {{relationsWith}}

            if ($request->has("orderBy")) {
                $orderBy = $request->orderBy;
                $query->orderBy($orderBy["field"], $orderBy["sort"]);
            } else {
                $orderBy = ["field" => "id", "sort" => "asc"];
            }

            ${{pluralVar}} = $query->paginate(10)
                ->onEachSide(1)
                ->appends(request()->query());

            {{prefetchOptions}}

            if ($request->wantsJson() && !$request->header('X-Inertia')) {
                return response()->json(compact("{{pluralVar}}", "filtros", "orderBy"{{compactExtra}}));
            }

            return Inertia::render(
                "Designer/{{modelName}}",
                compact("{{pluralVar}}", "filtros", "orderBy"{{compactExtra}})
            );
        } else {
            return redirect()->back()->with("error", "No cuenta con los permisos necesarios para realizar esta acción.");
        }
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        if ($user->can("{{tableName}}.store")) {
            $datos = $request->all();
            
            $rules = [
                {{validationRules}}
            ];
            
            Validator::make($datos, $rules)->validate();

            $record = {{modelName}}::create([
                {{createFields}}
            ]);

            return redirect()
                ->back()
                ->with("success", "{{modelName}} creado con éxito")
                ->with("passData", $record);
        } else {
            return redirect()->back()->with("error", "No cuenta con los permisos necesarios para realizar esta acción.");
        }
    }

    public function update(Request $request, $id)
    {
        $user = \Auth::user();
        if ($user->can("{{tableName}}.update")) {
            $datos = $request->all();
            
            $rules = [
                {{validationRules}}
            ];
            
            Validator::make($datos, $rules)->validate();

            $record = {{modelName}}::findOrFail($id);
            $record->update([
                {{createFields}}
            ]);

            return redirect()
                ->back()
                ->with("success", "{{modelName}} actualizado con éxito")
                ->with("passData", $record);
        } else {
            return redirect()->back()->with("error", "No cuenta con los permisos necesarios para realizar esta acción.");
        }
    }

    public function destroy($id)
    {
        $user = \Auth::user();
        if ($user->can("{{tableName}}.destroy")) {
            $record = {{modelName}}::findOrFail($id);
            $record->delete();

            return redirect()
                ->back()
                ->with("success", "{{modelName}} eliminado con éxito");
        } else {
            return redirect()->back()->with("error", "No cuenta con los permisos necesarios para realizar esta acción.");
        }
    }
}
PHP;
    }

    protected function getCreateMigrationTemplate()
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{{tableName}}', function (Blueprint $table) {
            $table->id();
{{columnsCode}}
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{{tableName}}');
    }
};
PHP;
    }

    protected function getAlterMigrationTemplate()
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('{{tableName}}', function (Blueprint $table) {
{{upCode}}
        });
    }

    public function down(): void
    {
        Schema::table('{{tableName}}', function (Blueprint $table) {
{{downCode}}
        });
    }
};
PHP;
    }

    protected function getPivotMigrationTemplate()
    {
        return <<<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{{pivotTable}}', function (Blueprint $table) {
            $table->id();
            $table->foreignId('{{foreignA}}')->constrained('{{tableA}}')->cascadeOnDelete();
            $table->foreignId('{{foreignB}}')->constrained('{{tableB}}')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{{pivotTable}}');
    }
};
PHP;
    }

    protected function getViewTemplate()
    {
        return <<<'VUE'
<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { useForm } from "@inertiajs/vue3";
import { reactive, ref, toRefs, onMounted, onUnmounted, watch, computed } from "vue";
import axios from "axios";
import JetDialogModal from "@/Jetstream/DialogModal";
import JetConfirmationModal from "@/Jetstream/ConfirmationModal";
import JetInput from "@/Jetstream/Input.vue";
import JetLabel from "@/Jetstream/Label.vue";
import JetButton from "@/Jetstream/Button.vue";
import JetSecondaryButton from "@/Jetstream/SecondaryButton.vue";
import Tabla from "@/Components/Tabla.vue";
import { notify } from "notiwind";
import JetInputError from "@/Jetstream/InputError.vue";
import JetDangerButton from "@/Jetstream/DangerButton.vue";
import Select from "@/Components/Select.vue";
import Buscador from "@/Components/Buscador.vue";
import Ordena from "@/Components/Ordena.vue";
import GlowCard from "@/Components/GlowCard.vue";
import Textarea from "@/Components/Textarea.vue";
import Toggle from "@/Components/Toggle.vue";

const props = defineProps({
  {{pluralVar}}: Object,
  filtro: String,
  orderBy: Object,
  auth: Object,
  filtros: Array,
  {{optionsProps}}
});

// Scroll Infinito Logic
const itemsLista = ref([...props.{{pluralVar}}.data]);
const paginaActual = ref(props.{{pluralVar}}.current_page);
const ultimaPagina = ref(props.{{pluralVar}}.last_page);
const loadingMore = ref(false);

const hasMore = computed(() => paginaActual.value < ultimaPagina.value);

watch(
  () => props.{{pluralVar}},
  (newItems) => {
    if (newItems.current_page === 1) {
      itemsLista.value = [...newItems.data];
      paginaActual.value = newItems.current_page;
      ultimaPagina.value = newItems.last_page;
    }
  },
  { deep: true }
);

const loadMore = async () => {
  if (loadingMore.value || !hasMore.value) return;
  loadingMore.value = true;

  const nextPage = paginaActual.value + 1;
  const currentParams = new URLSearchParams(window.location.search);
  currentParams.set("page", nextPage);

  try {
    const response = await axios.get(
      `${window.location.pathname}?${currentParams.toString()}`,
      {
        headers: {
          Accept: "application/json",
          "X-Requested-With": "XMLHttpRequest",
        },
      }
    );

    if (response.data && response.data.{{pluralVar}}) {
      itemsLista.value.push(...response.data.{{pluralVar}}.data);
      paginaActual.value = response.data.{{pluralVar}}.current_page;
      ultimaPagina.value = response.data.{{pluralVar}}.last_page;
    }
  } catch (err) {
    console.error("Error al cargar más registros:", err);
  } finally {
    loadingMore.value = false;
  }
};

const loadMoreTrigger = ref(null);
let infiniteObserver = null;

const setupObserver = () => {
  if (infiniteObserver) {
    infiniteObserver.disconnect();
  }

  infiniteObserver = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        loadMore();
      }
    },
    {
      root: null,
      rootMargin: "150px",
    }
  );

  if (loadMoreTrigger.value) {
    infiniteObserver.observe(loadMoreTrigger.value);
  }
};

onMounted(() => {
  setupObserver();
});

onUnmounted(() => {
  if (infiniteObserver) {
    infiniteObserver.disconnect();
  }
});

const form = useForm({
{{formFields}}
});

const error = reactive({
{{errorFields}}
});

const items = toRefs(props).{{pluralVar}};
const buscar = ref(props.filtro ? props.filtro : "");
const filtros = ref(props.filtros ? props.filtros : []);
const estadoModal = ref(false);
const estadoModalElimina = ref(false);
const accion = ref("new");
const idActual = ref(null);
const orderByObject = ref(
  props.orderBy ? props.orderBy : { field: "id", sort: "asc" }
);

const nuevoRegistro = () => {
  accion.value = "new";
  form.reset();
  {{resetErrors}}
  estadoModal.value = true;
};

const editaRegistro = (item) => {
  accion.value = "edit";
  idActual.value = item.id;
  {{editMapping}}
  {{resetErrors}}
  estadoModal.value = true;
};

const campos = {
{{camposProps}}
};

const nuevoRegistroDo = () => {
  {{resetErrors}}
  idActual.value = null;
  form.post(route("{{tableName}}.store"), {
    preserveScroll: true,
    onSuccess: () => {
      notify({ group: "main", title: "Creado", text: "Registro creado exitosamente" }, 3000);
      estadoModal.value = false;
      form.reset();
    },
    onError: (errors) => {
      {{errorAssignments}}
    },
  });
};

const editaRegistroDo = () => {
  form.post(route("{{tableName}}.update", idActual.value), {
    preserveScroll: true,
    onSuccess: () => {
      notify({ group: "main", title: "Actualizado", text: "Registro actualizado exitosamente" }, 3000);
      estadoModal.value = false;
      form.reset();
    },
    onError: (errors) => {
      {{errorAssignments}}
    },
  });
};

const borraRegistroDo = () => {
  form.post(route("{{tableName}}.destroy", idActual.value), {
    preserveScroll: true,
    onSuccess: () => {
      notify({ group: "main", title: "Eliminado", text: "Registro eliminado exitosamente" }, 3000);
      estadoModal.value = false;
      estadoModalElimina.value = false;
      form.reset();
    },
  });
};
</script>

<template>
  <AppLayout title="{{pluralLabel}}" :mainScrollable="false">
    <div class="w-full max-w-full mx-auto p-0 flex-1 min-h-0 flex flex-col">
      <GlowCard rounded="rounded-none" class="flex-1 min-h-0 border-0">
        <div class="flex flex-col h-full w-full">
          <Buscador
              v-model="buscar"
              :orderByObject="orderByObject"
              ruta="{{tableName}}"
              :filtros="filtros"
              :campos="campos"
              autofocus
            />
            <Tabla v-if="itemsLista.length > 0" heightClass="flex-1 min-h-0">
            <template #col>
{{tableCols}}
              <th class="px-4 py-3 w-5 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border">
                <button 
                    @click="nuevoRegistro" 
                    class="inline-flex items-center justify-center px-3 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded font-bold transition duration-200 shadow-md shadow-brand-500/20 hover:shadow-lg hover:shadow-brand-500/30"
                    title="Agregar Nuevo"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </button>
              </th>
            </template>
            <template #row>
              <tr
                class="text-slate-300 hover:bg-dark-elevated border-b border-dark-border"
                v-for="item in itemsLista"
                :key="item.id"
              >
{{tableRows}}
                <td class="px-4 py-3 text-center sticky right-0 bg-dark-surface/50 border-l border-dark-border">
                  <button
                    @click="editaRegistro(item)"
                    class="inline-flex items-center justify-center px-3 py-2 bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white rounded transition duration-200 shadow-md shadow-blue-500/20 hover:shadow-lg hover:shadow-blue-500/30"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                  </button>
                </td>
              </tr>
              <tr ref="loadMoreTrigger" class="opacity-0 h-1">
                <td colspan="100"></td>
              </tr>
            </template>
            <template #pagination>
              <div class="px-4 py-2.5 border-t border-dark-border text-xs font-semibold tracking-wide text-slate-500 uppercase bg-dark-surface/50 bg-glass-gradient backdrop-blur-md flex justify-between items-center select-none">
                <span>Mostrando {{ itemsLista.length }} de {{ {{pluralVar}}.total }} registros</span>
                <span v-if="loadingMore" class="flex items-center gap-1.5 text-brand-400">
                  <font-awesome-icon icon="spinner" class="animate-spin" />
                  Cargando más...
                </span>
                <span v-else-if="!hasMore && itemsLista.length > 0" class="text-slate-600 dark:text-slate-500">
                  Fin de la lista
                </span>
              </div>
            </template>
          </Tabla>
          <div v-else class="text-center p-5 text-slate-400 text-md">
            Sin Registros
            <br />
            <button 
                @click="nuevoRegistro" 
                class="mt-2 inline-flex items-center justify-center px-4 py-2 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white rounded-lg font-semibold text-xs uppercase tracking-widest transition duration-200 shadow-md shadow-brand-500/20"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Agregar Nuevo Registro
            </button>
          </div>
        </div>
      </GlowCard>
    </div>

    <form class="flex flex-col" @submit.prevent="accion === 'new' ? nuevoRegistroDo : editaRegistroDo">
      <JetDialogModal :show="estadoModal" @close="estadoModal = false" max-width="md">
        <template #title>
          {{ accion === "new" ? "Crear nuevo" : "Editar" }}
          <JetDangerButton v-if="accion === 'edit'" @click="estadoModalElimina = true" class="-mt-1 float-right" :disabled="form.processing">
            <font-awesome-icon icon="trash" class="mr-2" />
            Eliminar
          </JetDangerButton>
        </template>
        <template #content>
{{modalInputs}}
        </template>
        <template #footer>
          <div class="w-1/2">
            <JetSecondaryButton @click="estadoModal = false" class="bg-opacity-95" type="button" :disabled="form.processing">Cancelar</JetSecondaryButton>
          </div>
          <div class="w-1/2">
            <JetButton v-if="accion === 'new'" @click="nuevoRegistroDo" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="float-right">
              <template v-if="form.processing">
                <font-awesome-icon icon="spinner" class="mr-2 animate-spin" />Creando...
              </template>
              <template v-else>
                <font-awesome-icon icon="plus" class="mr-2" />Crear Registro
              </template>
            </JetButton>
            <JetButton v-if="accion === 'edit'" @click="editaRegistroDo" :class="{ 'opacity-25': form.processing }" class="float-right" :disabled="form.processing">
              <template v-if="form.processing">
                <font-awesome-icon icon="spinner" class="mr-2 animate-spin" />Guardando...
              </template>
              <template v-else>
                <font-awesome-icon icon="floppy-disk" class="mr-2" />Guardar Cambios
              </template>
            </JetButton>
          </div>
        </template>
      </JetDialogModal>
    </form>

    <JetConfirmationModal :show="estadoModalElimina" @close="estadoModalElimina = false">
      <template #title>Eliminar registro</template>
      <template #content>
        <h4 class="block mb-1 text-lg text-left text-slate-100">¿Realmente desea eliminar este registro?</h4>
        <span class="text-red-500 float-left">Esta acción no se puede deshacer.</span>
      </template>
      <template #footer>
        <div class="w-1/2">
          <JetSecondaryButton @click="estadoModalElimina = false" class="bg-opacity-95" type="button" :disabled="form.processing">Cancelar</JetSecondaryButton>
        </div>
        <div class="w-1/2">
          <JetDangerButton @click="borraRegistroDo" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" class="float-right">
            <font-awesome-icon icon="trash" class="mr-2" />Eliminar Registro
          </JetDangerButton>
        </div>
      </template>
    </JetConfirmationModal>
  </AppLayout>
</template>
VUE;
    }

    protected function generateRelationMethodsForEntity(array $entity, array $entities)
    {
        $name = $entity['name'];
        $table = $entity['table'] ?? Str::snake(Str::plural($name));
        $relations = $entity['relations'] ?? [];

        $relationMethods = "";
        $generatedMethods = [];

        // 1. Direct Relations
        foreach ($relations as $rel) {
            $relType = $rel['type']; // belongsTo, hasMany, belongsToMany
            $target = $rel['target'];
            $relName = $rel['relation_name'] ?? Str::camel($target);
            $fk = $rel['foreign_key'] ?? Str::snake($target) . '_id';

            if ($relType === 'belongsTo') {
                $methodName = $relName;
                if (!in_array($methodName, $generatedMethods)) {
                    $targetFqn = $this->getModelFQN($target);
                    $relationMethods .= "    public function {$methodName}()\n    {\n        return \$this->belongsTo({$targetFqn}::class, '{$fk}');\n    }\n\n";
                    $generatedMethods[] = $methodName;
                }
            } elseif ($relType === 'hasMany') {
                $methodName = Str::plural($relName);
                if (!in_array($methodName, $generatedMethods)) {
                    $targetFqn = $this->getModelFQN($target);
                    $relationMethods .= "    public function {$methodName}()\n    {\n        return \$this->hasMany({$targetFqn}::class, '{$fk}');\n    }\n\n";
                    $generatedMethods[] = $methodName;
                }
            } elseif ($relType === 'belongsToMany') {
                $singularA = Str::snake(Str::singular($name));
                $singularB = Str::snake(Str::singular($target));
                $names = [$singularA, $singularB];
                sort($names);
                $pivotTable = $names[0] . '_' . $names[1];

                $methodName = Str::plural($relName);
                if (!in_array($methodName, $generatedMethods)) {
                    $targetFqn = $this->getModelFQN($target);
                    $relationMethods .= "    public function {$methodName}()\n    {\n        return \$this->belongsToMany({$targetFqn}::class, '{$pivotTable}');\n    }\n\n";
                    $generatedMethods[] = $methodName;
                }
            }
        }

        // 2. Inverse Relations
        foreach ($entities as $otherEntity) {
            if ($otherEntity['name'] === $name) {
                continue;
            }

            $otherRelations = $otherEntity['relations'] ?? [];
            foreach ($otherRelations as $otherRel) {
                if ($otherRel['target'] === $name) {
                    $otherType = $otherRel['type'];
                    $otherName = $otherEntity['name'];
                    $otherFk = $otherRel['foreign_key'] ?? Str::snake($name) . '_id';

                    if ($otherType === 'belongsTo') {
                        $methodName = Str::plural(Str::camel($otherName));
                        if (!in_array($methodName, $generatedMethods)) {
                            $otherFqn = $this->getModelFQN($otherName);
                            $relationMethods .= "    public function {$methodName}()\n    {\n        return \$this->hasMany({$otherFqn}::class, '{$otherFk}');\n    }\n\n";
                            $generatedMethods[] = $methodName;
                        }
                    } elseif ($otherType === 'hasMany') {
                        $methodName = Str::camel($otherName);
                        if (!in_array($methodName, $generatedMethods)) {
                            $otherFqn = $this->getModelFQN($otherName);
                            $relationMethods .= "    public function {$methodName}()\n    {\n        return \$this->belongsTo({$otherFqn}::class, '{$otherFk}');\n    }\n\n";
                            $generatedMethods[] = $methodName;
                        }
                    } elseif ($otherType === 'belongsToMany') {
                        $singularA = Str::snake(Str::singular($name));
                        $singularB = Str::snake(Str::singular($otherName));
                        $names = [$singularA, $singularB];
                        sort($names);
                        $pivotTable = $names[0] . '_' . $names[1];

                        $methodName = Str::plural(Str::camel($otherName));
                        if (!in_array($methodName, $generatedMethods)) {
                            $otherFqn = $this->getModelFQN($otherName);
                            $relationMethods .= "    public function {$methodName}()\n    {\n        return \$this->belongsToMany({$otherFqn}::class, '{$pivotTable}');\n    }\n\n";
                            $generatedMethods[] = $methodName;
                        }
                    }
                }
            }
        }

        return $relationMethods;
    }

    protected function updateProtectedModelRelations($name, $relationMethods)
    {
        $path = app_path("Models/{$name}.php");
        if (!File::exists($path)) return;

        $content = File::get($path);
        
        $startComment = "// -- ENTITY DESIGNER RELATIONS START --";
        $endComment = "// -- ENTITY DESIGNER RELATIONS END --";
        
        $newBlock = "    {$startComment}\n" . rtrim($relationMethods) . "\n    {$endComment}";

        if (str_contains($content, $startComment) && str_contains($content, $endComment)) {
            $pattern = '/' . preg_quote($startComment, '/') . '.*?' . preg_quote($endComment, '/') . '/s';
            $newContent = preg_replace($pattern, $newBlock, $content);
        } else {
            $pos = strrpos($content, '}');
            if ($pos !== false) {
                $newContent = substr_replace($content, "\n" . $newBlock . "\n", $pos, 0);
            } else {
                $newContent = $content . "\n" . $newBlock;
            }
        }

        File::put($path, $newContent);
    }

    /**
     * Returns the fully-qualified class name for a model.
     * Protected/system models live in App\Models\;
     * Designer-generated models live in App\Models\Designer\.
     */
    protected function getModelFQN(string $name): string
    {
        if (in_array($name, $this->protectedModels)) {
            return "\\App\\Models\\{$name}";
        }
        return "\\App\\Models\\Designer\\{$name}";
    }

    protected function addToGitignore($path)
    {
        $gitignorePath = base_path('.gitignore');
        if (!File::exists($gitignorePath)) return;

        $content = File::get($gitignorePath);
        $relativePath = '/' . ltrim(str_replace(base_path(), '', $path), '/\\');
        $relativePath = str_replace('\\', '/', $relativePath); // normalize path slashes

        // Check if already in gitignore
        if (!str_contains($content, $relativePath)) {
            File::append($gitignorePath, "\n{$relativePath}");
        }
    }

    protected function getExistingMigrations()
    {
        $dir = database_path('migrations');
        $designerDir = database_path('migrations/designer');
        
        $files = glob("{$dir}/*.php") ?: [];
        $designerFiles = glob("{$designerDir}/*.php") ?: [];
        
        $allFiles = array_merge($files, $designerFiles);
        return array_map('basename', $allFiles);
    }
}
