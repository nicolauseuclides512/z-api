<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
    <meta name="author" content="Coderthemes">

    <link rel="shortcut icon" href="assets/images/favicon.ico">

    <title>Zuragan Labels</title>

    <style type="text/css" media="screen, print">
        body {
            font-size: 12px;
            font-family: Arial, sans-serif;
        }

        .shipping-name {
            font-weight: bold;
        }

        .shipping-phone {
            font-size: 11px;
        }

        .shipping-address {
            font-size: 10px;
        }

        .content-page {
            padding-bottom: 10px;
        }

        .row {
            display: flex;
        }

        .row:after, .row:before {
            content: " ";
            display: table;
        }

        .col-md-4 {
            width: 33.33333%;
            position: relative;
            float: left;
            display: block;
        }

        .col-md-6 {
            width: 50%;
            position: relative;
            float: left;
            display: block;
        }

        img.img-responsive {
            height: 60px;
            width: 80px;
            object-fit: cover;
        }

        .col-md-12 {
            width: 100%;
            position: relative;
            float: left;
            display: block;
        }

        .table {
            margin-bottom: 10px;
            width: 300px;
            /*max-width: 100%;*/
            background-color: transparent;
            border-collapse: collapse;
            border-spacing: 0;
            table-layout: fixed;
        }

        .dark-grey-background {
            background: #eeeeee;
            -webkit-print-color-adjust: exact;
        }

        .dark-grey-background th {
            color: #000000;
        }

        .sahito-invoice-table-inside thead th {
            font-size: 10px;
            font-weight: 100;
            border-bottom: 0px !important;
        }

        .sahito-invoice-table-inside thead th,
        .sahito-invoice-table-inside tbody tr > td {
            padding: 8px;
            line-height: 0.5;
            /*height: 20px;*/
            vertical-align: top;
            text-align: left;
            word-wrap: break-word;
        }

        .sahito-invoice-table-inside tbody tr {
            border-bottom: 1px solid #ddd;
            line-height: 0.5px;
        }

        .sahito-invoice-table-inside tbody tr td {
            color: #555;
            font-size: 10px;
            font-weight: 100;
            line-height: 0.5;
        }

        .sahito-invoice-table-inside tbody tr.sub-total {
            text-align: right;
            border-bottom: 0px !important;
        }

        .sahito-invoice-table-inside tbody tr.sub-total td {
            font-weight: 100;
            border-top: 0;
        }

        .sahito-invoice-table-inside tbody tr.total {
            text-align: right;
            border-bottom: 0px !important;
        }

        .sahito-invoice-table-inside tbody tr.sub-total td {
            border-top: 0;
        }

        .sahito-invoice-table-inside tbody tr.balance-due {
            text-align: right;
            border-bottom: 0px !important;
        }

        .sahito-invoice-table-inside tbody tr.balance-due td {
            background: #eeeeee;
            border-top: 0;
            -webkit-print-color-adjust: exact;
        }

        .sahito-invoice-table-inside tbody tr.balance-due td.no-bgr {
            background: none;
        }

        .sahito-invoice-table-inside thead tr th#qty,
        .sahito-invoice-table-inside tbody tr td#qty {
            text-align: right;
        }

        .sahito-invoice-table-inside thead tr th#qty p.qty-type,
        .sahito-invoice-table-inside tbody tr td#qty p.qty-type {
            margin-bottom: 0;
            font-weight: 100;
            color: #888;
        }

        .sahito-invoice-table-inside tbody tr td#rate,
        .sahito-invoice-table-inside thead tr th#rate {
            text-align: right;
        }

        .sahito-invoice-table-inside tbody tr td#amount,
        .sahito-invoice-table-inside thead tr th#amount {
            text-align: right;
        }

        .pull-right {
            text-align: right;
        }

        .sahito-invoice-pdf {
            position: relative;
            border: 1px solid #ddd;
            min-height: 40px;
            width: 300px;
            margin-left: 30px;
            margin-right: 10px;
            padding: 10px;
            line-height: 15px;
        }

        .billfrom p {
            margin: 0;
            color: #aaa;
            font-size: .83em;
        }

        .sahito-invoice-content {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .divider {
            border: 0 solid #e5e5e5;
            border-bottom-width: 1px;
            /*height: 0px;*/
            /*line-height: 20px;*/
            text-align: center;
        }

        /*.divider span {*/
        /*background-color: #FFF;*/
        /*display: inline-block;*/
        /*padding: 0 10px;*/
        /*min-height: 20px;*/
        /*min-width: 10%;*/
        /*}*/

        .dash {
            border-color: #e5e5e5;
            border-style: dashed;
            border-width: 0 0 1px;
            height: 10px;
            line-height: 20px;
            text-align: center;
            /*overflow: visable;*/
            margin-bottom: 10px;
        }

        .dash span {
            background-color: #FFF;
            display: inline-block;
            padding: 0 10px;
            min-height: 20px;
            min-width: 10%;
        }

        .break {
            page-break-before: always;
        }

        .clearfix:after {
            visibility: hidden;
            display: block;
            font-size: 0;
            content: " ";
            clear: both;
            height: 0;
        }

        .clearfix {
            display: inline-block;
        }

        * html .clearfix {
            height: 1%;
        }

        .clearfix {
            display: block;
        }

        /*.carrier_service{*/
            /*padding-bottom: 20px;*/
            /*line-height: 2px;*/
        /*}*/

    </style>
</head>
<body>
<?php foreach ($data as $k => $v) { ?>
    <div class="content-page break">
        <div class="col-md-4">
            <div class="sahito-invoice-pdf">
                <?php $a = $v['sales_order']['shipments'];?>
                <div class="clearfix"></div>
                <div class="col-md-6" style="width: 40%; position: relative; float: left; padding-top: 10px">
                    <img class="img-responsive" src="data:image/png;base64,
                        <?= empty($organization->organizationLogo) ? '' : base64_encode(file_get_contents($organization->organizationLogo)); ?>">
                </div>
                <div class="row sahito-invoice-content" style="width: 60%; position: relative; float: left;">
                    <div class="billfrom col-md-6">
                        <div class="shipping-address" style="text-align: left">
                            <?= $organization->organizationName ?? '-'; ?><br>
                            <?= $organization->phone ?? '-'; ?><br>
                            <?= $organization->district_name; ?><br>
                            <?= $organization->province_name; ?><br>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br>
                <div class="clearfix"></div>
                <div class="divider"></div>
                <div class="row sahito-invoice-content">
                    <div class="col-md-12" style="width: 60%">
                        <div class="billfrom">
                            <label class="shipping-name">
                                Kepada:
                            </label>
                            <div class="shipping-address">
                                <?= $v['sales_order']->contact->display_name ?? '-'; ?><br>
                                <?= $v['sales_order']->contact->phone ?? '-'; ?><br>
                                <?= $v['sales_order']['shipping_address'] ?? '-'; ?><br>
                                <?= $v['sales_order']['shipping_district_name']; ?> <?= $v['sales_order']['shipping_zip']; ?>
                                <br>
                                <?= $v['sales_order']['shipping_province_name']; ?><br>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12" style="width: 40%">
                        <p style="font-size: 10px">
                            No. SO : <?= $v['sales_order']['sales_order_number'] ?>
                        </p>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br>
                <div style="border: 1px dashed #e5e5e5; "></div>
                <div class="row sahito-invoice-content">
                    <div>
                        <div class="border-1 table-responsive">
                            <table class="table table-hover sahito-invoice-table-inside">
                                <thead>
                                <tr class="dark-grey-background">
                                    <th id="sku" style="width: 30%">
                                        <div>SKU</div>
                                    </th>
                                    <th id="item" style="width: 50%">
                                        <div>Item</div>
                                    </th>
                                    <th id="qty" style="width: 20%">
                                        <div>Qty</div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($v['sales_order']->sales_order_details as $dk => $dv) { ?>
                                    <tr>
                                        <td id="sku" style="width: 30%; line-height: 10px">
                                            <div><?= $dv->item->code_sku ?></div>
                                        </td>
                                        <td id="item" style="width: 50%; line-height: 10px">
                                            <div><?= $dv->item_name; ?></div>
                                        </td>
                                        <td id="qty" style="width: 20%; line-height: 10px">
                                            <div><?= $dv->item_quantity; ?> <?= $dv->uom; ?></div>
                                        </td>
                                    </tr>
                                    <?php
                                } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="carrier_service">
                    <div style="font-size: 10px">Kurir: </div>
                    <div>
                        <?php foreach ($v['sales_order']->invoices as $shipment) { ?>
                            <?= $shipment->shipping_carrier_service != null ? $shipment->shipping_carrier_service : ""; ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

</body>
</html>