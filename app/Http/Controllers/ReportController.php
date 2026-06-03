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
            'sort_by' => 'nullable|string',
            'sort_order' => 'required|in:asc,desc',
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
            'sort_by' => 'nullable|string',
            'sort_order' => 'required|in:asc,desc',
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

    public function preview($id)
    {
        $report = Report::findOrFail($id);
        $entities = $this->getEntitiesMetadata();
        
        $data = $this->buildReportQuery($report, $entities);
        $formatted = $this->formatReportData($report, $data, $entities);

        return response()->json([
            'ok' => true,
            'data' => $formatted,
            'headers' => count($formatted) > 0 ? array_keys($formatted[0]) : []
        ]);
    }

    public function exportExcel($id)
    {
        $report = Report::findOrFail($id);
        $entities = $this->getEntitiesMetadata();
        
        $data = $this->buildReportQuery($report, $entities);
        $formatted = $this->formatReportData($report, $data, $entities);

        $headings = count($formatted) > 0 ? array_keys($formatted[0]) : $report->fields;
        $export = new DynamicReportExport(collect($formatted), $headings);

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
        $modelClass = $entityName === 'User' ? 'App\\Models\\User' : 'App\\Models\\Designer\\' . $entityName;

        if (!class_exists($modelClass)) {
            throw new \Exception("La entidad {$entityName} no existe o no ha sido generada.");
        }

        $query = $modelClass::query();

        // Find relations for eager loading
        $entityMetadata = collect($entities)->firstWhere('name', $entityName);
        foreach ($entityMetadata['relations'] ?? [] as $rel) {
            if ($rel['type'] === 'belongsTo') {
                $relName = $rel['relation_name'] ?? Str::camel($rel['target']);
                $query->with($relName);
            }
        }

        // Apply filters
        if (!empty($report->filters)) {
            foreach ($report->filters as $filter) {
                $field = $filter['field'] ?? null;
                $operator = $filter['operator'] ?? '=';
                $value = $filter['value'] ?? null;

                if ($field && $value !== null) {
                    if ($operator === 'like') {
                        $query->where($field, 'LIKE', '%' . $value . '%');
                    } else {
                        $query->where($field, $operator, $value);
                    }
                }
            }
        }

        // Apply sorting
        if ($report->sort_by) {
            $query->orderBy($report->sort_by, $report->sort_order ?: 'asc');
        }

        return $query->get();
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
            return $row;
        })->toArray();
    }
}
