<?php

namespace App\Http\Controllers\Jmd;

use App\Data\Jmd\AbrasionObservationData;
use App\Data\Jmd\BulkDensityObservationData;
use App\Data\Jmd\CoarseAggregateSpecificGravityData;
use App\Data\Jmd\FineAggregateSpecificGravityData;
use App\Data\Jmd\MoistureObservationData;
use App\Data\Jmd\SiltObservationData;
use App\Enums\AggregateType;
use App\Enums\JmdStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jmd\StoreAbrasionTestRequest;
use App\Http\Requests\Jmd\StoreBulkDensityTestRequest;
use App\Http\Requests\Jmd\StoreCementSpecificGravityTestRequest;
use App\Http\Requests\Jmd\StoreCoarseAggregateSpecificGravityTestRequest;
use App\Http\Requests\Jmd\StoreFineAggregateSpecificGravityTestRequest;
use App\Http\Requests\Jmd\StoreMoistureTestRequest;
use App\Http\Requests\Jmd\StoreSieveTestRequest;
use App\Http\Requests\Jmd\StoreSiltTestRequest;
use App\Models\Jmd\AbrasionTest;
use App\Models\Jmd\BulkDensityTest;
use App\Models\Jmd\CementSpecificGravityTest;
use App\Models\Jmd\CoarseAggregateSpecificGravityTest;
use App\Models\Jmd\FineAggregateSpecificGravityTest;
use App\Models\Jmd\MoistureTest;
use App\Models\Jmd\SieveTest;
use App\Models\Jmd\SiltTest;
use App\Models\Project;
use App\Services\AuditService;
use App\Services\Jmd\AbrasionService;
use App\Services\Jmd\BulkDensityService;
use App\Services\Jmd\CementSpecificGravityService;
use App\Services\Jmd\CoarseAggregateSpecificGravityService;
use App\Services\Jmd\FineAggregateSpecificGravityService;
use App\Services\Jmd\MoistureContentService;
use App\Services\Jmd\SieveAnalysisService;
use App\Services\Jmd\SiltContentService;
use App\Services\Jmd\StandardMasterService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class MaterialTestController extends Controller
{
    public function projects()
    {
        return view('jmd.material-tests.projects', [
            'projects' => Project::query()->latest()->paginate(20),
        ]);
    }

    public function index(Project $project)
    {
        Gate::authorize('view', $project);
        $modules = self::modules();
        foreach ($modules as $key => &$module) {
            $model = $module['model'];
            $module['latest'] = $model::query()->where('project_id', $project->id)->latest('tested_at')->latest('id')->first();
            $module['count'] = $model::query()->where('project_id', $project->id)->count();
            $module['key'] = $key;
        }

        return view('jmd.material-tests.index', compact('project', 'modules'));
    }

    public function form(Project $project, string $module, Request $request, StandardMasterService $standards)
    {
        Gate::authorize('view', $project);
        $config = self::modules()[$module] ?? abort(Response::HTTP_NOT_FOUND);
        $model = $config['model'];
        $record = $request->integer('test')
            ? $model::query()->where('project_id', $project->id)->with('items')->findOrFail($request->integer('test'))
            : null;

        return view('jmd.material-tests.form', [
            'project' => $project,
            'module' => $module,
            'config' => $config,
            'record' => $record,
            'materials' => $project->jmdMaterials()->orderBy('material_type')->get(),
            'history' => $model::query()->where('project_id', $project->id)->latest('tested_at')->latest('id')->limit(15)->get(),
            'standardTables' => $standards->activeTables($config['standard_keys']),
        ]);
    }

    public function storeMoisture(Project $project, StoreMoistureTestRequest $request, MoistureContentService $service)
    {
        return $this->persist($project, $request, 'moisture', fn ($data) => $service->calculate(
            array_map(fn ($row) => MoistureObservationData::fromArray($row), $data['observations']), $data['standard_source']
        ));
    }

    public function storeSilt(Project $project, StoreSiltTestRequest $request, SiltContentService $service)
    {
        return $this->persist($project, $request, 'silt', fn ($data) => $service->calculate(
            array_map(fn ($row) => SiltObservationData::fromArray($row), $data['observations']), (float) $data['limit_percent'], $data['standard_source']
        ));
    }

    public function storeFineSpecificGravity(Project $project, StoreFineAggregateSpecificGravityTestRequest $request, FineAggregateSpecificGravityService $service)
    {
        return $this->persist($project, $request, 'fine-specific-gravity', fn ($data) => $service->calculate(
            array_map(fn ($row) => FineAggregateSpecificGravityData::fromArray($row), $data['observations']), $data['standard_source']
        ));
    }

    public function storeCoarseSpecificGravity(Project $project, StoreCoarseAggregateSpecificGravityTestRequest $request, CoarseAggregateSpecificGravityService $service)
    {
        return $this->persist($project, $request, 'coarse-specific-gravity', fn ($data) => $service->calculate(
            array_map(fn ($row) => CoarseAggregateSpecificGravityData::fromArray($row), $data['observations']), $data['standard_source']
        ));
    }

    public function storeBulkDensity(Project $project, StoreBulkDensityTestRequest $request, BulkDensityService $service)
    {
        return $this->persist($project, $request, 'bulk-density', fn ($data) => $service->calculate(
            array_map(fn ($row) => BulkDensityObservationData::fromArray($row), $data['observations']), $data['mass_unit'], $data['standard_source']
        ));
    }

    public function storeCementSpecificGravity(Project $project, StoreCementSpecificGravityTestRequest $request, CementSpecificGravityService $service)
    {
        return $this->persist($project, $request, 'cement-specific-gravity', fn ($data) => $service->calculate($data['observations'], $data['standard_source']));
    }

    public function storeSieve(Project $project, StoreSieveTestRequest $request, SieveAnalysisService $service)
    {
        return $this->persist($project, $request, 'sieve', fn ($data) => $service->calculate(
            AggregateType::from($data['aggregate_type']), (float) $data['initial_sample_mass'], $data['observations'],
            (float) $data['loss_tolerance_percent'], $data['standard_source']
        ));
    }

    public function storeAbrasion(Project $project, StoreAbrasionTestRequest $request, AbrasionService $service)
    {
        return $this->persist($project, $request, 'abrasion', fn ($data) => $service->calculate(
            array_map(fn ($row) => AbrasionObservationData::fromArray($row), $data['observations']), (float) $data['limit_percent'], $data['standard_source']
        ));
    }

    private function persist(Project $project, FormRequest $request, string $module, callable $calculate)
    {
        $data = $request->validated();
        $config = self::modules()[$module];
        $model = $config['model'];
        $data = $this->resolveStandardSelection($data, $config);
        try {
            $result = $calculate($data);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['observations' => $exception->getMessage()]);
        }

        $record = DB::transaction(function () use ($project, $data, $config, $model, $result, $module) {
            $record = ! empty($data['test_id'])
                ? $model::query()->where('project_id', $project->id)->lockForUpdate()->findOrFail($data['test_id'])
                : new $model;
            $before = $record->exists ? $record->load('items')->toArray() : null;
            $record->fill($this->headerData($project, $data, $config, $result->toArray(), $record->exists));
            $record->save();

            $position = $config['position'];
            $existingIds = $record->items()->pluck('id')->all();
            $submittedIds = array_values(array_filter(array_map(fn ($row) => isset($row['id']) ? (int) $row['id'] : null, $data['observations'])));
            $record->items()->whereNotIn('id', $submittedIds ?: [0])->delete();
            if ($existingIds !== []) {
                $record->items()->whereIn('id', $submittedIds)->increment($position, 100000);
            }
            $calculatedRows = $result->raw[$config['result_rows']] ?? [];
            foreach (array_values($data['observations']) as $index => $row) {
                $item = ! empty($row['id']) ? $record->items()->findOrFail($row['id']) : $record->items()->make();
                $item->fill(Arr::only($row, $config['item_fields']) + [
                    $position => $index + 1,
                    'calculation_snapshot' => $calculatedRows[$index] ?? null,
                ]);
                $item->save();
            }

            $progress = $project->module_progress ?? [];
            $progress['material_tests'][$module] = ['status' => $result->valid ? 'completed' : 'needs_verification', 'updated_at' => now()->toIso8601String()];
            $project->forceFill(['jmd_status' => JmdStatus::MaterialTesting, 'module_progress' => $progress])->save();
            AuditService::record('jmd-material-tests', $before ? 'update' : 'create', $record->fresh(), $before);

            return $record;
        });

        return redirect()->route('jmd.material-tests.form', [$project, $module, 'test' => $record->id])
            ->with('success', 'Pengujian tersimpan dan hasil telah dihitung ulang.');
    }

    private function headerData(Project $project, array $data, array $config, array $result, bool $exists): array
    {
        $common = [
            'project_id' => $project->id,
            'jmd_project_material_id' => $data['jmd_project_material_id'] ?? null,
            'sample_number' => $data['sample_number'] ?? null,
            'tested_at' => $data['tested_at'], 'technician' => $data['technician'],
            'status' => $result['valid'] ? 'completed' : 'needs_verification',
            'result_snapshot' => $result,
            'standard_snapshot' => ($data['_standard_snapshot'] ?? ['mode' => 'legacy', 'source' => $data['standard_source']]) + Arr::only($data, $config['standard_fields']),
            'notes' => $data['notes'] ?? null, 'updated_by' => auth()->id(),
        ];
        if (! $exists) {
            $common += ['test_number' => 'JMD-'.strtoupper($config['code']).'-'.Str::ulid(), 'created_by' => auth()->id()];
        }

        $moduleData = Arr::only($data, $config['header_fields']);
        if ($config['code'] === 'SA') {
            $moduleData += [
                'maximum_size_mm' => data_get($result, 'raw.maximum_size_mm'),
                'nominal_maximum_size_mm' => data_get($result, 'raw.nominal_maximum_size_mm'),
            ];
        }

        return $common + $moduleData;
    }

    private function resolveStandardSelection(array $data, array $config): array
    {
        if (($data['value_source'] ?? 'legacy') === 'table') {
            $snapshot = app(StandardMasterService::class)->resolveValue((int) $data['standard_table_value_id'], $config['standard_keys']);
            $masterAggregate = data_get($snapshot, 'dimension_values.aggregate_type');
            if ($masterAggregate && isset($data['aggregate_type']) && $masterAggregate !== $data['aggregate_type']) {
                throw ValidationException::withMessages(['standard_table_value_id' => 'Nilai master tidak sesuai dengan jenis agregat yang dipilih.']);
            }
            $target = $config['master_targets'][$snapshot['table_key']] ?? null;
            if ($target && $snapshot['numeric_value'] !== null) {
                $data[$target] = $snapshot['numeric_value'];
            }
            $data['standard_source'] = $snapshot['source'];
            $data['_standard_snapshot'] = $snapshot;
        } elseif (($data['value_source'] ?? 'legacy') === 'manual') {
            $data['_standard_snapshot'] = [
                'mode' => 'manual', 'source' => $data['standard_source'],
                'reason' => $data['manual_standard_reason'], 'captured_at' => now()->toIso8601String(),
            ];
        }

        return $data;
    }

    public static function modules(): array
    {
        return [
            'moisture' => ['title' => 'Kadar Air Agregat', 'code' => 'KA', 'icon' => 'droplet', 'model' => MoistureTest::class, 'position' => 'observation_number', 'result_rows' => 'observations', 'header_fields' => ['aggregate_type'], 'standard_fields' => [], 'standard_keys' => [], 'master_targets' => [], 'item_fields' => ['container_mass', 'wet_container_mass', 'dry_container_mass']],
            'silt' => ['title' => 'Kadar Lumpur Agregat', 'code' => 'KL', 'icon' => 'water', 'model' => SiltTest::class, 'position' => 'observation_number', 'result_rows' => 'observations', 'header_fields' => ['aggregate_type', 'limit_percent'], 'standard_fields' => ['limit_percent'], 'standard_keys' => ['silt_limits'], 'master_targets' => ['silt_limits' => 'limit_percent'], 'item_fields' => ['container_mass', 'before_wash_container_mass', 'after_wash_container_mass']],
            'fine-specific-gravity' => ['title' => 'Berat Jenis Agregat Halus', 'code' => 'BJAH', 'icon' => 'speedometer', 'model' => FineAggregateSpecificGravityTest::class, 'position' => 'observation_number', 'result_rows' => 'observations', 'header_fields' => [], 'standard_fields' => [], 'standard_keys' => [], 'master_targets' => [], 'item_fields' => ['pycnometer_mass', 'ssd_sample_mass', 'pycnometer_sample_water_mass', 'pycnometer_water_mass', 'oven_dry_sample_mass']],
            'coarse-specific-gravity' => ['title' => 'Berat Jenis Agregat Kasar', 'code' => 'BJAK', 'icon' => 'speedometer2', 'model' => CoarseAggregateSpecificGravityTest::class, 'position' => 'observation_number', 'result_rows' => 'observations', 'header_fields' => [], 'standard_fields' => [], 'standard_keys' => [], 'master_targets' => [], 'item_fields' => ['ssd_air_mass', 'submerged_mass', 'oven_dry_mass']],
            'bulk-density' => ['title' => 'Berat Volume Material', 'code' => 'BV', 'icon' => 'boxes', 'model' => BulkDensityTest::class, 'position' => 'observation_number', 'result_rows' => 'observations', 'header_fields' => ['material_type'], 'standard_fields' => ['mass_unit'], 'standard_keys' => [], 'master_targets' => [], 'item_fields' => ['condition', 'mould_volume_cm3', 'mould_mass', 'filled_mould_mass']],
            'cement-specific-gravity' => ['title' => 'Berat Jenis Semen', 'code' => 'BJS', 'icon' => 'hexagon', 'model' => CementSpecificGravityTest::class, 'position' => 'observation_number', 'result_rows' => 'observations', 'header_fields' => [], 'standard_fields' => [], 'standard_keys' => [], 'master_targets' => [], 'item_fields' => ['bottle_kerosene_mass', 'bottle_cement_kerosene_mass', 'initial_reading_ml', 'final_reading_ml', 'test_temperature_c', 'water_density']],
            'sieve' => ['title' => 'Analisis Saringan', 'code' => 'SA', 'icon' => 'bar-chart-steps', 'model' => SieveTest::class, 'position' => 'sort_order', 'result_rows' => 'rows', 'header_fields' => ['aggregate_type', 'initial_sample_mass', 'loss_tolerance_percent', 'gradation_zone'], 'standard_fields' => ['loss_tolerance_percent', 'gradation_zone'], 'standard_keys' => ['sieve_loss_tolerance'], 'master_targets' => ['sieve_loss_tolerance' => 'loss_tolerance_percent'], 'item_fields' => ['sieve_label', 'sieve_size_mm', 'is_pan', 'retained_mass', 'lower_limit_percent', 'upper_limit_percent', 'planned_passing_percent']],
            'abrasion' => ['title' => 'Abrasi Agregat Kasar', 'code' => 'ABR', 'icon' => 'gear-wide-connected', 'model' => AbrasionTest::class, 'position' => 'observation_number', 'result_rows' => 'observations', 'header_fields' => ['inspection_gradation', 'steel_ball_count', 'revolution_count', 'limit_percent'], 'standard_fields' => ['limit_percent'], 'standard_keys' => ['abrasion_limits'], 'master_targets' => ['abrasion_limits' => 'limit_percent'], 'item_fields' => ['passing_sieve_mm', 'retained_sieve_mm', 'initial_mass', 'retained_no12_mass']],
        ];
    }
}
