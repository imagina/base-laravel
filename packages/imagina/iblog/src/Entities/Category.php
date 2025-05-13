<?php

namespace Imagina\Iblog\Entities;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Laracasts\Presenter\PresentableTrait;
use Kalnoy\Nestedset\NodeTrait;
use Illuminate\Support\Str;

use Imagina\Icrud\Entities\CrudModel;

class Category extends CrudModel
{
    use Translatable, NodeTrait;

    public $transformer = 'Imagina\Icrud\Transformers\CategoryTransformer';
    public $entity = 'Imagina\Icrud\Entities\Category';
    public $repository = 'Imagina\Icrud\Repositories\CategoryRepository';
    public $requestValidation = [
        'create' => 'Imagina\Icrud\Http\Requests\CreateCategoryRequest',
        'update' => 'Imagina\Icrud\Http\Requests\UpdateCategoryRequest',
    ];
    protected $table = 'iblog__categories';

    protected $fillable = [
        'parent_id',
        'show_menu',
        'featured',
        'internal',
        'sort_order',
        'external_id',
        'options'
    ];

    public $translatedAttributes = ['title', 'status', 'description', 'slug', 'meta_title', 'meta_description', 'meta_keywords', 'translatable_options'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array'
    ];

    protected $with = [    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function parent()
    {
        return $this->belongsTo('Imagina\Icrud\Entities\Category', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Imagina\Icrud\Entities\Category', 'parent_id');
    }

    public function posts()
    {
        return $this->belongsToMany('Imagina\Icrud\Entities\Post', 'iblog__post__category')->as('posts')->with('category');
    }

    public function getOptionsAttribute($value)
    {
        $response = json_decode($value);

        if (is_string($response)) {
            $response = json_decode($response);
        }

        return $response;
    }


    public function getMainImageAttribute()
    {

        //Default
        $image = [
            'mimeType' => 'image/jpeg',
            'path' => url('modules/iblog/img/post/default.jpg')
        ];

        $mainimageFile = null;
        if ($this->relationLoaded('files')) {
            foreach ($this->files as $file) {
                if ($file->pivot->zone == "mainimage") $mainimageFile = $file;
            }
        }


        if (!is_null($mainimageFile)) {
            $image = [
                'mimeType' => $mainimageFile->mimetype,
                'path' => $mainimageFile->path_string
            ];
        }

        return json_decode(json_encode($image));

    }


    public function getUrlAttribute($locale = null)
    {
        $url = "";

        if ($this->internal) return "";
        if (empty($this->slug)) {

            $category = $this->getTranslation(\LaravelLocalization::getDefaultLocale());
            $this->slug = $category->slug ?? '';
        }

        $currentLocale = $locale ?? locale();
        if (!is_null($currentLocale)) {
            $this->slug = $this->getTranslation($currentLocale)->slug;
        }

        if (empty($this->slug)) return "";

        $currentDomain = !empty($this->organization_id) ? tenant()->domain ?? tenancy()->find($this->organization_id)->domain :
            parse_url(config('app.url'), PHP_URL_HOST);

        if (config("app.url") != $currentDomain) {
            $savedDomain = config("app.url");
            config(["app.url" => "https://" . $currentDomain]);
        }
        $url = \LaravelLocalization::localizeUrl('/' . $this->slug, $currentLocale);

        if (isset($savedDomain) && !empty($savedDomain)) config(["app.url" => $savedDomain]);


        return $url;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */
    public function scopeFirstLevelItems($query)
    {
        return $query->where('depth', '1')
            ->orWhere('depth', null)
            ->orderBy('lft', 'ASC');
    }

    public function __call($method, $parameters)
    {
        #i: Convert array to dot notation
        $config = implode('.', ['asgard.iblog.config.relations.category', $method]);

        #i: Relation method resolver
        if (config()->has($config)) {
            $function = config()->get($config);
            $bound = $function->bindTo($this);

            return $bound();
        }

        #i: No relation found, return the call to parent (Eloquent) to handle it.
        return parent::__call($method, $parameters);
    }

    public function getLftName()
    {
        return 'lft';
    }

    public function getRgtName()
    {
        return 'rgt';
    }

    public function getDepthName()
    {
        return 'depth';
    }

    public function getParentIdName()
    {
        return 'parent_id';
    }

    public function getCacheClearableData()
    {
        $baseUrls = [config("app.url")];

        if (!$this->wasRecentlyCreated) {
            $baseUrls[] = $this->url;
        }
        $urls = ['urls' => $baseUrls];

        return $urls;
    }
}
