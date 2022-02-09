<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Cores;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator as PaginatorParent;

class CustomTransformerPaginator extends PaginatorParent
{
    private $total;
    private $lastPage;
    private $count;
    private $morePage;

    public function __construct(LengthAwarePaginator $pagedData, $transformData)
    {

        parent::__construct(
            $transformData,
            $pagedData->perPage(),
            $pagedData->currentPage(),
            []
        );
        $this->total = $pagedData->total();
        $this->lastPage = $pagedData->lastPage();
        $this->count = $pagedData->count();
        $this->morePage = $pagedData->lastPage() > $pagedData->currentPage();
        $this->total = $pagedData->total();
        $this->lastPage = $pagedData->lastPage();
        $this->pageName = $pagedData->getPageName();
        $this->setPath(strtok(($pagedData->url($pagedData->currentPage())), "?"));
    }

    /**
     * @return mixed
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * @param mixed $total
     */
    public function setTotal($total): void
    {
        $this->total = $total;
    }

    /**
     * @return mixed
     */
    public function lastPage()
    {
        return $this->lastPage;
    }

    /**
     * @param mixed $lastPage
     */
    public function setLastPage($lastPage): void
    {
        $this->lastPage = $lastPage;
    }

    /**
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->morePage;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }
}