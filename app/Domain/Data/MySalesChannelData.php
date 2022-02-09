<?php
/**
 * @author Arseto Nugroho <satriyo.796@gmail.com>.
 */

namespace App\Domain\Data;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class MySalesChannelData implements Arrayable
{
    private $id;
    private $salesChannelId;
    private $storeName;
    private $displayMode = 1;
    private $order;
    private $isShown;
    private $externalLink;

    public function __construct($id, $salesChannelId, $storeName, $external_link, $displayMode, $is_shown, $order)
    {
        $this->id = $id;
        $this->salesChannelId = $salesChannelId;
        $this->storeName = $storeName;
        $this->externalLink = $external_link;
        $this->displayMode = $displayMode;
        $this->isShown = $is_shown;
        $this->order = $order;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSalesChannelId()
    {
        return $this->salesChannelId;
    }

    public function getStoreName()
    {
        return $this->storeName;
    }

    public function getDisplayMode()
    {
        return $this->displayMode;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getIsShown()
    {
        return $this->isShown;
    }

    public function getExternalLink()
    {
        return $this->externalLink;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'sales_channel_id' => $this->salesChannelId,
            'store_name' => $this->storeName,
            'display_mode' => $this->displayMode,
            'order' => $this->order,
            'is_shown' => $this->isShown,
            'external_link' => $this->externalLink,
        ];
    }

    public static function new(Request $request)
    {
        $data = new static(
            null,
            $request->get('sales_channel_id'),
            $request->get('store_name'),
            $request->get('external_link'),
            $request->has('display_mode')
            ? $request->get('display_mode')
            : 1,
            $request->get('is_shown') === 'false' ? false : true,
            $request->has('order')
            ? $request->get('order')
            : 1
        );
        return $data;
    }

    public static function update($id, Request $request)
    {
        $data = new static(
            $id,
            $request->get('sales_channel_id'),
            $request->get('store_name'),
            $request->get('external_link'),
            $request->has('display_mode')
            ? $request->get('display_mode')
            : 1,
            $request->get('is_shown') === 'false' ? false : true,
            $request->has('order')
            ? $request->get('order')
            : 1
        );
        return $data;
    }
}
