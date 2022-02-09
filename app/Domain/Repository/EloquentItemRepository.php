<?php

namespace App\Domain\Repository;

use App\Domain\Contracts\ItemRepository;
use App\Models\Item;

class EloquentItemRepository implements ItemRepository
{
    private $itemModel;

    public function __construct(Item $itemModel)
    {
        $this->itemModel = $itemModel;
    }

    public function getItemInOrg($itemId)
    {
        return $this->itemModel->getItemInOrg($itemId);
    }
}
