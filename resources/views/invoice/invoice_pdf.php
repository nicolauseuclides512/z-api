<html xmlns:0>
<head>
    <title>Invoice pdf</title>
    <style type="text/css">
        #status_bg {
            font-size: 10px;
            font-weight: bold;
            color: #FFF;
            text-transform: uppercase;
            text-align: center;
            line-height: 20px;
            transform: rotate(-45deg);
            -webkit-transform: rotate(-45deg);
            width: 100px;
            display: block;
            background: linear-gradient(#9BC90D 0%, #79A70A 100%);
            box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
            position: absolute;
            -webkit-print-color-adjust: exact;
            top: 20px;
            left: -21px;
            background: #9BC90D;
        }

        thead {
            display: table-row-group
        }
    </style>
</head>
<body>
<div class="content-page" style="width: 100%;">
    <div class="zuragan-invoice-pdf" style="position: relative; min-height: 500px;">
        <div class="ribbon_payment"
             style="position: absolute;left: -5px;top: -5px;z-index: 1;overflow: hidden;width: 75px;height: 75px;text-align: right;">
            <?php
            if (strcmp($invoice->sales_order->invoice_status,"UNPAID") === 0 && strcmp($invoice->sales_order->shipment_status,"NOT_YET_SHIPPED") === 0){
                ?> <span style="background: #ffc100;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.unpaid'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"UNPAID") === 0 && strcmp($invoice->sales_order->shipment_status,"SHIPPED") === 0){
                ?> <span style="background: #ffc100;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.unpaid'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"PAID") === 0 && strcmp($invoice->sales_order->shipment_status,"NOT_YET_SHIPPED") === 0){
                ?> <span style="background: #009933;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.paid'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"PAID") === 0 && strcmp($invoice->sales_order->shipment_status,"SHIPPED") === 0){
                ?> <span style="background: #2FA3E6;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.paid'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"OVERDUE") === 0 && strcmp($invoice->sales_order->shipment_status,"NOT_YET_SHIPPED") === 0){
                ?> <span style="background: #ffc100;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.overdue'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"OVERDUE") === 0 && strcmp($invoice->sales_order->shipment_status,"SHIPPED") === 0){
                ?> <span style="background: #ffc100;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.overdue'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"PARTIALLY_PAID") === 0 && strcmp($invoice->sales_order->shipment_status,"NOT_YET_SHIPPED") === 0){
                ?> <span style="background: #ffc100;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.partially_paid'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"PARTIALLY_PAID") === 0 && strcmp($invoice->sales_order->shipment_status,"SHIPPED") === 0){
                ?> <span style="background: #ffc100;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.partially_paid'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"VOID") === 0){
                ?> <span style="background: #444444;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.void'))?></span> <?php
            }
            elseif (strcmp($invoice->sales_order->invoice_status,"DRAFT") === 0){
                ?> <span style="background: #aaaaaa;" id='status_bg'><?= strtoupper(trans('messages.invoice_status.open'))?></span> <?php
            }
            ?>
        </div>

        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
            <?php if (\App\Models\AuthToken::info()->organizationLogo != null) {
                ?>
                <div class="col-md-4 pt-20"
                     style="width: 20%;position: relative;float: left;display: block;padding-top: 50px;padding-left: 40px">

                    <?php if (isset(\App\Models\AuthToken::info()->organizationLogo)) { ?>
                        <img class="img-responsive"
                             src="data:image/png;base64, <?= base64_encode(file_get_contents(\App\Models\AuthToken::info()->organizationLogo)); ?>"
                             style="max-height: 120px;max-width: 160px;object-fit: cover;">
                    <?php } ?>
                </div>
                <style>
                    div.office-desc {
                        width: 40%;
                    }
                </style><?php
            } else {
                ?>
                <style>
                    div.office-desc {
                        width: 60%;
                        padding-left: 40px;
                    }
                </style><?php
            } ?>
            <div class="office-desc pt-10"
                 style="position: relative;float: left;display: block;padding-top: 50px;">
                <h4 style="line-height: normal;font-weight: 100;font-size: 1em;margin-bottom: 0;margin-left: 0;">
                    <?= \App\Models\AuthToken::info()->organizationName; ?>
                </h4>
                <p style="margin-top: 0;line-height: normal;margin-bottom: 0;font-size: .83em;color: #aaa;margin-left: 0;">
                    <?php if ($invoice->organization['address'] != "null") {
                        if ($invoice->organization['region'] != null)
                            echo $invoice->organization['address'], ',';
                        else
                            echo $invoice->organization['address'], '';
                    } else
                        echo ''; ?>
                </p>
                <p style="margin-top: 0;line-height: normal;margin-bottom: 0;font-size: .83em;color: #aaa;margin-left: 0;">
                    <?php
                    if ($invoice->organization['region'] != null) {
                        if ($invoice->organization['district'] != null) {
                            echo $invoice->organization['region'], ', ';
                            if ($invoice->organization['province'] != null || $invoice->organization['country'] != null || $invoice->organization['zip'] != null || $invoice->organization['phone'] != null) {
                                echo $invoice->organization['district'], ',';
                            } else
                                echo $invoice->organization['district'], '';
                        } else
                            echo $invoice->organization['region'], '';
                    } else
                        echo '';
                    ?>
                </p>
                <p style="margin-top: 0;line-height: normal;margin-bottom: 0;font-size: .83em;color: #aaa;margin-left: 0;">
                    <?php
                    if ($invoice->organization['province'] != null) {
                        if ($invoice->organization['country'] != null && $invoice->organization['zip'] != null) {
                            echo $invoice->organization['province'], ', ';
                            echo $invoice->organization['country'], ' ';
                            echo $invoice->organization['zip'];
                        } elseif ($invoice->organization['country'] != null && $invoice->organization['zip'] == null) {
                            echo $invoice->organization['province'], ', ';
                            echo $invoice->organization['country'], ' ';
                        } elseif ($invoice->organization['country'] == null && $invoice->organization['zip'] != null) {
                            echo $invoice->organization['province'], ', ';
                            echo $invoice->organization['zip'];
                        } else
                            echo $invoice->organization['province'], '';
                    } else
                        echo '';
                    ?>
                </p>
                <p style="margin-top: 0;line-height: normal;margin-bottom: 0;font-size: .83em;color: #aaa;margin-left: 0;">
                    <?php if ($invoice->organization['phone'] != null)
                        echo $invoice->organization['phone'];
                    else
                        echo ''; ?>
                </p>
            </div>
            <div class="col-md-8 text-right"
                 style="width: 30%;position: relative;float: left;display: block;text-align: right;padding-top: 40px;">
                <?php if ($invoice->invoice_status == 'DRAFT') { ?>
                    <h1 class="page-title text-right" style="font-size: 32pt; font-weight: 100; margin-bottom: 0;"><?= strtoupper(trans('messages.order')) ?></h1>
                <?php } else { ?>
                    <h1 class="page-title text-right" style="font-size: 32pt; font-weight: 100; margin-bottom: 0;"><?= strtoupper(trans('messages.invoice')) ?></h1>
                <?php }  ?>
                <p class="inv-code" style="margin-top: 10px;line-height: normal;color: #aaa;">
                    <?= $invoice->invoice_number; ?></p>
                <p><?= $invoice->sales_order_status; ?></p>
                <p class="inv-balance"
                   style="margin-top: 8px;line-height: 1;font-size: .9em;color: #555;margin-bottom: 0;"><?= trans('messages.balance_due') ?></p>
                <h4 style="margin-top: 10px;line-height: normal;font-weight: 100;font-size: 1em;">
                    <?= trans('messages.rp') . ' ' . str_replace(',', '.', number_format($invoice->balance_due, 0)); ?></h4>
            </div>
        </div>
        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
            <div class="col-md-4" style="width: 30%;position: relative;float: left;display: block;padding-left: 40px">
                <div class="billto" style="margin-top: 30px;">
                    <p style="margin-top: 0;line-height: normal;margin-bottom: 0;color: #aaa;font-size: .83em;"><?= trans('messages.bill_to') ?></p>
                    <h4 style="margin-top: 0;line-height: normal;font-weight: lighter;font-size: 1em;margin: 0;color: #555;">
                        <?= $invoice->contact->display_name ?? ''; ?>
                    </h4>
                    <p style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->contact->phone ?? '-'; ?> <br>
                        <?= $invoice->contact->mobile ?? '-'; ?>
                    </p>
                    <p style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_address ?? ''; ?>
                    </p>
                    <p style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_area['region_name'] ?? ''; ?>
                    </p>
                    <p style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_area['district_name'] ?? ''; ?>
                    </p>
                    <p style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_area['province_name'] ?? ''; ?>
                    </p>
                    <p style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->contact->billing_zip ?? ''; ?> <?= $invoice->billing_area['country_name'] ?? ''; ?>
                    </p>
                </div>
                <br/>
                <br/>
            </div>
            <div class="col-md-8" style="width: 57%;position: relative;float: left;display: block;">
                <div class="col-md-9 col-md-offset-3 invoice-date"
                     style="width: 80%;position: relative;float: left;display: block;margin-left: 25%;margin-top: 50px;padding-right: 0;">
                    <div class="form-group">
                        <div class="col-md-6"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                            <?= trans('messages.invoice_date') ?> :
                        </div>
                        <div class="col-md-6 invoice-date-term"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                            <?= date('j M Y', strtotime($invoice->invoice_date)); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                            <?= trans('messages.terms') ?> :
                        </div>
                        <div class="col-md-6 invoice-date-term"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                            <?= trans('messages.due_on_receipt') ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                            <?= trans('messages.due_date') ?> :
                        </div>
                        <div class="col-md-6 invoice-date-term"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                            <?= date('j M Y', strtotime($invoice->due_date)); ?>
                        </div>
                    </div>
                    <!--                    <div class="form-group">-->
                    <!--                        <div class="col-md-6"-->
                    <!--                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">-->
                    <!--                            P.O.#:-->
                    <!--                        </div>-->
                    <!--                        <div class="col-md-6 invoice-date-term"-->
                    <!--                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">-->
                    <!--                            SO-00001-->
                    <!--                        </div>-->
                    <!--                    </div>-->
                </div>
            </div>
        </div>
        <div class="row zuragan-invoice-content p-15 pt-0 m-b-20" style="display: flex;padding: 15px;">
            <div class="col-md-12" style="width: 100%;position: relative;float: left;display: block;">
                <div class="border-1 table-responsive mt-20"
                     style="solid #ddd; padding-left: 40px;">
                    <table class="table table-hover zuragan-invoice-table-inside"
                           style="margin-bottom: 10px;width: 95%;max-width: 100%;background-color: transparent;border-collapse: collapse;border-spacing: 0;">
                        <thead style="display: table-row-group;">
                        <tr class="dark-grey-background" style="background: #eeeeee;-webkit-print-color-adjust: exact;">

                            <th id="item"
                                style="float:left;color: #000000;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;border-bottom: 0px !important;">
                                <?= trans('messages.products') ?>
                            </th>
                            <th id="qty"
                                style="color: #000000;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: left;border-bottom: 0px !important;">
                                <?= trans('messages.qty') ?>
                            </th>
                            <th id="rate"
                                style="color: #000000;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: left;border-bottom: 0px !important;">
                                <?= trans('messages.rate') ?>
                            </th>
                            <th id="discount"
                                style="color: #000000;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: left;border-bottom: 0px !important;">
                                <?= trans('messages.discount') ?>
                            </th>
                            <th id="amount"
                                style="color: #000000;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">

                            </th>
                            <th id="amount"
                                style="color: #000000;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                <?= trans('messages.amount') ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($invoice->invoice_details as $key => $value) { ?>
                            <tr style="border-bottom: 1px solid #ddd; page-break-inside: avoid;">

                                <td id="item"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;">
                                    <?= $value->item_name; ?>
                                </td>
                                <td id="qty"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: left;">
                                    <p class="qty-amount" style="margin-top: 0;line-height: normal;margin-bottom: 0;">
                                        <?= $value->item_quantity ?>
                                    </p>
                                    <p class="qty-type"
                                       style="margin-top: 0;line-height: normal;margin-bottom: 0;font-weight: 100;color: #888;">
                                        <?= $value->uom; ?>
                                    </p>
                                </td>
                                <td id="rate"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: left;">
                                    <?= trans('messages.rp') . ' ' , str_replace(',', '.', number_format($value->item_rate, 0)); ?>
                                </td>
                                <td id="discount"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: left;">
                                    <?= ($value->discount_amount_type == 'fixed') ? trans('messages.rp') . ' '. str_replace_first(',', '.', number_format($value->discount_amount_value, 0)) : $value->discount_amount_value . "%"; ?>
                                </td>
                                <td id="amount"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                    <?= trans('messages.rp') . ' ' ?>
                                </td>
                                <td id="amount"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                    <?= str_replace(',', '.', number_format($value->amount, 0)); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="t_sub-total" style="border-bottom: 0px !important;text-align: left;">
                            <td colspan="3" class="no-bgr"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;text-align: left">
                                <?= trans('messages.sub_total') ?>
                            </td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                <?= trans('messages.rp') . ' ' ?>
                            </td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;text-align: right;">
                                <?= str_replace(',', '.', number_format($invoice->sub_total, 0)); ?>
                            </td>
                        </tr>
                        <?php if (!empty((int)$invoice->shipping_rate)) { ?>
                        <tr class="t_shipping_charge" style="border-bottom: 0px !important;text-align: left;">
                            <td colspan="3" class="no-bgr"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                            <?php
                                switch ($invoice->shipping_carrier_service){
                                    case "Shipping Charge": ?>
                                        <td colspan="1"
                                            style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;text-align: left">
                                            <?= ($invoice->shipping_carrier_service) ?>
                                        </td>
                                        <?php
                                        break;
                                    case "Ongkos Kirim": ?>
                                        <td colspan="1"
                                            style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;text-align: left">
                                            <?= ($invoice->shipping_carrier_service) ?>
                                        </td>
                                        <?php
                                        break;
                                    default: ?>
                                        <td colspan="1"
                                            style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;text-align: left">
                                            <?php echo trans('messages.shipping_charge') ?> <?= ($invoice->shipping_carrier_service) ?>
                                        </td>
                                    <?php
                                        break;
                                }
                            ?>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                <?= trans('messages.rp') . ' ' ?>
                            </td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;text-align: right;">
                                <?= str_replace(',', '.', number_format($invoice->shipping_rate, 0)); ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if (!$invoice->tax_included) { ?>
                            <tr class="t_tax" style="border-bottom: 0px !important;text-align: left;">
                                <td colspan="3"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;">
                                    Tax
                                </td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;">
                                    Ppn 10%
                                </td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                    <?= trans('messages.rp') . ' ' ?>
                                </td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                    <?= str_replace(',', '.', number_format($invoice->tax, 0)); ?>
                                </td>
                            </tr>

                        <?php } ?>
                        <?php if (!empty((int)$invoice->adjustment_value)) { ?>
                            <tr class="t_adjustment" style="border-bottom: 0px !important;text-align: left;">
                                <td colspan="3" class="no-bgr"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;">
                                    <?= !empty($invoice->adjustment_name) ? $invoice->adjustment_name : trans('messages.adjustment_so'); ?>
                                </td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                    <?= trans('messages.rp') . ' ' ?>
                                </td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                    <?= str_replace(',', '.', number_format($invoice->adjustment_value, 0)); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="t_total" style="border-bottom: 0px !important;text-align: left;">
                            <td colspan="3" class="no-bgr"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;">
                                <?= trans('messages.total') ?>
                            </td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                <?= trans('messages.rp') . ' ' ?>
                            </td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;">
                                <?= str_replace(',', '.', number_format($invoice->total, 0)); ?>
                            </td>
                        </tr>
                        <?php foreach ($invoice->payments as $key => $value) { ?>
                            <tr class="adjustment" style="border-bottom: 0px !important;text-align: left;">
                                <td colspan="3" class="no-bgr"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal; border-top: 1px solid #dddddd"><?= trans('messages.paid_at') . ' ' . date('j M Y', strtotime($value->formatted_date)); ?></td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;border-top: 1px solid #dddddd"><?= trans('messages.rp') . ' ' ?></td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;text-align: right;border-top: 1px solid #dddddd"><?= str_replace(',', '.', number_format($value->amount, 0)); ?></td>
                            </tr>
                        <?php } ?>
                        <tr class="balance-due" style="border-bottom: 0px !important;text-align: left;">
                            <td colspan="3" class="no-bgr"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: #eeeeee;border-top: 0;-webkit-print-color-adjust: exact;">
                                <?= trans('messages.balance_due') ?>
                            </td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: #eeeeee;border-top: 0;-webkit-print-color-adjust: exact;text-align: right;">
                                <?= trans('messages.rp') . ' ' ?>
                            </td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: normal;background: #eeeeee;border-top: 0;-webkit-print-color-adjust: exact;text-align: right;">
                                <?= str_replace(',', '.', number_format($invoice->balance_due, 0)); ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row zuragan-invoice-content p-15" style="display: flex; padding-left: 40px">
            <div class="col-md-12" style="width: 100%;position: relative;float: left;display: block;">
                <div class="zuragan-invoice-notes m-b-20" style="margin-bottom: 20px;">
                    <?php if($invoice->customer_notes != null && $invoice->customer_notes != ""){
                        ?>
                        <h3 style="font-size: 1.1em;color: #888;font-weight: 100;margin-top: 0;margin-bottom: 0;vertical-align: middle;line-height: 2;">
                            <?= trans('messages.customer_notes') ?></h3>
                        <p style="margin-top: 0;line-height: normal;font-size: .83em;margin-bottom: 0;">
                            <?= $invoice->customer_notes ?>
                        </p>
                        <?php
                    }?>
                    <?php if($invoice->term_and_condition != null && $invoice->term_and_condition != ""){
                        ?>
                        <h3 style="font-size: 1.1em;color: #888;font-weight: 100;margin-top: 0;margin-bottom: 0;vertical-align: middle;line-height: 2;">
                            <?= trans('messages.terms_conditions') ?></h3>
                        <p style="margin-top: 0;line-height: normal;font-size: .83em;margin-bottom: 0;">
                            <?= $invoice->term_and_condition ?>
                        </p>
                        <?php
                    }?>
                </div>
                <!--                <div class="zuragan-invoice-notes" style="margin-bottom: 20px;">-->
                <!--                    <h3 style="font-size: 1.1em;color: #888;font-weight: 100;margin-top: 0;margin-bottom: 0;vertical-align: middle;line-height: 2;">-->
                <!--                        Terms & Condition</h3>-->
                <!--                    <p style="margin-top: 0;line-height: normal;font-size: .83em;margin-bottom: 0;">This is the terms-->
                <!--                        and conditions:</p>-->
                <!--                    <ol style="padding-left: 15px;font-size: .83em;margin-top: 5px;">-->
                <!--                        <li>Yada Yada Yada</li>-->
                <!--                        <li>Yada Yada Yada</li>-->
                <!--                    </ol>-->
                <!--                </div>-->
            </div>
        </div>
    </div>
</div>
</body>
</html>