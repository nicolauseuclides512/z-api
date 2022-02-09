<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\Contact;
use App\Models\MySalesChannel;
use App\Transformers\Base\Transformer;
use Illuminate\Support\Facades\Log;

class MySalesChannelTransformer extends Transformer
{

    protected $defaultIncludes = [
        'sales_channel'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform($model)
    {
        return $this->filterTransform([
            'id' => $model->id,
//            'organization_id' => $model->organization_id,
//            'sales_channel_id' => $model->sales_channel_id,
        ]);
    }

    public function includeSalesChannel(MySalesChannel $channel)
    {
        $salesChannel = $channel->sales_channel;

        return $salesChannel
            ? $this->item(
                $salesChannel,
                SalesChannelTransformer::inst()
                    ->showFields(
                        $this->includeFields['sales_channel'] ?? []
                    ))
            : $this->null();
    }

}