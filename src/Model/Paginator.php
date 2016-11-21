<?php

namespace CarterZenk\JsonApi\Model;

use Illuminate\Pagination\LengthAwarePaginator;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PageBasedPaginationLinkProviderTrait;
use WoohooLabs\Yin\JsonApi\Schema\Pagination\PaginationLinkProviderInterface;

class Paginator extends LengthAwarePaginator implements PaginationLinkProviderInterface
{
    use PageBasedPaginationLinkProviderTrait;

    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->perPage;
    }
}
