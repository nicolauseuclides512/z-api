<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 * @refactor Arseto Nugroho <satriyo.796@gmail.com>
 */
namespace App\Cores;

use Illuminate\Support\Facades\Log;
use App\Domain\ValueObjects\SortParameter;

trait HasFilterRequest
{
    protected function translateFilterRequest($request, $sortBy, $filterCfg)
    {
        $page = $request->input('page') ?? 1;
        $perPage = $request->input('per_page') ?? env('APP_PER_PAGE', 15);

        $sortParam = SortParameter::fromRequest($request, $sortBy);
        $filterByRaw = 'all';
        $q = $request->get('q') ?? '';

        #filter
        $filterValue = Filter::getFilter($request->input('filter') ?? $filterByRaw,
            $filterCfg);
        $filterBy = $filterCfg[$filterValue];

        #return
        $newRequest = array(
            'page' => $page,
            'filter_by' => $filterBy,
            'per_page' => $perPage,
            'q' => strtolower($q),
        );

        $newRequest = array_merge($newRequest, $sortParam->toArray());

        $newQuery = $request->input();
        $newQuery['sort'] = $sortParam->toString();
        $newQuery['filter'] = $filterByRaw;
        $newQuery['per_page'] = $perPage;
        $newQuery['q'] = $q;

        $query = [];
        foreach ($newQuery as $key => $value) {
            if ($key != 'page')
                $query[] = $key . '=' . $value;
        }
        $query = '&' . implode('&', $query);

        $newRequest['query'] = $query;

        return $newRequest;
    }
}
