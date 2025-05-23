<?php

namespace Imagina\Iblog\Entities;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    use Sluggable;

    public $timestamps = false;

    protected $table = 'iblog__post_translations';

    protected $fillable = [
        'title',
        'description',
        'slug',
        'summary',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'translatable_options',
        'status',
    ];

    protected $casts = [
        'translatable_options' => 'array',
        'meta_keywords' => 'array',
    ];

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable():array
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }

    public function getTranslatableOptionAttribute($value)
    {
        $options = json_decode($value);

        return $options;
    }
}
