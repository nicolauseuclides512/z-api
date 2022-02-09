<?php

/**
 * Pagination
 */


/*config ini sebenernya bisa di refactor karena sama,tp kelebihan penulisan seperti ini adalah customable, sapa tau  kedepan ada penambahan status*/
return [
    /*ASSET*/
    'asset_accounts' => [
        'all' => \App\Models\AssetAccount::STATUS_ALL,
        'inactive' => \App\Models\AssetAccount::STATUS_INACTIVE,
        'active' => \App\Models\AssetAccount::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetAccount::STATUS_DELETED
    ],
    'asset_attributes' => [
        'all' => \App\Models\AssetAttribute::STATUS_ALL,
        'inactive' => \App\Models\AssetAttribute::STATUS_INACTIVE,
        'active' => \App\Models\AssetAttribute::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetAttribute::STATUS_DELETED
    ],
    'asset_categories' => [
        'all' => \App\Models\AssetCategory::STATUS_ALL,
        'inactive' => \App\Models\AssetCategory::STATUS_INACTIVE,
        'active' => \App\Models\AssetCategory::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetCategory::STATUS_DELETED
    ],

    'asset_payment_terms' => [
        'all' => \App\Models\AssetPaymentTerm::STATUS_ALL,
        'inactive' => \App\Models\AssetPaymentTerm::STATUS_INACTIVE,
        'active' => \App\Models\AssetPaymentTerm::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetPaymentTerm::STATUS_DELETED
    ],

    'asset_sales_persons' => [
        'all' => \App\Models\AssetSalesPerson::STATUS_ALL,
        'inactive' => \App\Models\AssetSalesPerson::STATUS_INACTIVE,
        'active' => \App\Models\AssetSalesPerson::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetSalesPerson::STATUS_DELETED
    ],
    'asset_salutations' => [
        'all' => \App\Models\AssetSalutation::STATUS_ALL,
        'inactive' => \App\Models\AssetSalutation::STATUS_INACTIVE,
        'active' => \App\Models\AssetSalutation::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetSalutation::STATUS_DELETED
    ],
    'asset_taxes' => [
        'all' => \App\Models\AssetTax::STATUS_ALL,
        'inactive' => \App\Models\AssetTax::STATUS_INACTIVE,
        'active' => \App\Models\AssetTax::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetTax::STATUS_DELETED
    ],
    'asset_uoms' => [
        'all' => \App\Models\AssetUom::STATUS_ALL,
        'inactive' => \App\Models\AssetUom::STATUS_INACTIVE,
        'active' => \App\Models\AssetUom::STATUS_ACTIVE,
        'deleted' => \App\Models\AssetUom::STATUS_DELETED
    ],


    /*USERS*/
    'user' => [
        'all' => \App\Models\User::USER_ALL,
        'inactive' => \App\Models\User::USER_INACTIVE,
        'active' => \App\Models\User::USER_ACTIVE,
        'invited' => \App\Models\User::USER_INVITED,
        'deleted' => \App\Models\User::USER_DELETED
    ],
    'organization' => [
        'all' => \App\Models\User::USER_ALL,
        'inactive' => \App\Models\User::USER_INACTIVE,
        'active' => \App\Models\User::USER_ACTIVE,
        'invited' => \App\Models\User::USER_INVITED,
        'deleted' => \App\Models\User::USER_DELETED
    ],

    /*blm diubah*/
    /*MASTER*/
    'items' => [
        'all' => \App\Models\Item::ITEM_ALL,
        'active' => \App\Models\Item::ITEM_ACTIVE,
        'inactive' => \App\Models\Item::ITEM_INACTIVE,
        'low_stock' => \App\Models\Item::ITEM_LOW_STOCK,
        'un_group' => \App\Models\Item::ITEM_LOW_UN_GROUP,
        'sales' => \App\Models\Item::ITEM_SALES,
        'purchase' => \App\Models\Item::ITEM_PURCHASE,
        'inventory' => \App\Models\Item::ITEM_INVENTORY,
        'sales_and_purchase' => \App\Models\Item::ITEM_SALES_AND_PURCHASE
    ],
    'contacts' => [
        'all' => \App\Models\Contact::STATUS_ALL,
        'active' => \App\Models\Contact::STATUS_ACTIVE,
        'inactive' => \App\Models\Contact::STATUS_INACTIVE,
        'customer' => \App\Models\Contact::STATUS_CUSTOMER,
        'dropshipper' => \App\Models\Contact::STATUS_DROPSHIPPER,
        'vendor' => \App\Models\Contact::STATUS_VENDOR,
        'reseller' => \App\Models\Contact::STATUS_RESELLER,
    ],
    'item_collections' => [
        'all' => \App\Models\Base\BaseModel::STATUS_ALL,
        'inactive' => \App\Models\Base\BaseModel::STATUS_INACTIVE,
        'active' => \App\Models\Base\BaseModel::STATUS_ACTIVE,
        'deleted' => \App\Models\Base\BaseModel::STATUS_DELETED,
        'publish' => \App\Models\ItemCollection::ITEM_COLLECTION_PUBLISH,
        'hidden' => \App\Models\ItemCollection::ITEM_COLLECTION_HIDDEN
    ],
    'discounts' => [
        'all' => \App\Models\Base\BaseModel::STATUS_ALL,
        'disabled' => \App\Models\Base\BaseModel::STATUS_INACTIVE,
        'enabled' => \App\Models\Base\BaseModel::STATUS_ACTIVE,
        'deleted' => \App\Models\Base\BaseModel::STATUS_DELETED,
        'expired' => \App\Models\Discount::DISCOUNT_EXPIRED
    ],
    'sales_orders' => [
        'all' => \App\Models\Base\BaseModel::STATUS_ALL,
        'draft' => \App\Models\SalesOrder::DRAFT,
        'awaiting_payment' => \App\Models\SalesOrder::AWAITING_PAYMENT,
        'overdue' => 'OVERDUE',
        'awaiting_shipment' => \App\Models\SalesOrder::AWAITING_SHIPMENT,
        'fulfilled' => \App\Models\SalesOrder::FULFILLED,
        'canceled' => \App\Models\SalesOrder::CANCELED,
        'paid' => \App\Models\Invoice::PAID,
        'partially_paid' => \App\Models\Invoice::PARTIALLY_PAID,
        'shipped' => 'SHIPPED',
        'not_yet_shipped' => 'NOT_YET_SHIPPED',
        'void' => 'VOID',
        'unpaid' => 'UNPAID'
    ],
    'invoices' => [
        'all' => \App\Models\Base\BaseModel::STATUS_ALL,
        'draft' => '',
        'invoiced' => '',
        'overdue' => '',
        'paid' => '',
        'fulfilled' => '',
        'cancelled' => ''
    ],
    'shipments' => [
        'all' => \App\Models\Base\BaseModel::STATUS_ALL,
    ],

    'stock_adjustments' => [
        'all' => 'ALL',
        'draft' => 'DRAFT',
        'applied' => 'APPLIED',
        'void' => 'VOID',
    ],

    'my_sales_channels' => [
        'all' => 'ALL',
    ],

    'quick_reply_categories' => [ //samakan dengan nama tabel
        'all' => \App\Models\QuickReplyCategory::STATUS_ALL,
        'inactive' => \App\Models\QuickReplyCategory::STATUS_INACTIVE,
        'active' => \App\Models\QuickReplyCategory::STATUS_ACTIVE,
        'deleted' => \App\Models\QuickReplyCategory::STATUS_DELETED
    ],

    'quick_replies' => [
        'all' => \App\Models\QuickReply::STATUS_ALL,
        'inactive' => \App\Models\QuickReply::STATUS_INACTIVE,
        'active' => \App\Models\QuickReply::STATUS_ACTIVE,
        'deleted' => \App\Models\QuickReply::STATUS_DELETED
    ],

    'sales_channels' => [
        'all' => \App\Models\QuickReply::STATUS_ALL,
        'inactive' => \App\Models\QuickReply::STATUS_INACTIVE,
        'active' => \App\Models\QuickReply::STATUS_ACTIVE,
        'deleted' => \App\Models\QuickReply::STATUS_DELETED
    ],
];
