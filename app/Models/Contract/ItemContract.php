<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Models\Contract;

interface ItemContract
{
    public function setPrimaryImage($id, $imageId);

    public function uploadMediaAndPop($media);

    public function getSkuCodeGen($id, $lastCode = 0);

    public function storeExec(array $request);

    public function updateExec(array $request, int $id);

    public function scopeGetBySkuCode($q, $skuCode);

    public function importMass($file_url, array $authInfo): void;
}