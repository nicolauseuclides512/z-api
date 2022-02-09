<?php

return [
    //when adding more category, add validation rule for category code here
    //with comma separated values
    'category_rule' => \App\Models\StockAdjustment::REASON_CATEGORY,

    'categories' => [
        [
            'code' => \App\Models\StockAdjustment::REASON_CATEGORY,
            'description' => 'Adjustment',
        ]
    ],
    'stock_adjustment' => [
        'category' => \App\Models\StockAdjustment::REASON_CATEGORY,
        'default_reasons' => [
            'Barang dicuri',
            'Barang rusak',
            'Stock terbakar',
            'Stock dihapus',
            'Revaluasi inventaris',
        ],
    ],
];
