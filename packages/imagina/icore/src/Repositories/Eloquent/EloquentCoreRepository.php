<?php

namespace Imagina\Icore\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Imagina\Icore\Repositories\CoreRepository;
use Imagina\Icore\Transformers\CoreResource;
use Nwidart\Modules\Facades\Module;
use Imagina\Icore\Support\FilterQueryBuilder;

/* TODO : check media event
use Modules\Ihelpers\Events\CreateMedia;
use Modules\Ihelpers\Events\UpdateMedia;

use Illuminate\Database\Eloquent\SoftDeletes;*/

/**
 * Class EloquentCrudRepository
 *
 * @package Modules\Core\Repositories\Eloquent
 */
abstract class EloquentCoreRepository extends EloquentBaseRepository implements CoreRepository
{
    /**
     * @var array
     */
    protected array $replaceFilters = [];

    /**
     * @var array
     */
    protected array $replaceSyncModelRelations = [];


    /**
     * @var Builder|null
     */
    protected ?Builder $query = null;

    /**
     * @var object|null
     */
    protected ?object $params = null;

    /**
     * @var array
     */
    protected array $with = [/*all => [] ,index => [],show => []*/];

    /**
     * @param array $extraTags
     * @return bool
     */
    public function clearCache(array $extraTags = []): bool
    {
        return true;
    }

    /**
     * @param object $params
     * @param string|int|null $criteria
     * @return Builder
     */
    public function getOrCreateQuery(object $params, string|int $criteria = null): Builder
    {
        $this->params = $params;

        if (!empty($params)) {
            $cloneParams = clone $params;
            $cloneParams->returnAsQuery = true;
        } else $cloneParams = (object)["returnAsQuery" => true];

        if (is_null($criteria))
            $this->query = $this->getItemsBy($cloneParams);
        else
            $this->query = $this->getItem($criteria, $cloneParams);

        return $this->query;
    }

    /**
     * @param Builder $query
     * @param object $params
     * @param callable|null $method
     * @return Builder
     */
    public function includeToQuery(Builder $query, object $params, ?callable $method = null): Builder
    {
        $relations = $params->include ?? [];
        $withoutDefaultInclude = $params->filter?->withoutDefaultInclude ?? false;
        //request all categories instances in the "relations" attribute in the entity model
        if (in_array('*', $relations)) $relations = $this->model->getRelations() ?? [];
        else if (!$withoutDefaultInclude) {
            $relations = array_merge($relations, ($this->with['all'] ?? [])); // Include all default relations
            if ($method == 'show') $relations = array_merge($relations, ($this->with['show'] ?? [])); // include show default relations
            if ($method == 'index') $relations = array_merge($relations, ($this->with['index'] ?? [])); // include index default relation
        }
        //Filter valid Relations if is possible
        if (method_exists($this->model, 'filterValidRelations')) {
            $relations = $this->model->filterValidRelations($relations);
        }
        //Instance relations in query
        $query->with(array_unique($relations));
        //Response
        return $query;
    }

    /**
     * @param Builder $query
     * @param object $filter
     * @param object $params
     * @return Builder
     */
    public function filterQuery(Builder $query, object $filter, object $params): Builder
    {
        return $query;
    }

    /**
     * @param Builder $query
     * @param object $order
     * @param bool $noSortOrder
     * @param string $orderByRaw
     * @return Builder
     */
    public function orderQuery(Builder $query, object $order, bool $noSortOrder, string $orderByRaw): Builder
    {
        // Use raw order if provided, stripping any potential HTML tags
        if (!empty($orderByRaw)) {
            return $query->orderByRaw(strip_tags($orderByRaw));
        }

        // Apply default sort_order ordering if available
        if (!$noSortOrder && in_array('sort_order', $this->model->getFillable())) {
            $query->orderByRaw('COALESCE(sort_order, 0) DESC');
        }

        $orderField = $order->field ?? 'created_at'; //Default field
        $orderWay = $order->way ?? 'desc'; //Default way

        // Determine if this is a translatable field
        $translatedAttributes = $this->model->translatedAttributes ?? [];

        if (in_array($orderField, $translatedAttributes)) {
            //TODO: is this working yet?
            $query->orderByTranslation($orderField, $orderWay);
        } else {
            $query->orderBy($orderField, $orderWay);
        }

        return $query;
    }

    /**
     * @return array
     */
    public function getModelRelations(): array
    {
        $modelRelations = [];

        $rawRelations = $this->model->modelRelations ?? [];

        foreach ($rawRelations as $name => $value) {
            if (is_string($value)) {
                $modelRelations[$name] = ['relation' => $value];
            } elseif (is_array($value) && isset($value['relation'])) {
                $modelRelations[$name] = $value;
            }
        }

        return $modelRelations;
    }

    /**
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function defaultSyncModelRelations(Model $model, array $data): Model
    {
        foreach ($this->getModelRelations() as $relationName => $relation) {
            if (
                in_array($relationName, $this->replaceSyncModelRelations) ||
                !array_key_exists($relationName, $data)
            ) {
                continue;
            }

            $relationInstance = $model->$relationName();
            $relationType = $relation['type'] ?? null;
            $compareKeys = $relation['compareKeys'] ?? [];

            match ($relation['relation']) {
                'hasMany' => $this->handleHasManySync($model, $relationName, $relationInstance, $data[$relationName], $relationType, $compareKeys),
                'belongsToMany' => $this->handleBelongsToManySync($model, $relationName, $relationInstance, $data[$relationName], $relationType, $compareKeys),
                default => null,
            };
        }

        return $model;
    }

    /**
     * @param Model $model
     * @param string $relationName
     * @param $relationInstance
     * @param array $items
     * @param string|null $type
     * @param array $compareKeys
     * @return void
     */
    protected function handleHasManySync(
        Model   $model,
        string  $relationName,
                $relationInstance,
        array   $items,
        ?string $type,
        array   $compareKeys
    ): void
    {
        if ($type === 'updateOrCreateMany') {
            $relatedRepositoryClass = $relationInstance->getRelated()->repository ?? null;
            $foreignKey = $relationInstance->getForeignKeyName();

            if (!$relatedRepositoryClass || !$foreignKey) return;

            $repo = app($relatedRepositoryClass);

            foreach ($items as $item) {
                if (!empty(array_diff($compareKeys, array_keys($item)))) continue;

                $compare = array_merge(
                    [$foreignKey => $model->id],
                    array_intersect_key($item, array_flip($compareKeys))
                );

                $repo->updateOrCreate($compare, $item);
            }
        } else {
            $relationInstance->forceDelete();
            $model->setRelation($relationName, $relationInstance->createMany($items));
        }
    }

    /**
     * @param Model $model
     * @param string $relationName
     * @param $relationInstance
     * @param array $items
     * @param string|null $type
     * @param array $compareKeys
     * @return void
     */
    protected function handleBelongsToManySync(
        Model   $model,
        string  $relationName,
                $relationInstance,
        array   $items,
        ?string $type,
        array   $compareKeys
    ): void
    {
        if ($type === 'updateOrCreateMany') {
            $pivotTable = $relationInstance->getTable();
            $foreignKey = $relationInstance->getRelatedPivotKeyName();
            $modelKey = $relationInstance->getForeignPivotKeyName();

            foreach ($items as $item) {
                if (!isset($item[$foreignKey]) || !empty(array_diff($compareKeys, array_keys($item)))) continue;

                $relatedId = $item[$foreignKey];
                unset($item[$foreignKey]);

                $lookup = array_merge(
                    [$modelKey => $model->id, $foreignKey => $relatedId],
                    array_intersect_key($item, array_flip($compareKeys))
                );

                DB::table($pivotTable)->updateOrInsert(
                    $lookup,
                    array_merge($item, ['updated_at' => now(), 'created_at' => now()])
                );
            }

        } else {
            $relationInstance->sync($items);
        }
        $model->setRelation($relationName, $model->$relationName);
    }

    /**
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function syncModelRelations(Model $model, array $data): Model
    {
        return $model;
    }

    /**
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        //Event creating model
        $this->dispatchesEvents(['eventName' => 'creating', 'data' => $data]);

        // allow action before create
        $this->beforeCreate($data);

        //Create model
        $model = $this->model->query()->create($data);

        // Default sync model relations
        $model = $this->defaultSyncModelRelations($model, $data);

        // Custom sync model relations
        $model = $this->syncModelRelations($model, $data);

        // allow action after creation
        $this->afterCreate($model, $data);

        //Event created model
        $this->dispatchesEvents(['eventName' => 'created', 'data' => $data, 'model' => $model]);

        //Response
        return $model;
    }

    /**
     * @param array $data
     * @return void
     */
    public function beforeCreate(array &$data): void
    {
    }

    /**
     * @param Model $model
     * @param array $data
     * @return void
     */
    public function afterCreate(Model &$model, array &$data): void
    {
    }

    /**
     * @param object|null $params
     * @return Collection|Builder
     */
    public function getItemsBy(?object $params = null): Collection|Builder
    {
        $params = $params ?? (object)[];
        $filters = $params->filter ?? (object)[];
        $differentParameters = $this->compareParameters($params);
        $this->params = $params;
        // Reuse the query if already exist
        if (empty($this->query) || $differentParameters) {
            $query = $this->model->query();
            $query = $this->includeToQuery($query, $params, "index");
            $query = $this->applyFiltersToQuery($query, $filters, $params);
            $query = $this->orderQuery(
                $query,
                $params->order ?? true,
                $filters->noSortOrder ?? false,
                $params->orderByRaw ?? null
            );
            $this->query = $query;
        } else {
            $query = $this->query;
        }

        //Response as query
        if (isset($params->returnAsQuery) && $params->returnAsQuery) return $query;

        //Get response
        $response = !empty($params->page)
            ? $query->paginate($params->take ?? 12, ['*'], null, $params->page)
            : ($params->take ? $query->take($params->take)->get() : $query->get());

        //Event return model
        $this->dispatchesEvents(['eventName' => 'retrievedIndex', 'data' => [
            "requestParams" => $params,
            "response" => $response,
        ]]);

        //Response
        return $response;
    }

    /**
     * @param string|int $criteria
     * @param object|null $params
     * @return Model|Builder|null
     */
    public function getItem(string|int $criteria, ?object $params = null): Model|Builder|null
    {
        $params = $params ?? (object)[];
        $filters = $params->filter ?? (object)[];
        $differentParameters = $this->compareParameters($params);

        if (empty($this->query) || $differentParameters) {
            $query = $this->model->query();
            $query = $this->includeToQuery($query, $params, "show");

            $criteriaFields = (array)($filters->field ?? ['id']);
            $translatableAttrs = $this->model->translatedAttributes ?? [];
            $locale = $filters->locale ?? app()->getLocale();

            $translatableFields = array_intersect($criteriaFields, $translatableAttrs);
            $modelFields = array_diff($criteriaFields, $translatableFields);

            if (!empty($translatableFields)) {
                $query->whereHas('translations', function ($q) use ($locale, $criteria, $translatableFields) {
                    $q->where('locale', $locale)->where(function ($subQ) use ($criteria, $translatableFields) {
                        //TODO: Does it working with reduce?
                        collect($translatableFields)->reduce(function ($carry, $field) use ($subQ, $criteria) {
                            return $subQ->orWhere($field, $criteria);
                        });
                    });
                });
            }

            if (!empty($modelFields)) {
                $table = $this->model->getTable();
                $query->where(function ($q) use ($modelFields, $criteria, $table) {
                    //TODO: Does it working with reduce?
                    collect($modelFields)->reduce(function ($carry, $field) use ($q, $criteria, $table) {
                        return $q->orWhere("{$table}.{$field}", $criteria);
                    });
                });
            }

            $query = $this->applyFiltersToQuery($query, $filters, $params);
            $this->query = $query;
        } else {
            $query = $this->query;
        }

        if (!empty($params->returnAsQuery)) return $query;

        $response = $query->first();

        $this->dispatchesEvents([
            'eventName' => 'retrievedShow',
            'data' => [
                "requestParams" => $params,
                "response" => $response,
                "criteria" => $criteria
            ]
        ]);

        return $response;
    }


    protected function applyFiltersToQuery(Builder $query, object $filters, object $params): Builder
    {
        $modelRelations = $this->getModelRelations();
        $modelFillable = array_merge($this->model->getFillable(), ['id', 'created_at', 'updated_at', 'created_by', 'updated_by']);
        $translatableAttributes = $this->model->translatedAttributes ?? [];

        foreach ($filters as $filterName => $filterValue) {
            $filterNameSnake = camelToSnake($filterName);
            if (array_key_exists($filterName, $this->replaceFilters)) continue;

            if (array_key_exists($filterNameSnake, $modelFillable)) {
                if ($filterNameSnake == "id") $filterValue = (object)["where" => 'in', "value" => (array)$filterValue];
                if (is_array($filterValue) && !isset($filterValue['where'])) $filterValue = (object)["where" => 'in', "value" => $filterValue];
                if ($filterNameSnake == "parent_id" && !$filterValue) $filterValue = (object)["where" => 'null'];
                $query = FilterQueryBuilder::apply($query, $filterValue, $filterNameSnake);
            }

            if (array_key_exists($filterNameSnake, $translatableAttributes)) {
                $query->whereHas('translations', function ($q) use ($filters, $filterNameSnake, $filterValue) {
                    $q->where('locale', $filters->locale ?? app()->getLocale());
                    ilterQueryBuilder::apply($q, $filterValue, $filterNameSnake);
                });
            }

            $relationPath = explode('.', $filterName);
            if (array_key_exists($relationPath[0], $modelRelations)) {
                $query = ilterQueryBuilder::apply($query, (object)[
                    'where' => $modelRelations[$relationPath[0]]['relation'],
                    'value' => $filterValue
                ], $relationPath);
            }
        }

        if (!empty($filters->date)) {
            $date = $filters->date;
            $field = $date->field ?? 'created_at';
            if (!empty($date->from)) $query->whereDate($field, '>=', $date->from);
            if (!empty($date->to)) $query->whereDate($field, '<=', $date->to);
        }

        if (!empty($filters->withTrashed)) $query->withTrashed();
        if (!empty($filters->onlyTrashed)) $query->onlyTrashed();
        if (!empty($filters->withoutTenancy)) $query->withoutTenancy();

        return $this->filterQuery($query, $filters, $params);
    }

    /**
     * @param Collection $models
     * @param object $params
     * @return Collection
     */
    public function getItemsByTransformed(Collection $models, object $params): Collection
    {
        return json_decode(json_encode(CoreResource::transformData($models)));
    }

    /**
     * @param string|int $criteria
     * @param array $data
     * @param object|null $params
     * @return Model|null
     */
    public function updateBy(string|int $criteria, array $data, ?object $params = null): ?Model
    {
        //Event updating model
        $this->dispatchesEvents(['eventName' => 'updating', 'data' => $data, 'criteria' => $criteria]);

        //Instance Query
        $query = $this->model->query();

        //Check field name to criteria
        if (isset($params->filter->field)) $field = $params->filter->field;

        //get model and update
        $model = $query->where($field ?? 'id', $criteria)->first();
        if (isset($model)) {
            $data['id'] = $model->id;
            $this->beforeUpdate($data);
            // Update attributes
            $nonColumnAttributes = ['medias_single', 'medias_multi'];
            $fillableData = collect($data)->except($nonColumnAttributes)->toArray();
            $model->fill($fillableData);
            // Save model if dirty
            if ($model->isDirty()) $model->save();
            // Check for dirty translations and fire the touch to save the model timestamp
            if (method_exists($model, 'translations')) {
                foreach ($model->translations as $translation) {
                    if ($translation->isDirty()) {
                        $model->touch();
                        break;
                    }
                }
            }
            // Default Sync model relations
            $model = $this->defaultSyncModelRelations($model, $data);
            // Custom Sync model relations
            $model = $this->syncModelRelations($model, $data);
            // Call function after update it, and take all changes from $data and $model
            $this->afterUpdate($model, $data);
            //Event updated model
            $this->dispatchesEvents([
                'eventName' => 'updated',
                'data' => $data,
                'criteria' => $criteria,
                'model' => $model
            ]);
        }

        //Response
        return $model;
    }

    /**
     * @param $data
     * @return void
     */
    public function beforeUpdate(&$data): void
    {
    }

    /**
     * @param $model
     * @param $data
     * @return void
     */
    public function afterUpdate(&$model, &$data): void
    {
    }

    /**
     * @param array $data
     * @param object|null $params
     * @return Collection
     */
    public function bulkOrder(array $data, ?object $params = null): Collection
    {
        //Instance the orderField
        $orderField = $params->filter->field ?? 'position';
        //loop through data to update the position according to index data
        foreach ($data as $key => $item) {
            $this->model->query()->find($item['id'])->update([$orderField => ++$key]);
        }
        //Response
        return $this->model->query()->whereIn('id', array_column($data, "id"))->get();
    }

    /**
     * @param array $data
     * @param object|null $params
     * @return array
     */
    public function bulkUpdate(array $data, ?object $params = null): array
    {
        //Instance the orderField
        $fieldName = $params->filter->field ?? 'id';
        $updated = [];
        //loop through data to update the position according to index data
        foreach ($data as $item) {
            $updated[] = $this->updateBy($item[$fieldName], $item, $params);
        }
        //Response
        return $updated;
    }

    /**
     * @param array $data
     * @return array
     */
    public function bulkCreate(array $data): array
    {
        $created = [];
        //loop through data to create the position according to index data
        foreach ($data as $item) {
            $created[] = $this->create($item);
        }
        //Response
        return $created;
    }

    /**
     * @param string|int $criteria
     * @param object|null $params
     * @return bool
     */
    public function deleteBy(string|int $criteria, ?object $params = null): bool
    {
        //Instance Query
        $query = $this->model->query();

        //Check field name to criteria
        if (isset($params->filter->field)) $field = $params->filter->field;

        //Include trashed records | SoftDeletes
        if ($this->hasSoftDeletes()) $query->withTrashed();

        //get model
        $model = $query->where($field ?? 'id', $criteria)->first();

        //Event deleting model
        $this->dispatchesEvents(['eventName' => 'deleting', 'criteria' => $criteria, 'model' => $model]);

        //Delete Model
        if ($model) {
            if (isset($params->filter->forceDelete) && $this->hasSoftDeletes()) $model->forceDelete();
            else $model->delete();
        }

        //Event deleted model
        $this->dispatchesEvents(['eventName' => 'deleted', 'criteria' => $criteria]);

        //Response
        return $model;
    }

    /**
     * @param string|int $criteria
     * @param object|null $params
     * @return Model
     */
    public function restoreBy(string|int $criteria, ?object $params = null): Model
    {
        //Instance Query
        $query = $this->model->query();

        //Check field name to criteria
        if (isset($params->filter->field)) $field = $params->filter->field;

        //get model | SoftDeletes
        $model = $query->where($field ?? 'id', $criteria)->withTrashed()->first();

        //Delete Model
        if ($model) $model->restore();

        //Response
        return $model;
    }

    /**
     * @param array $params
     * @return void
     */
    public function dispatchesEvents(array $params): void
    {
        $eventName = $params['eventName'];
        $data = $params['data'] ?? [];
        $criteria = $params['criteria'] ?? null;
        $model = $params['model'] ?? null;

        // Define model method callbacks for specific events
        $modelEventCallbacks = [
            'retrievedIndex' => ['object' => $this->model, 'method' => 'retrievedIndexCrudModel', 'args' => ['data' => $data]],
            'retrievedShow' => ['object' => $this->model, 'method' => 'retrievedShowCrudModel', 'args' => ['data' => $data]],
            'creating' => ['object' => $this->model, 'method' => 'creatingCrudModel', 'args' => ['data' => $data]],
            'created' => ['object' => $model, 'method' => 'createdCrudModel', 'args' => ['data' => $data]],
            'updating' => ['object' => $this->model, 'method' => 'updatingCrudModel', 'args' => ['data' => $data, 'params' => $params, 'criteria' => $criteria]],
            'updated' => ['object' => $model, 'method' => 'updatedCrudModel', 'args' => ['data' => $data, 'params' => $params, 'criteria' => $criteria]],
            'deleting' => ['object' => $model, 'method' => 'deletingCrudModel', 'args' => ['params' => $params, 'criteria' => $criteria]],
        ];

        // Execute the matching model method if it exists
        if (isset($modelEventCallbacks[$eventName])) {
            $callback = $modelEventCallbacks[$eventName];
            if (method_exists($callback['object'], $callback['method'])) {
                $callback['object']->{$callback['method']}($callback['args']);
            }
        }

        // Dispatch custom model-defined events (e.g., from config)
        $dispatchesEvents = $this->model->dispatchesEventsWithBindings ?? [];
        if (!empty($dispatchesEvents[$eventName])) {
            foreach ($dispatchesEvents[$eventName] as $event) {
                $moduleName = explode("\\", $event['path'])[1] ?? null;
                if ($moduleName && Module::isEnabled($moduleName)) {
                    event(new $event['path']([
                        'data' => $data,
                        'extraData' => $event['extraData'] ?? [],
                        'criteria' => $criteria,
                        'model' => $model
                    ]));
                }
            }
        }
    }


    /**
     * @param object $params
     * @return bool
     */
    public function compareParameters(object $params): bool
    {
        $newParams = json_encode($params);
        $queryParams = json_encode($this->params);
        return $newParams != $queryParams;
    }

    /**
     * @return bool
     */
    private function hasSoftDeletes(): bool
    {
        return false;
        //return in_array(SoftDeletes::class, class_uses_recursive($this->model));
    }

    /**
     * @param array $validation
     * @param array $data
     * @return Model
     */
    public function updateOrCreate(array $validation, array $data): Model
    {
        //Search the record
        $model = $this->getItemsBy((object)['filter' => (object)$validation])->first();
        $modelData = array_merge($validation, $data);
        //update Or Create the record
        if ($model) $this->updateBy($model->id, $modelData);
        else $this->create($modelData);
        //Response
        return $model;
    }

    /**
     * @param object|null $params
     * @return Collection
     */
    public function getDashboard(?object $params): Collection
    {
        return new Collection();
    }
}
