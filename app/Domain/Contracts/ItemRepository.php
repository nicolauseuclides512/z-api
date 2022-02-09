<?php
namespace App\Domain\Contracts;

interface ItemRepository
{
    public function getItemInOrg($itemId);
}
