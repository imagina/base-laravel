<?php

namespace Modules\Iuser\Models;

use Astrotomic\Translatable\Translatable;
use Imagina\Icore\Models\CoreModel;

class Role extends CoreModel
{
    use Translatable;

    protected $table = 'iuser__roles';
    public $transformer = 'Modules\Iuser\Transformers\RoleTransformer';
    public $repository = 'Modules\Iuser\Repositories\RoleRepository';
    public $requestValidation = [
        'create' => 'Modules\Iuser\Http\Requests\CreateRoleRequest',
        'update' => 'Modules\Iuser\Http\Requests\UpdateRoleRequest',
    ];
    //Instance external/internal events to dispatch with extraData
    public $dispatchesEventsWithBindings = [
        //eg. ['path' => 'path/module/event', 'extraData' => [/*...optional*/]]
        'created' => [],
        'creating' => [],
        'updated' => [],
        'updating' => [],
        'deleting' => [],
        'deleted' => []
    ];
    public $translatedAttributes = [
        'title'
    ];
    protected $fillable = [
        'slug',
        'name',
        'permissions',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class,'iuser__role_user');
    }

}
