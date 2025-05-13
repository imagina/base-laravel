<?php

namespace Imagina\Iblog\Entities;

use Astrotomic\Translatable\Translatable;
use Imagina\Icrud\Entities\CrudModel;

class Post extends CrudModel
{
  use Translatable;

  protected static $entityNamespace = 'asgardcms/post';

  public $transformer = 'Imagina\Icrud\Transformers\PostTransformer';
  public $entity = 'Imagina\Icrud\Entities\Post';
  public $repository = 'Imagina\Icrud\Repositories\PostRepository';
  public $requestValidation = [
    'create' => 'Imagina\Icrud\Http\Requests\CreatePostRequest',
    'update' => 'Imagina\Icrud\Http\Requests\UpdatePostRequest',
  ];

  protected $table = 'iblog__posts';

  protected $fillable = [
    'options',
    'category_id',
    'user_id',
    'featured',
    'sort_order',
    'external_id',
    'created_at',
    'date_available'
  ];
  public $translatedAttributes = [
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
  public $uniqueFields = ['slug'];

  protected $dates = [
    'date_available'
  ];

  protected $with = [
    'tags','files', 'fields'
  ];

  protected $casts = [
    'options' => 'array'
  ];

  protected $revisionEnabled = true;
  protected $revisionCleanup = true;
  protected $historyLimit = 100;
  protected $revisionCreationsEnabled = true;

  public function categories()
  {
    return $this->belongsToMany(Category::class, 'iblog__post__category');
  }

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  public function user()
  {
    $driver = config('asgard.user.config.driver');

    return $this->belongsTo("Modules\\User\\Entities\\{$driver}\\User");
  }

  public function getOptionsAttribute($value)
  {
    $response = json_decode($value);

    if(is_string($response)) {
      $response = json_decode($response);
    }

    return $response;
  }

  /**
   *  main image url used to meta
   */
  public function getMainImageAttribute()
  {

    //Default
    $image = [
      'mimeType' => 'image/jpeg',
      'path' => url('modules/iblog/img/post/default.jpg')
    ];

    //Get and Set mainimage
    $mainimageFile = null;
    if($this->relationLoaded('files')) {
      foreach ($this->files as $file) {
        if ($file->pivot->zone == "mainimage") $mainimageFile = $file;
      }
    }

    if(!is_null($mainimageFile)){
      $image = [
        'mimeType' => $mainimageFile->mimetype,
        'path' => $mainimageFile->path_string
      ];
    }

    return json_decode(json_encode($image));

  }


  /**
   * URL post
   * @return string
   */
  public function getUrlAttribute($locale = null)
  {


    if (empty($this->slug)) {
      $post = $this->getTranslation(\LaravelLocalization::getDefaultLocale());
      $this->slug = $post->slug ?? "";
    }

    $currentLocale = $locale ?? locale();
    if (!is_null($locale)) {
      $this->slug = $this->getTranslation($currentLocale)->slug;
      $this->category = $this->category->getTranslation($currentLocale);
    }

    if (empty($this->slug)) return "";

    $currentDomain = !empty($this->organization_id) ? tenant()->domain ?? tenancy()->find($this->organization_id)->domain :
      parse_url(config('app.url'), PHP_URL_HOST);

    if (config("app.url") != $currentDomain) {
      $savedDomain = config("app.url");
      config(["app.url" => "https://" . $currentDomain]);
    }

    if (isset($this->options->urlCoder) && !empty($this->options->urlCoder) && $this->options->urlCoder == "onlyPost") {

      $url = \LaravelLocalization::localizeUrl('/' . $this->slug, $currentLocale);

    } else {
      if (empty($this->category->slug)) $url = "";
      else $url = \LaravelLocalization::localizeUrl('/' . $this->category->slug . '/' . $this->slug, $currentLocale);
    }

    if (isset($savedDomain) && !empty($savedDomain)) config(["app.url" => $savedDomain]);

    return $url;

  }

  /**
   * Magic Method modification to allow dynamic relations to other entities.
   * @return string
   * @var $destination_path
   * @var $value
   */
  public function __call($method, $parameters)
  {
    #i: Convert array to dot notation
    $config = implode('.', ['asgard.iblog.config.relations.post', $method]);

    #i: Relation method resolver
    if (config()->has($config)) {
      $function = config()->get($config);

      return $function($this);
    }

    #i: No relation found, return the call to parent (Eloquent) to handle it.
    return parent::__call($method, $parameters);
  }

  public function getCacheClearableData()
  {
    $baseUrls = [config("app.url")];

    if($this->category) $baseUrls[] = $this->category->url;

    if (!$this->wasRecentlyCreated) {
      $baseUrls[] = $this->url;
    }
    $urls = ['urls' => $baseUrls];

    return $urls;
  }
}
