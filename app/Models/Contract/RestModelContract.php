<?php

namespace App\Models\Contract;


interface RestModelContract
{
    public function storeExec(array $request);

    public function updateExec(array $request, int $id);

    public function destroyExec(int $id);

    public function destroySomeExec(string $ids);
}