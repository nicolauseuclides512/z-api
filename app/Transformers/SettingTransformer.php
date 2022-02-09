<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;


use App\Transformers\Base\Transformer;

class SettingTransformer extends Transformer
{
    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return [

        ];
    }
}