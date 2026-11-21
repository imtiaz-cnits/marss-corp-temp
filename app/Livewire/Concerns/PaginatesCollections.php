<?php

namespace App\Livewire\Concerns;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait PaginatesCollections
{
    /**
     * Paginate an in-memory array or Collection while staying 100% compatible with Livewire WithPagination.
     *
     * @param  iterable<mixed>  $items
     */
    protected function paginateItems(iterable $items, int $perPage = 15, string $pageName = 'page'): LengthAwarePaginator
    {
        $collection = $items instanceof Collection ? $items->values() : collect($items)->values();
        $total = $collection->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = max(1, min((int) $this->getPage($pageName), $lastPage));
        $slice = $collection->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ],
        );
    }
}
