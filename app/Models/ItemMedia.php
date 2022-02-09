<?php

namespace App\Models;

use App\Exceptions\AppException;
use App\Models\Base\BaseModel;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ItemMedia extends MasterModel
{

    protected $table = 'item_media';

    protected $fillable = [
        'item_id',
        'media_type',
        'is_resized',
        'is_main',
        'media_url',
    ];

    protected $primaryKey = 'item_media_id';

    protected $columnStatus = 'item_media_status';

    protected $columnDefault = array("*");

    protected $columnSimple = array("*");

    protected $appends = ['multi_res_image'];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_media_id');
    }

    public function getMultiResImageAttribute()
    {
        if (!$this->endsWith($this->media_url, '.jpg') &&
            !$this->endsWith($this->media_url, '.jpeg') &&
            !$this->endsWith($this->media_url, '.png')) {
            return null;
        }
        $multiRes = new MultiResImage($this->media_url);
        return $multiRes;
    }

    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    public function rules($id = null)
    {
        return [
            'organization_id' => 'required|integer',
            'item_id' => 'required|integer',
            'media_type' => 'required|string|max:100',
            'is_resized' => 'required|boolean',
            'is_main' => 'required|boolean',
            'media_url' => 'required|string',
            'item_media_status' => 'required|integer|in:0,1'
        ];
    }

    public static function inst()
    {
        return new self();
    }

    public function populate($request = [], BaseModel $model = null)
    {

        if (is_null($model))
            $model = self::inst();

        $req = new Collection($request);

        $model->organization_id = (int)AuthToken::$info->organizationId;
        $model->item_id = $req->get('item_id');
        $model->media_type = $req->get('media_type');
        $model->is_resized = 0;
        $model->is_main = $req->get('is_main') ?? false;
        $model->item_media_status = 1;
        $model->media_url = $req->get('media_url');

        return $model;
    }

    public function scopeFilter($q, $filterBy = "", $key = "")
    {
        $data = $q->getInOrgRef();
        return $data;
    }

    public function scopeGetByItem($q, $itemId)
    {
        return $q->getInOrgRef()
            ->where('item_id', $itemId)
            ->get();
    }

    public function scopeGetByItemFirst($q, $itemId)
    {
        return $q->getInOrgRef()
            ->where('item_id', $itemId)
            ->first();
    }

    public function scopeGetByPath($q, $path)
    {
        return $q
            ->getInOrgRef()
            ->where('media_url', $path)
            ->first();
    }

    public function scopeGetByItemAndPath($q, $itemId, $path)
    {
        return $q
            ->getInOrgRef()
            ->where('item_id', $itemId)
            ->where('media_url', $path)
            ->first();
    }

    public function isMainInItemAvailable($itemId): bool
    {
        $data = $this
            ->getInOrgRef()
            ->where('item_id', $itemId);

        if ($data->count() === 0)
            return true;

        return $data->where('is_main', true)->count() > 0
            ? false : true;
    }

    public function scopeGetByItemAndId($q, $itemId, $id)
    {
        return $q
            ->getInOrgRef()
            ->where('item_id', $itemId)
            ->where('item_media_id', $id)
            ->first();
    }

    public function scopeGetByItemAndIdRef($q, $itemId, $id)
    {
        return $q
            ->getInOrgRef()
            ->where('item_id', $itemId)
            ->where('item_media_id', $id);
    }

    /**
     * @param $media
     * @param $itemId
     * @return BaseModel|ItemMedia|MasterModel
     * @throws \Exception
     */
    public function storeMediaInSpecificItem(array $media, $itemId)
    {
        try {
            //check availability main image by itemId
            if ($this->isMainInItemAvailable($itemId))
                $media['is_main'] = true;

            $imageData = $this->populate($media);
            if (!$imageData->save())
                throw AppException::flash(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    "Save image failed."
                );

            return $imageData;

        } catch (\Exception $e) {
            throw $e;
        }


    }
}
