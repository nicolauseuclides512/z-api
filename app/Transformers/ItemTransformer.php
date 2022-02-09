<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

namespace App\Transformers;

use App\Models\Item;
use App\Transformers\Base\Transformer;

class ItemTransformer extends Transformer
{
    const SIMPLE_FIELDS = [
        'item_id',
        'organization_id',
        'uom_id',
        'tax_id',
        'item_name',
        'item_attributes',
        'weight',
        'weight_unit',
        'dimension_l',
        'dimension_w',
        'dimension_h',
        'code_sku',
        'sales_rate',
        'track_inventory',
        'inventory_stock',
        'inventory_stock_warning',
        'parent_id',
        'category_id',
        'description',
        'compare_rate',
        'barcode',
        'page_title',
        'meta_description',
        'slug',
//        'visibility',
        'is_shown_in_shop',
        'tags',
        'sales_checked',
        'sales_account',
        'sales_description',
        'purchase_checked',
        'purchase_rate',
        'purchase_account',
        'purchase_description',
        'inventory_checked',
        'inventory_rate',
        'inventory_account',
        'item_status',
        'item_type',
        'stock_quantity',
        'primary_images'
    ];

    protected $availableIncludes = [
        'asset_tax',
        'stock',
        'asset_uom',
        'asset_category',
        'item_rates',
        'item_medias',
        'children'
    ];

    public static function inst()
    {
        return new self();
    }

    public function transform(Item $model)
    {
        return $this->filterTransform([
            'item_id' => $model->item_id,
            'organization_id' => $model->organization_id,
            'uom_id' => $model->uom_id,
            'tax_id' => $model->tax_id,
            'item_name' => $model->item_name,
            'item_attributes' => $model->item_attributes,
            'weight' => $model->weight,
            'weight_unit' => $model->weight_unit,
            'dimension_l' => $model->dimension_l,
            'dimension_w' => $model->dimension_w,
            'dimension_h' => $model->dimension_h,
            'code_sku' => $model->code_sku,
            'sales_rate' => $model->sales_rate,
            'track_inventory' => $model->track_inventory,
            'inventory_stock' => $model->inventory_stock,
            'inventory_stock_warning' => $model->inventory_stock_warning,
            'parent_id' => $model->parent_id,
            'category_id' => $model->category_id,
            'description' => $model->description,
            'compare_rate' => $model->compare_rate,
            'barcode' => $model->barcode,
            'page_title' => $model->page_title,
            'meta_description' => $model->meta_description,
            'slug' => $model->slug,
//            'visibility' => $model->visibility,
            'is_shown_in_shop' => $model->is_shown_in_shop,
            'tags' => $model->tags,
            'sales_checked' => $model->sales_checked,
            'sales_account' => $model->sales_account,
            'sales_description' => $model->sales_description,
            'purchase_checked' => $model->purchase_checked,
            'purchase_rate' => $model->purchase_rate,
            'purchase_account' => $model->purchase_account,
            'purchase_description' => $model->purchase_description,
            'inventory_checked' => $model->inventory_checked,
            'inventory_rate' => $model->inventory_rate,
            'inventory_account' => $model->inventory_account,
            'item_status' => $model->item_status,
            'item_type' => $model->item_type,
            'stock_quantity' => $model->stock_quantity,
            'primary_images' => $model->primary_images
        ]);
    }

    public function includeTax(Item $item)
    {
        $tax = $item->asset_tax;

        if (!is_null($tax)) {
            return $this->item($tax, AssetTaxTransformer::inst()->showFields(
                $this->includeFields['asset_tax'] ?? []
            ));
        }

        return $this->null();
    }

    public function includeStock(Item $item)
    {
        $stock = $item->stock;

        if (!is_null($stock)) {
            return $this->item($stock, StockTransformer::inst()
                ->showFields(
                    $this->includeFields['stock'] ?? []
                ));
        }

        return $this->null();
    }

    public function includeAssetUom(Item $item)
    {
        $uom = $item->asset_uom;

        if (!is_null($uom)) {
            return $this->item($uom, AssetUomTransformer::inst()
                ->showFields(
                    $this->includeFields['asset_uom'] ?? []
                ));
        }

        return $this->null();
    }

    public function includeAssetCategory(Item $item)
    {
        $category = $item->asset_category;

        if (!is_null($category)) {
            return $this->item($category, AssetCategoryTransformer::inst()
                ->showFields(
                    $this->includeFields['asset_category'] ?? []
                ));
        }

        return $this->null();
    }

    public function includeItemRates(Item $item)
    {
        $itemRates = $item->item_rates;

        if (!is_null($itemRates)) {
            return $this->collection($itemRates, ItemRateTransformer::inst()
                ->showFields(
                    $this->includeFields['item_rates'] ?? []
                ));
        }

        return $this->null();
    }

    public function includeItemMedias(Item $item)
    {
        $itemMedias = $item->item_medias;

        if (!is_null($itemMedias)) {
            return $this->collection($itemMedias, ItemMediaTransformer::inst()
                ->showFields(
                    $this->includeFields['item_medias'] ?? []
                ));
        }

        return $this->null();
    }

    public function includeChildren(Item $item)
    {
        $children = $item->children;

        if (!is_null($children)) {
            return $this->collection($children, ItemTransformer::inst()
                ->showFields(
                    $this->includeFields['children'] ?? []
                ));
        }

        return $this->null();
    }
}