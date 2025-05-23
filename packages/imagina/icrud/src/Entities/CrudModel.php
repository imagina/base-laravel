<?php

namespace Imagina\Icrud\Entities;

use Illuminate\Database\Eloquent\Model;

/*use Modules\Core\Support\Traits\AuditTrait;
use Modules\Isite\Traits\RevisionableTrait;
use Imagina\Icrud\Traits\SingleFlaggable;
use Imagina\Icrud\Traits\HasUniqueFields;
use Imagina\Icrud\Traits\HasCacheClearable;*/
use Imagina\Icrud\Traits\hasEventsWithBindings;
use Imagina\Icrud\Traits\HasOptionalTraits;
use Imagina\Icrud\Repositories\Eloquent\CustomBuilder;

class CrudModel extends Model
{
    use HasOptionalTraits, hasEventsWithBindings;/*AuditTrait, hasEventsWithBindings, RevisionableTrait, SingleFlaggable, HasUniqueFields,
       HasCacheClearable,*/

    function getFillables()
    {
        return $this->fillable;
    }

    /**
     * Use the custom query builder.
     *
     */
    public function newEloquentBuilder($query)
    {
        return new CustomBuilder($query);
    }

    /**
     * Filter valid relations for eager loading.
     *
     */
    public function filterValidRelations($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        return array_filter($relations, function ($relation) use ($relations) {
            if (is_string($relation)) $relation = explode('.', $relation)[0];
            return !is_string($relation) || method_exists($this, $relation) ||
                in_array($relation, static::$optionalTraitsRelations); //This depent of HasOptionalTraits trait
        });
    }
}
