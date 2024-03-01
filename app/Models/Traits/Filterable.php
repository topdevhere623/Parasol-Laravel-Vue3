<?php

namespace App\Models\Traits;

use App\Models\QueryFilters\QueryFilter;

trait Filterable
{
    /**
     * @var QueryFilter|null
     */
    protected $filters;

    public function scopeFilter($query, QueryFilter $filters)
    {
        $this->filters = $filters;

        return $filters->apply($query);
    }

    /**
     * Добавляет к пагинации параметры фильтров
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $perPage
     * @param array $columns
     * @param string $pageName
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function scopePaginateFilter($query, $perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $perPage = $perPage ?: $query->getModel()->getPerPage();

        $paginator = $query->paginate($perPage, $columns, $pageName, $page);

        if ($this->filters !== null) {
            $paginator->appends($this->filters->filters());
        }

        return $paginator;
    }
}
