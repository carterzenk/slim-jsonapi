<?php

namespace CarterZenk\JsonApi\Controller;

use CarterZenk\JsonApi\Model\Paginator;
use Illuminate\Database\Eloquent\Builder;
use WoohooLabs\Yin\JsonApi\Request\Pagination\PageBasedPagination;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

interface FetchingBuilderInterface
{
    /**
     * @param Builder $builder
     * @param RequestInterface $request
     * @return mixed
     */
    public function applyQueryParams(Builder $builder, RequestInterface $request);

    /**
     * @param Builder $builder
     * @param PageBasedPagination $pagination
     * @param array $columns
     * @return Paginator
     */
    public function paginate(Builder $builder, PageBasedPagination $pagination, array $columns);
}
