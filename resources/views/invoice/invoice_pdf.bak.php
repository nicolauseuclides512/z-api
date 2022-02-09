<html>
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
    </style>
</head>
<body>
<div class="content-page" style="width: 100%;">
    <div class="zuragan-invoice-pdf" style="position: relative; min-height: 500px;">
        <div class="ribbon"
             style="position: absolute;left: -5px;top: -5px;z-index: 1;overflow: hidden;width: 75px;height: 75px;text-align: right;">
            <?= $invoice->invoice_status == 'DRAFT'
                ? "<span id='status_bg' style='background: #999999; '>$invoice->invoice_status</span>"
                : "<span id='status_bg'>$invoice->invoice_status</span>"; ?>
        </div>

        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
            <div class="col-md-4 pt-20"
                 style="width: 33.33333%;position: relative;float: left;display: block;padding-top: 20px;">

                <?php if (isset(\App\Models\AuthToken::info()->organizationLogo)) { ?>
                    <img class="img-responsive"
                         src="data:image/png;base64, <?= base64_encode(file_get_contents(\App\Models\AuthToken::info()->organizationLogo)); ?>"
                         style="max-height: 120px;max-width: 160px;object-fit: cover;">
                <?php } ?>

            </div>
            <div class="col-md-8 text-right"
                 style="width: 66.66667%;position: relative;float: left;display: block;text-align: right;">
                <h1 style="margin-bottom: 0;line-height: 43px;font-size: 36px;">INVOICE</h1>
                <p class="inv-code" style="margin-top: 0;line-height: normal;color: #aaa;">
                    #<?= $invoice->invoice_number; ?></p>
                <p class="inv-balance"
                   style="margin-top: 30px;line-height: 1;font-size: .9em;color: #555;margin-bottom: 0;">Balance Due</p>
                <h4 style="margin-top: 0;line-height: normal;font-weight: 600;font-size: 1.2em;">
                    Rp. <?= number_format($invoice->balance_due, 0); ?></h4>
            </div>
        </div>
        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
            <div class="col-md-4" style="width: 33.33333%;position: relative;float: left;display: block;">
                <div class="office-desc">
                    <h4 style="margin-top: 0;line-height: normal;font-weight: 600;font-size: 1em;margin-bottom: 0;">
                        <?= \App\Models\AuthToken::info()->organizationName; ?>
                    </h4>
                    <p style="margin-top: 0;line-height: normal;margin-bottom: 0;font-size: .83em;color: #aaa;">
                        Indonesia
                    </p>
                </div>
                <div class="billto" style="margin-top: 30px;">
                    <p style="margin-top: 0;line-height: normal;margin-bottom: 0;color: #aaa;font-size: .83em;">Bill
                        To</p>
                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->contact->display_name ?? ''; ?>
                    </h4>
                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_address ?? ''; ?>
                    </h4>
                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_area['region_name'] ?? ''; ?>
                    </h4>
                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_area['district_name'] ?? ''; ?> <?= $invoice->billing_zip ?? ''; ?>
                    </h4>
                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->billing_area['country_name'] ?? ''; ?>
                    </h4>
                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                        <?= $invoice->contact->phone ?? '-'; ?> <br>
                        <?= $invoice->contact->mobile ?? '-'; ?>
                    </h4>
                </div>
            </div>
            <div class="col-md-8" style="width: 66.66667%;position: relative;float: left;display: block;">
                <div class="col-md-9 col-md-offset-3 invoice-date"
                     style="width: 75%;position: relative;float: left;display: block;margin-left: 25%;margin-top: 70px;padding-right: 0;">
                    <div class="form-group">
                        <div class="col-md-6"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                            Invoice Date:
                        </div>
                        <div class="col-md-6 invoice-date-term"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                            <?= date('j F Y', strtotime($invoice->invoice_date)); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                            Terms:
                        </div>
                        <div class="col-md-6 invoice-date-term"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                            Due on Receipt
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                            Due Date:
                        </div>
                        <div class="col-md-6 invoice-date-term"
                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                            <?= date('j F Y', strtotime($invoice->due_date)); ?>
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
                     style="border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;">
                    <table class="table table-hover zuragan-invoice-table-inside"
                           style="margin-bottom: 10px;width: 100%;max-width: 100%;background-color: transparent;border-collapse: collapse;border-spacing: 0;">
                        <thead>
                        <tr class="dark-grey-background" style="background: #333333;-webkit-print-color-adjust: exact;">
                            <th style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;border-bottom: 0px !important;">
                                #
                            </th>
                            <th id="item"
                                style="float:left;color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;border-bottom: 0px !important;">
                                Item & Description
                            </th>
                            <th id="qty"
                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                Qty
                            </th>
                            <th id="rate"
                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                Rate
                            </th>
                            <th id="discount"
                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                Discount
                            </th>
                            <th id="amount"
                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                Amount
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($invoice->invoice_details as $key => $value) { ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                    <?= $key + 1; ?>
                                </td>
                                <td id="item"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                    <?= $value->item_name; ?>
                                </td>
                                <td id="qty"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                    <p class="qty-amount" style="margin-top: 0;line-height: normal;margin-bottom: 0;">
                                        <?= $value->item_quantity ?>
                                    </p>
                                    <p class="qty-type"
                                       style="margin-top: 0;line-height: normal;margin-bottom: 0;font-weight: 100;color: #888;">
                                        <?= $value->uom; ?>
                                    </p>
                                </td>
                                <td id="rate"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                    <?= 'Rp. ' . number_format($value->item_rate, 0); ?>
                                </td>
                                <td id="discount"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                    <?= ($value->discount_amount_type == 'fixed') ? 'Rp. ' . number_format($value->discount_amount_value, 0) : $value->discount_amount_value . "%"; ?>
                                </td>
                                <td id="amount"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                    <?= 'Rp. ' . number_format($value->amount, 0); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="t_sub-total" style="border-bottom: 0px !important;text-align: right;">
                            <td colspan="4"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;">
                                Sub Total
                            </td>
                            <td colspan="2"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;">
                                <?= 'Rp. ' . number_format($invoice->sub_total, 0); ?>
                            </td>
                        </tr>
                        <?php if (!$invoice->tax_included) { ?>
                            <tr class="t_tax" style="border-bottom: 0px !important;text-align: right;">
                                <td colspan="4"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                    Tax
                                </td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                    Ppn 10%
                                </td>
                                <td colspan="1"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                    <?= 'Rp. ' . number_format($invoice->tax, 0); ?>
                                </td>
                            </tr>

                        <?php } ?>
                        <?php if (!empty((int)$invoice->adjustment_value)) { ?>
                            <tr class="t_adjustment" style="border-bottom: 0px !important;text-align: right;">
                                <td colspan="4"<html>
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
                                    </style>
                                </head>
                                <body>
                                <div class="content-page" style="width: 100%;">
                                    <div class="zuragan-invoice-pdf" style="position: relative; min-height: 500px;">
                                        <div class="ribbon"
                                             style="position: absolute;left: -5px;top: -5px;z-index: 1;overflow: hidden;width: 75px;height: 75px;text-align: right;">
                                            <?= $invoice->invoice_status == 'DRAFT'
                                                ? "<span id='status_bg' style='background: #999999; '>$invoice->invoice_status</span>"
                                                : "<span id='status_bg'>$invoice->invoice_status</span>"; ?>
                                        </div>

                                        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
                                            <div class="col-md-4 pt-20"
                                                 style="width: 33.33333%;position: relative;float: left;display: block;padding-top: 20px;">

                                                <?php if (isset(\App\Models\AuthToken::info()->organizationLogo)) { ?>
                                                    <img class="img-responsive"
                                                         src="data:image/png;base64, <?= base64_encode(file_get_contents(\App\Models\AuthToken::info()->organizationLogo)); ?>"
                                                         style="max-height: 120px;max-width: 160px;object-fit: cover;">
                                                <?php } ?>

                                            </div>
                                            <div class="col-md-8 text-right"
                                                 style="width: 66.66667%;position: relative;float: left;display: block;text-align: right;">
                                                <h1 style="margin-bottom: 0;line-height: 43px;font-size: 36px;">INVOICE</h1>
                                                <p class="inv-code" style="margin-top: 0;line-height: normal;color: #aaa;">
                                                    #<?= $invoice->invoice_number; ?></p>
                                                <p class="inv-balance"
                                                   style="margin-top: 30px;line-height: 1;font-size: .9em;color: #555;margin-bottom: 0;">Balance Due</p>
                                                <h4 style="margin-top: 0;line-height: normal;font-weight: 600;font-size: 1.2em;">
                                                    Rp. <?= number_format($invoice->balance_due, 0); ?></h4>
                                            </div>
                                        </div>
                                        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
                                            <div class="col-md-4" style="width: 33.33333%;position: relative;float: left;display: block;">
                                                <div class="office-desc">
                                                    <h4 style="margin-top: 0;line-height: normal;font-weight: 600;font-size: 1em;margin-bottom: 0;">
                                                        <?= \App\Models\AuthToken::info()->organizationName; ?>
                                                    </h4>
                                                    <p style="margin-top: 0;line-height: normal;margin-bottom: 0;font-size: .83em;color: #aaa;">
                                                        Indonesia
                                                    </p>
                                                </div>
                                                <div class="billto" style="margin-top: 30px;">
                                                    <p style="margin-top: 0;line-height: normal;margin-bottom: 0;color: #aaa;font-size: .83em;">Bill
                                                        To</p>
                                                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                                                        <?= $invoice->contact->display_name ?? ''; ?>
                                                    </h4>
                                                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                                                        <?= $invoice->billing_address ?? ''; ?>
                                                    </h4>
                                                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                                                        <?= $invoice->billing_area['region_name'] ?? ''; ?>
                                                    </h4>
                                                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                                                        <?= $invoice->billing_area['district_name'] ?? ''; ?> <?= $invoice->billing_zip ?? ''; ?>
                                                    </h4>
                                                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                                                        <?= $invoice->billing_area['country_name'] ?? ''; ?>
                                                    </h4>
                                                    <h4 style="margin-top: 0;line-height: normal;font-weight: 100;font-size: .9em;margin: 0;color: #555;">
                                                        <?= $invoice->contact->phone ?? '-'; ?> <br>
                                                        <?= $invoice->contact->mobile ?? '-'; ?>
                                                    </h4>
                                                </div>
                                            </div>
                                            <div class="col-md-8" style="width: 66.66667%;position: relative;float: left;display: block;">
                                                <div class="col-md-9 col-md-offset-3 invoice-date"
                                                     style="width: 75%;position: relative;float: left;display: block;margin-left: 25%;margin-top: 70px;padding-right: 0;">
                                                    <div class="form-group">
                                                        <div class="col-md-6"
                                                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                                                            Invoice Date:
                                                        </div>
                                                        <div class="col-md-6 invoice-date-term"
                                                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                                                            <?= date('j F Y', strtotime($invoice->invoice_date)); ?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6"
                                                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                                                            Terms:
                                                        </div>
                                                        <div class="col-md-6 invoice-date-term"
                                                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                                                            Due on Receipt
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="col-md-6"
                                                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #aaa;padding-bottom: 5px;">
                                                            Due Date:
                                                        </div>
                                                        <div class="col-md-6 invoice-date-term"
                                                             style="width: 50%;position: relative;float: left;display: block;text-align: right;font-size: .83em;color: #555;padding-bottom: 5px;">
                                                            <?= date('j F Y', strtotime($invoice->due_date)); ?>
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
                                                     style="border-top: 1px solid #ddd;border-bottom: 1px solid #ddd;">
                                                    <table class="table table-hover zuragan-invoice-table-inside"
                                                           style="margin-bottom: 10px;width: 100%;max-width: 100%;background-color: transparent;border-collapse: collapse;border-spacing: 0;">
                                                        <thead>
                                                        <tr class="dark-grey-background" style="background: #333333;-webkit-print-color-adjust: exact;">
                                                            <th style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;border-bottom: 0px !important;">
                                                                #
                                                            </th>
                                                            <th id="item"
                                                                style="float:left;color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;border-bottom: 0px !important;">
                                                                Item & Description
                                                            </th>
                                                            <th id="qty"
                                                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                                                Qty
                                                            </th>
                                                            <th id="rate"
                                                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                                                Rate
                                                            </th>
                                                            <th id="discount"
                                                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                                                Discount
                                                            </th>
                                                            <th id="amount"
                                                                style="color: #fff;font-size: .9em;font-weight: 100;padding: 8px;line-height: 1.42857;vertical-align: top;text-align: right;border-bottom: 0px !important;">
                                                                Amount
                                                            </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php foreach ($invoice->invoice_details as $key => $value) { ?>
                                                            <tr style="border-bottom: 1px solid #ddd;">
                                                                <td style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                    <?= $key + 1; ?>
                                                                </td>
                                                                <td id="item"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                    <?= $value->item_name; ?>
                                                                </td>
                                                                <td id="qty"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                                                    <p class="qty-amount" style="margin-top: 0;line-height: normal;margin-bottom: 0;">
                                                                        <?= $value->item_quantity ?>
                                                                    </p>
                                                                    <p class="qty-type"
                                                                       style="margin-top: 0;line-height: normal;margin-bottom: 0;font-weight: 100;color: #888;">
                                                                        <?= $value->uom; ?>
                                                                    </p>
                                                                </td>
                                                                <td id="rate"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                                                    <?= 'Rp. ' . number_format($value->item_rate, 0); ?>
                                                                </td>
                                                                <td id="discount"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                                                    <?= ($value->discount_amount_type == 'fixed') ? 'Rp. ' . number_format($value->discount_amount_value, 0) : $value->discount_amount_value . "%"; ?>
                                                                </td>
                                                                <td id="amount"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;text-align: right;">
                                                                    <?= 'Rp. ' . number_format($value->amount, 0); ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                        <tr class="t_sub-total" style="border-bottom: 0px !important;text-align: right;">
                                                            <td colspan="4"
                                                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;">
                                                                Sub Total
                                                            </td>
                                                            <td colspan="2"
                                                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 100;border-top: 0;">
                                                                <?= 'Rp. ' . number_format($invoice->sub_total, 0); ?>
                                                            </td>
                                                        </tr>
                                                        <?php if (!$invoice->tax_included) { ?>
                                                            <tr class="t_tax" style="border-bottom: 0px !important;text-align: right;">
                                                                <td colspan="4"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                    Tax
                                                                </td>
                                                                <td colspan="1"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                    Ppn 10%
                                                                </td>
                                                                <td colspan="1"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                    <?= 'Rp. ' . number_format($invoice->tax, 0); ?>
                                                                </td>
                                                            </tr>

                                                        <?php } ?>
                                                        <?php if (!empty((int)$invoice->adjustment_value)) { ?>
                                                            <tr class="t_adjustment" style="border-bottom: 0px !important;text-align: right;">
                                                                <td colspan="4"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                    <?= !empty($invoice->adjustment_name) ? $invoice->adjustment_name : 'Adjustment'; ?>
                                                                </td>
                                                                <td colspan="2"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                    <?= 'Rp. ' . number_format($invoice->adjustment_value, 0); ?>
                                                                </td>
                                                            </tr>
                                                        <?php } ?>
                                                        <tr class="t_total" style="border-bottom: 0px !important;text-align: right;">
                                                            <td colspan="4"
                                                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                Total
                                                            </td>
                                                            <td colspan="2"
                                                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                                                <?= 'Rp. ' . number_format($invoice->total, 0); ?>
                                                            </td>
                                                        </tr>
                                                        <?php foreach ($invoice->payments as $key => $value) { ?>
                                                            <tr class="adjustment" style="border-bottom: 0px !important;text-align: right;">
                                                                <td colspan="4"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;"><?= "Payment on " . $value->formatted_date; ?></td>
                                                                <td colspan="2"
                                                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;"><?= 'Rp. ' . number_format($value->amount, 0); ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                        <tr class="balance-due" style="border-bottom: 0px !important;text-align: right;">
                                                            <td colspan="3" class="no-bgr"
                                                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                                                            <td colspan="1"
                                                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;background: #eeeeee;border-top: 0;-webkit-print-color-adjust: exact;">
                                                                Balance Due
                                                            </td>
                                                            <td colspan="2"
                                                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;background: #eeeeee;border-top: 0;-webkit-print-color-adjust: exact;">
                                                                <?= 'Rp. ' . number_format($invoice->balance_due, 0); ?>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
                                            <div class="col-md-12" style="width: 100%;position: relative;float: left;display: block;">
                                                <div class="zuragan-invoice-notes m-b-20" style="margin-bottom: 20px;">
                                                    <h3 style="font-size: 1.1em;color: #888;font-weight: 100;margin-top: 0;margin-bottom: 0;vertical-align: middle;line-height: 2;">
                                                        Customer Notes</h3>
                                                    <p style="margin-top: 0;line-height: normal;font-size: .83em;margin-bottom: 0;">
                                                        <?= $invoice->customer_notes ?? '-' ?>
                                                    </p>

                                                    <h3 style="font-size: 1.1em;color: #888;font-weight: 100;margin-top: 0;margin-bottom: 0;vertical-align: middle;line-height: 2;">
                                                        Term & Condition</h3>
                                                    <p style="margin-top: 0;line-height: normal;font-size: .83em;margin-bottom: 0;">
                                                        <?= $invoice->term_and_condition ?? '-' ?>
                                                    </p>
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
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                <?= !empty($invoice->adjustment_name) ? $invoice->adjustment_name : 'Adjustment'; ?>
                                </td>
                                <td colspan="2"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                    <?= 'Rp. ' . number_format($invoice->adjustment_value, 0); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="t_total" style="border-bottom: 0px !important;text-align: right;">
                            <td colspan="4"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                Total
                            </td>
                            <td colspan="2"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;">
                                <?= 'Rp. ' . number_format($invoice->total, 0); ?>
                            </td>
                        </tr>
                        <?php foreach ($invoice->payments as $key => $value) { ?>
                            <tr class="adjustment" style="border-bottom: 0px !important;text-align: right;">
                                <td colspan="4"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;"><?= "Payment on " . $value->formatted_date; ?></td>
                                <td colspan="2"
                                    style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;"><?= 'Rp. ' . number_format($value->amount, 0); ?></td>
                            </tr>
                        <?php } ?>
                        <tr class="balance-due" style="border-bottom: 0px !important;text-align: right;">
                            <td colspan="3" class="no-bgr"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;background: none;border-top: 0;-webkit-print-color-adjust: exact;"></td>
                            <td colspan="1"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;background: #eeeeee;border-top: 0;-webkit-print-color-adjust: exact;">
                                Balance Due
                            </td>
                            <td colspan="2"
                                style="padding: 8px;line-height: 1.42857;vertical-align: top;color: #555;font-size: .87em;font-weight: 600;background: #eeeeee;border-top: 0;-webkit-print-color-adjust: exact;">
                                <?= 'Rp. ' . number_format($invoice->balance_due, 0); ?>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row zuragan-invoice-content p-15" style="display: flex;padding: 15px;">
            <div class="col-md-12" style="width: 100%;position: relative;float: left;display: block;">
                <div class="zuragan-invoice-notes m-b-20" style="margin-bottom: 20px;">
                    <h3 style="font-size: 1.1em;color: #888;font-weight: 100;margin-top: 0;margin-bottom: 0;vertical-align: middle;line-height: 2;">
                        Customer Notes</h3>
                    <p style="margin-top: 0;line-height: normal;font-size: .83em;margin-bottom: 0;">
                        <?= $invoice->customer_notes ?? '-' ?>
                    </p>

                    <h3 style="font-size: 1.1em;color: #888;font-weight: 100;margin-top: 0;margin-bottom: 0;vertical-align: middle;line-height: 2;">
                        Term & Condition</h3>
                    <p style="margin-top: 0;line-height: normal;font-size: .83em;margin-bottom: 0;">
                        <?= $invoice->term_and_condition ?? '-' ?>
                    </p>
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