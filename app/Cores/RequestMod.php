<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 * @refactor Arseto Nugroho <satriyo.796@gmail.com>
 */


namespace App\Cores;

trait RequestMod
{
    use HasFilterRequest;

    protected function requestMod()
    {
        $request = $this->request;
        $sortBy = $this->sortBy;
        $filterCfg = $this->model->filterCfg();

        return $this->translateFilterRequest($request, $sortBy, $filterCfg);
    }

}
