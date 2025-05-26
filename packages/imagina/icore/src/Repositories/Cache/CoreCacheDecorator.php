<?php

namespace Imagina\Icore\Repositories\Cache;

use Illuminate\Cache\Repository;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\Eloquent\Builder;
use Imagina\Icore\Repositories\BaseRepository;
use Imagina\Icore\Repositories\CoreRepository;

abstract class CoreCacheDecorator implements BaseRepository, CoreRepository
{
    protected $repository;

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @var int cache timing in seconds
     */
    protected $cacheTime;

    /**
     * @var string The entity name
     */
    protected $entityName;

    /**
     * @var string The application locale
     */
    protected $locale;

    /**
     * @var string|array The cache tags
     */
    protected $tags;

    public function __construct()
    {
        $this->cache = app(Repository::class);
        $this->locale = app()->getLocale();
        $this->cacheTime = app(ConfigRepository::class)->get('cache.time', 2592000);
    }

    public function find($id)
    {
        return $this->remember(function () use ($id) {
            return $this->repository->find($id);
        });
    }

    public function all()
    {
        return $this->remember(function () {
            return $this->repository->all();
        });
    }

    public function allWithBuilder(): Builder
    {
        return $this->remember(function () {
            return $this->repository->allWithBuilder();
        });
    }

    public function paginate($perPage = 15)
    {
        return $this->remember(function () use ($perPage) {
            return $this->repository->paginate($perPage);
        });
    }

    public function allTranslatedIn($lang)
    {
        return $this->remember(function () use ($lang) {
            return $this->repository->allTranslatedIn($lang);
        });
    }

    public function findBySlug($slug)
    {
        return $this->remember(function () use ($slug) {
            return $this->repository->findBySlug($slug);
        });
    }

    public function create($data)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->create($data);
    }

    public function update($model, $data)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->update($model, $data);
    }

    public function destroy($model)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->destroy($model);
    }

    public function findByAttributes(array $attributes)
    {
        return $this->remember(function () use ($attributes) {
            return $this->repository->findByAttributes($attributes);
        });
    }

    public function getByAttributes(array $attributes, $orderBy = null, $sortOrder = 'asc')
    {
        return $this->remember(function () use ($attributes, $orderBy, $sortOrder) {
            return $this->repository->getByAttributes($attributes, $orderBy, $sortOrder);
        });
    }

    public function findByMany(array $ids)
    {
        return $this->remember(function () use ($ids) {
            return $this->repository->findByMany($ids);
        });
    }

    public function clearCache($tags = null)
    {
        $store = $this->cache;

        if (method_exists($this->cache->getStore(), 'tags')) {
            if (!empty($tags)) {
                !is_array($tags) ? $tags = [$tags] : false;
            }
            $tags = array_merge($tags ?? [], [$this->entityName]);
            $store = $store->tags($tags);
        }

        return $store->flush();
    }

    protected function remember(\Closure $callback, $key = null, $time = null)
    {
        $cacheKey = $this->makeCacheKey($key);

        $store = $this->cache;

        if (method_exists($this->cache->getStore(), 'tags')) {
            $store = $store->tags([$this->entityName, 'global']);
        }

        $cacheTime = $time ?? $this->cacheTime;

        return $store->remember($cacheKey, $cacheTime, $callback);
    }

    private function makeCacheKey($key = null): string
    {
        if ($key !== null) {
            return $key;
        }

        $cacheKey = $this->getBaseKey();

        $backtrace = debug_backtrace()[2];

        return sprintf("$cacheKey %s %s", $backtrace['function'], \serialize($backtrace['args']));
    }

    protected function getBaseKey(): string
    {
        return sprintf(
            'asgardcms -locale:%s -entity:%s',
            $this->locale,
            $this->entityName
        );
    }

    public function whereIn(string $field, array $values): Builder
    {
        return $this->repository->whereIn($field, $values);
    }

    public function where(string $field, $value, string $operator = null)
    {
        return $this->remember(function () use ($field, $value, $operator) {
            return $this->repository->where($field, $value, $operator);
        });
    }

    public function with($relationships)
    {
        return $this->remember(function () use ($relationships) {
            return $this->repository->with($relationships);
        });
    }

    public function getItemsBy($params)
    {
        $query = $this->repository->getOrCreateQuery($params);

        return $this->remember(function () use ($params) {
            return $this->repository->getItemsBy($params);
        }, $this->createKey($query, $params));
    }

    public function getItem($criteria, $params = false)
    {
        $query = $this->repository->getOrCreateQuery($params, $criteria);

        return $this->remember(function () use ($criteria, $params) {
            return $this->repository->getItem($criteria, $params);
        }, $this->createKey($query, $params));
    }

    public function getItemsByTransformed($models, $params)
    {
        $params->transformed = true;
        $query = $this->repository->getOrCreateQuery($params);

        return $this->remember(function () use ($models, $params) {
            return $this->repository->getItemsByTransformed($models, $params);
        }, $this->createKey($query, $params));
    }

    public function updateBy($criteria, $data, $params = false)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->updateBy($criteria, $data, $params);
    }

    public function deleteBy($criteria, $params = false)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->deleteBy($criteria, $params);
    }

    public function restoreBy($criteria, $params = false)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->restoreBy($criteria, $params);
    }

    public function bulkOrder($data, $params = false)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->bulkOrder($data, $params);
    }

    public function bulkUpdate($data, $params = false)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->bulkUpdate($data, $params);
    }

    public function bulkCreate($data)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->bulkCreate($data);
    }

    public function getDashboard($params)
    {
        return $this->remember(function () use ($params) {
            return $this->repository->getDashboard($params);
        }, $this->createKey(null, $params));
    }

    public function createKey($query, $params)
    {
        $cacheKey = str_replace(["\"", "`", "{", "}"], "", (($query ? $query->toSql() ?? "" : "") .
            (\serialize($query ? $query->getBindings() ?? "" : "")) .
            (!empty($params->filter) ? \serialize($params->filter) : "") .
            (!empty($params->order) ? \serialize($params->order) : "") .
            (!empty($params->include) ? \serialize($params->include) : "") .
            (!empty($params->page) ? \serialize($params->page) : "") .
            (!empty($params->take) ? \serialize($params->take) : "") .
            ($params->transformed ?? false)
        ));

        return hash('sha256', $cacheKey);
    }

    public function getTags(): array
    {
        return array_merge(is_null($this->tags) ? [] : (is_array($this->tags) ? $this->tags : [$this->tags]), [$this->entityName]);
    }

    public function updateOrCreate($validationData, $data)
    {
        $this->cache->tags($this->getTags())->flush();
        return $this->repository->updateOrCreate($validationData, $data);
    }
}
