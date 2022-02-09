<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\Contact;
use App\Transformers\Base\Transformer;
use Illuminate\Support\Facades\Log;

class SalesChannelTransformer extends Transformer
{

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'id' => $model->id,
            'channel_name' => $model->channel_name
        ]);
    }

}