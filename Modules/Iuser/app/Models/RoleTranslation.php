<?php

namespace Modules\Iuser\Models;

use Illuminate\Database\Eloquent\Model;

class RoleTranslation extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'title'
    ];
    protected $table = 'iuser__role_translations';
}
