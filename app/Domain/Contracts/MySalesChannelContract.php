<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */
namespace App\Domain\Contracts;

use App\Domain\Data\MySalesChannelData;

interface MySalesChannelContract
{
    public function create(MySalesChannelData $data);
    public function update(MySalesChannelData $data);
    public function delete($id);
    public function detail($id);
    public function fetch(array $filterRequest);
    public function homeShop(array $filterRequest);
    public function all();
    public function setup($force = false);
}
