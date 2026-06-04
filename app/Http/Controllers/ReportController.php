<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Exports\DynamicReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::with('user');

        if ($request->has("orderBy")) {
            $orderBy = $request->orderBy;
            $query->orderBy($orderBy["field"], $orderBy["sort"]);
        } else {
            $orderBy = ["field" => "id", "sort" => "asc"];
        }

        $reports = $query->paginate(10)
            ->onEachSide(1)
            ->appends(request()->query());

        $entities = $this->getEntitiesMetadata();
        $filtro = null;

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json(compact("reports", "filtro", "orderBy", "entities"));
        }

        return Inertia::render('Reportes/Index', [
            'reports' => $reports,
            'entities' => $entities,
            'filtro' => $filtro,
            'orderBy' => $orderBy
        ]);
    }

    public function search($filtro, Request $request)
    {
        $query = Report::filtro($filtro)->with('user');

        if ($request->has("orderBy")) {
            $orderBy = $request->orderBy;
            $query->orderBy($orderBy["field"], $orderBy["sort"]);
        } else {
            $orderBy = ["field" => "id", "sort" => "asc"];
        }

        $reports = $query->paginate(10)
            ->onEachSide(1)
            ->appends(request()->query());

        $entities = $this->getEntitiesMetadata();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json(compact("reports", "filtro", "orderBy", "entities"));
        }

        return Inertia::render('Reportes/Index', [
            'reports' => $reports,
            'entities' => $entities,
            'filtro' => $filtro,
            'orderBy' => $orderBy
        ]);
    }

    public function filter(Request $request)
    {
        $filtros = $request->filtros;
        $query = Report::filtros($filtros)->with('user');

        if ($request->has("orderBy")) {
            $orderBy = $request->orderBy;
            $query->orderBy($orderBy["field"], $orderBy["sort"]);
        } else {
            $orderBy = ["field" => "id", "sort" => "asc"];
        }

        $reports = $query->paginate(10)
            ->onEachSide(1)
            ->appends(request()->query());

        $entities = $this->getEntitiesMetadata();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json(compact("reports", "filtros", "orderBy", "entities"));
        }

        return Inertia::render('Reportes/Index', [
            'reports' => $reports,
            'entities' => $entities,
            'filtros' => $filtros,
            'orderBy' => $orderBy
        ]);
    }

    public function create()
    {
        $entitiesPath = database_path('metadata/entities.json');
        $entities = [];
        if (File::exists($entitiesPath)) {
            $entities = json_decode(File::get($entitiesPath), true);
        }
        return Inertia::render('Reportes/Builder', [
            'entities' => $entities,
            'report' => null
        ]);
    }

    public function edit($id)
    {
        $report = Report::findOrFail($id);
        $entitiesPath = database_path('metadata/entities.json');
        $entities = [];
        if (File::exists($entitiesPath)) {
            $entities = json_decode(File::get($entitiesPath), true);
        }
        return Inertia::render('Reportes/Builder', [
            'entities' => $entities,
            'report' => $report
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'entity_name' => 'required|string',
            'fields' => 'required|array|min:1',
            'filters' => 'nullable|array',
            'filters_operator' => 'nullable|in:and,or',
            'sort_by' => 'nullable|string',
            'sort_order' => 'required|in:asc,desc',
            'group_by' => 'nullable|string',
            'aggregations' => 'nullable|array',
        ]);

        $validated['user_id'] = \Auth::id();

        $report = Report::create($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Plantilla de reporte guardada con éxito.',
            'report' => $report
        ]);
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'required|array|min:1',
            'filters' => 'nullable|array',
            'filters_operator' => 'nullable|in:and,or',
            'sort_by' => 'nullable|string',
            'sort_order' => 'required|in:asc,desc',
            'group_by' => 'nullable|string',
            'aggregations' => 'nullable|array',
        ]);

        $report->update($validated);

        return response()->json([
            'ok' => true,
            'message' => 'Plantilla de reporte actualizada con éxito.',
            'report' => $report
        ]);
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return redirect()->route('reportes.index')->with('message', 'Plantilla de reporte eliminada con éxito.');
    }

    public function livePreview(Request $request)
    {
        $validated = $request->validate([
            'entity_name' => 'required|string',
            'fields' => 'required|array|min:1',
            'filters' => 'nullable|array',
            'filters_operator' => 'nullable|in:and,or',
            'sort_by' => 'nullable|string',
            'sort_order' => 'required|in:asc,desc',
            'group_by' => 'nullable|string',
            'aggregations' => 'nullable|array',
        ]);

        $tempReport = new Report($validated);
        $entities = $this->getEntitiesMetadata();

        try {
            $data = $this->buildReportQuery($tempReport, $entities);
            $formatted = $this->formatReportData($tempReport, $data, $entities);

            $headers = count($formatted) > 0 
                ? array_values(array_filter(array_keys($formatted[0]), function($key) {
                    return strpos($key, '_') !== 0;
                })) 
                : [];

            return response()->json([
                'ok' => true,
                'data' => $formatted,
                'headers' => $headers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function preview($id)
    {
        $report = Report::findOrFail($id);
        $entities = $this->getEntitiesMetadata();
        
        $data = $this->buildReportQuery($report, $entities);
        $formatted = $this->formatReportData($report, $data, $entities);

        $headers = count($formatted) > 0 
            ? array_values(array_filter(array_keys($formatted[0]), function($key) {
                return strpos($key, '_') !== 0;
            })) 
            : [];

        return response()->json([
            'ok' => true,
            'data' => $formatted,
            'headers' => $headers
        ]);
    }

    public function exportExcel($id)
    {
        $report = Report::findOrFail($id);
        $entities = $this->getEntitiesMetadata();
        
        $data = $this->buildReportQuery($report, $entities);
        $formatted = $this->formatReportData($report, $data, $entities);

        $headings = count($formatted) > 0 
            ? array_values(array_filter(array_keys($formatted[0]), function($key) {
                return strpos($key, '_') !== 0;
            })) 
            : $report->fields;

        $formattedExport = collect($formatted)->map(function ($row) {
            return collect($row)->filter(function ($value, $key) {
                return strpos($key, '_') !== 0;
            })->all();
        });

        $export = new DynamicReportExport($formattedExport, $headings);

        $fileName = Str::slug($report->name) . '_' . date('YmdHis') . '.xlsx';

        return Excel::download($export, $fileName);
    }

    // Helper methods
    private function getEntitiesMetadata()
    {
        $entitiesPath = database_path('metadata/entities.json');
        if (File::exists($entitiesPath)) {
            return json_decode(File::get($entitiesPath), true);
        }
        return [];
    }

    private function buildReportQuery($report, $entities)
    {
        $entityName = $report->entity_name;
        $entityMetadata = collect($entities)->firstWhere('name', $entityName);
        $modelClass = $entityName === 'User' ? 'App\\Models\\User' : 'App\\Models\\Designer\\' . $entityName;

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class not found: " . $modelClass);
        }

        $query = $modelClass::query();

        // Find relations for eager loading
        foreach ($entityMetadata['relations'] ?? [] as $rel) {
            if ($rel['type'] === 'belongsTo') {
                $relName = $rel['relation_name'] ?? Str::camel($rel['target']);
                $query->with($relName);
            }
        }

        // Apply filters (recursive nested groups support)
        if (!empty($report->filters)) {
            $defaultOperator = $report->filters_operator ?? 'and';
            $this->applyFilterNode($query, $report->filters, $defaultOperator);
        }

        // Apply sorting
        if ($report->group_by) {
            $query->orderBy($report->group_by, 'asc');
        }
        if ($report->sort_by) {
            $query->orderBy($report->sort_by, $report->sort_order ?: 'asc');
        }

        return $query->get();
    }

    private function applyFilterNode($query, $node, $defaultOperator = 'and')
    {
        // For backward compatibility: if it's a flat array of rules
        if (isset($node[0]) && !isset($node[0]['type'])) {
            $query->where(function ($subQuery) use ($node, $defaultOperator) {
                foreach ($node as $index => $rule) {
                    $this->applyRule($subQuery, $rule, $defaultOperator, $index === 0);
                }
            });
            return;
        }

        // If it's a single rule node
        if (isset($node['type']) && $node['type'] === 'rule') {
            $this->applyRule($query, $node, $defaultOperator, true);
            return;
        }

        // If it's a group node
        if (isset($node['type']) && $node['type'] === 'group') {
            $groupOperator = $node['operator'] ?? 'and';
            $rules = $node['rules'] ?? [];

            if (empty($rules)) {
                return;
            }

            $query->where(function ($subQuery) use ($rules, $groupOperator) {
                foreach ($rules as $index => $subNode) {
                    $joinOperator = ($groupOperator === 'or' && $index > 0) ? 'or' : 'and';

                    if (isset($subNode['type']) && $subNode['type'] === 'group') {
                        if ($joinOperator === 'or') {
                            $subQuery->orWhere(function ($innerQuery) use ($subNode) {
                                $this->applyFilterNode($innerQuery, $subNode);
                            });
                        } else {
                            $subQuery->where(function ($innerQuery) use ($subNode) {
                                $this->applyFilterNode($innerQuery, $subNode);
                            });
                        }
                    } else {
                        // It's a rule
                        $this->applyRule($subQuery, $subNode, $groupOperator, $index === 0);
                    }
                }
            });
        }
    }

    private function applyRule($query, $rule, $operatorType, $isFirst = false)
    {
        $field = $rule['field'] ?? null;
        $operator = $rule['operator'] ?? '=';
        $value = $rule['value'] ?? null;

        if ($field && $value !== null) {
            // Dynamic values evaluation
            if ($value === '{HOY}') {
                $value = now()->toDateString();
            } elseif ($value === '{AYER}') {
                $value = now()->subDay()->toDateString();
            } elseif ($value === '{INICIO_MES}') {
                $value = now()->startOfMonth()->toDateString();
            } elseif ($value === '{FIN_MES}') {
                $value = now()->endOfMonth()->toDateString();
            } elseif ($value === '{USUARIO_AUTENTICADO}') {
                $value = auth()->id();
            }

            $whereMethod = ($operatorType === 'or' && !$isFirst) ? 'orWhere' : 'where';

            if (str_contains($field, '.')) {
                [$relation, $relField] = explode('.', $field);
                $relationMethod = Str::camel($relation);
                $whereHasMethod = ($operatorType === 'or' && !$isFirst) ? 'orWhereHas' : 'whereHas';

                $query->$whereHasMethod($relationMethod, function ($q) use ($relField, $operator, $value) {
                    if ($operator === 'like') {
                        $q->where($relField, 'LIKE', '%' . $value . '%');
                    } else {
                        $q->where($relField, $operator, $value);
                    }
                });
            } else {
                if ($operator === 'like') {
                    $query->$whereMethod($field, 'LIKE', '%' . $value . '%');
                } else {
                    $query->$whereMethod($field, $operator, $value);
                }
            }
        }
    }

    private function formatReportData($report, $data, $entities)
    {
        $entityMetadata = collect($entities)->firstWhere('name', $report->entity_name);
        $fieldsMeta = collect($entityMetadata['fields'] ?? []);

        $relationMap = [];
        foreach ($entityMetadata['relations'] ?? [] as $rel) {
            if ($rel['type'] === 'belongsTo') {
                $fk = $rel['foreign_key'] ?? Str::snake($rel['target']) . '_id';
                $relationMap[$fk] = $rel;
            }
        }

        return $data->map(function ($item) use ($report, $fieldsMeta, $relationMap) {
            $row = [];
            foreach ($report->fields as $field) {
                $fieldMeta = $fieldsMeta->firstWhere('name', $field);
                $label = $fieldMeta['label'] ?? $field;
                $value = $item->{$field};

                if (isset($relationMap[$field])) {
                    $rel = $relationMap[$field];
                    $relName = $rel['relation_name'] ?? Str::camel($rel['target']);
                    $related = $item->{$relName};
                    if ($related) {
                        $value = $related->nombre ?? $related->name ?? $related->titulo ?? $related->title ?? $related->id;
                    }
                } elseif ($fieldMeta && $fieldMeta['type'] === 'boolean') {
                    $value = $value ? 'Sí' : 'No';
                }

                $row[$label] = $value;
            }

            // Include group metadata for visual grouping on the frontend
            if ($report->group_by) {
                $groupByField = $report->group_by;
                $groupByFieldMeta = $fieldsMeta->firstWhere('name', $groupByField);
                $groupByLabel = $groupByFieldMeta['label'] ?? $groupByField;
                $groupByValue = $item->{$groupByField};

                if (isset($relationMap[$groupByField])) {
                    $rel = $relationMap[$groupByField];
                    $relName = $rel['relation_name'] ?? Str::camel($rel['target']);
                    $related = $item->{$relName};
                    if ($related) {
                        $groupByValue = $related->nombre ?? $related->name ?? $related->titulo ?? $related->title ?? $related->id;
                    }
                }

                $row['_group_by_label'] = $groupByLabel;
                $row['_group_by_value'] = $groupByValue;
            }

            return $row;
        })->toArray();
    }
}
