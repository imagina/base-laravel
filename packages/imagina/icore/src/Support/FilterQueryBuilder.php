<?php

namespace Imagina\Icore\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FilterQueryBuilder
{
    public static function apply(Builder $query, object $filterData, string|array $fieldName, ?Model $model = null): Builder
    {
        $filterWhere = $filterData->where ?? null;
        $filterOperator = $filterData->operator ?? '=';
        $filterValue = $filterData->value ?? $filterData;

        return match ($filterWhere) {
            'in' => $query->whereIn($fieldName, (array)$filterValue),
            'notIn' => $query->whereNotIn($fieldName, (array)$filterValue),
            'between' => $query->whereBetween($fieldName, $filterValue),
            'notBetween' => $query->whereNotBetween($fieldName, $filterValue),
            'null' => $query->whereNull($fieldName),
            'notNull' => $query->whereNotNull($fieldName),
            'date' => $query->whereDate($fieldName, $filterOperator, $filterValue),
            'year' => $query->whereYear($fieldName, $filterOperator, $filterValue),
            'month' => $query->whereMonth($fieldName, $filterOperator, $filterValue),
            'day' => $query->whereDay($fieldName, $filterOperator, $filterValue),
            'time' => $query->whereTime($fieldName, $filterOperator, $filterValue),
            'column' => $query->whereColumn($fieldName, $filterOperator, $filterValue),
            'orWhere' => $query->orWhere($fieldName, $filterOperator, $filterValue),
            'belongsToMany' => self::applyBelongsToMany($query, $fieldName, $filterValue, $model),
            'hasMany' => self::applyHasMany($query, $fieldName, $filterValue),
            default => $query->where($fieldName, $filterOperator, $filterValue),
        };
    }

    protected static function applyBelongsToMany(Builder $query, array $fieldName, array $filterValue, ?Model $model): Builder
    {
        if (count($filterValue) && $model) {
            $relationName = $fieldName[0];
            $foreignKey = $model->$relationName()->getRelatedPivotKeyName();
            $query->whereHas($relationName, function ($q) use ($foreignKey, $filterValue) {
                $q->whereIn($foreignKey, $filterValue);
            });
        }
        return $query;
    }

    protected static function applyHasMany(Builder $query, array $fieldName, array $filterValue): Builder
    {
        if (count($filterValue)) {
            $relatedFieldName = camelToSnake($fieldName[1] ?? 'id');
            $query->whereHas($fieldName[0], function ($q) use ($relatedFieldName, $filterValue) {
                $q->whereIn($relatedFieldName, $filterValue);
            });
        }
        return $query;
    }
}
