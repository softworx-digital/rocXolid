<?php

namespace Softworx\RocXolid\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
// rocXolid contracts
use Softworx\RocXolid\Repositories\Contracts\Repository as RepositoryContract;
// rocXolid repository traits
use Softworx\RocXolid\Repositories\Traits\Orderable;
use Softworx\RocXolid\Repositories\Traits\Filterable;
use Softworx\RocXolid\Repositories\Traits\Paginationable;

/**
 * Repository is responsible for retrieving model data upon ordering and filters.
 *
 * @author softworx <hello@softworx.digital>
 * @package Softworx\RocXolid
 * @version 1.0.0
 */
class Repository implements RepositoryContract
{
    use Scopeable;
    use Orderable;
    use Filterable;
    use Paginationable;

    /**
     * Model reference to work with.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $query_model;

    /**
     * {@inheritDoc}
     */
    public function init(string $model_type): RepositoryContract
    {
        $this->query_model = app($model_type);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this
            ->getCollectionQuery()
            ->get($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this
            ->getCollectionQuery()
            ->count();
    }

    /**
     * {@inheritDoc}
     */
    public function find($key, array $columns = ['*']): ?Model
    {
        return $this->findBy($this->getModel()->getKeyName(), $key, $columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(string $column, $value, array $columns = ['*']): ?Model
    {
        return $this
            ->getQuery()
            ->where($column, '=', $value)
            ->first($columns);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail($key, array $columns = ['*']): Model
    {
        return $this
            ->getQuery()
            ->findOrFail($key, $columns);
    }

    /**
     * Initialize model query for retrieving single model data.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getQuery(): Builder
    {
        $query = $this->query_model->query();

        $this
            ->applyScopes($query)
            ->applyIntenalFilters($query);

        return $query;
    }

    /**
     * Initialize model query for retrieving collection of data.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getCollectionQuery(): Builder
    {
        $query = $this->initQuery();

        $this
            ->applyOrder($query)
            ->applyFilters($query);

        return $query;
    }

    /**
     * Apply internal repository filters to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder
     * @return \Softworx\RocXolid\Repositories\Contracts\Repository
     */
    protected function applyIntenalFilters(Builder &$query): RepositoryContract
    {
        return $this;
    }
}
