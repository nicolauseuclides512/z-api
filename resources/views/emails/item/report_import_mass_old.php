<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
</head>
<body style="font-size: 12pt;">
<br/>
<div style="font-size: 13pt;"><b>Report Import Item Mass</b></div>

<div style="border-bottom: 1px solid #dddddd; margin-top: 10px;margin-bottom: 10px;"></div>

<div class="info" style="text-transform: capitalize">
    <div style="padding-top: 5px;padding-bottom: 5px;" class="date">Date : <i><?= \Carbon\Carbon::now(); ?></i>.
    </div>
    <div class="organization" style="padding-top: 5px;padding-bottom: 5px;">
        Organization : <?= $user->organizationName; ?>.
    </div>
    <div style="padding-top: 5px;padding-bottom: 5px;" class="user">User PIC : <?= $user->username; ?>.</div>
    <div style="padding-top: 5px;padding-bottom: 5px;" class="status">
        Status: <?= $status == -1 ? "Error Exception" : "OK"; ?>.
    </div>
</div>

<br/>
<div><b>Result :</b></div>
<br/>
<?php if ($status != -1) { ?>
    <div class="error_result">
        <div class="total">
            Import: <?= $data['total']; ?>,
            <span style="color: green;">Success: <?= $data['success']; ?></span>
            ,
            <span style="color: red;">Failure: <?= $data['failure']; ?></span>
            .
        </div>
        <br/>
        <?php if ($data['failure'] > 0) { ?>
            <table style="font-size: 11pt" width="100%" border="1" cellpadding="2" cellspacing="0">
                <tr>
                    <th class="number">#</th>
                    <th class="item">Item Name</th>
                    <th class="error">Error Message</th>
                </tr>
                <?php foreach ($data['result'] as $k => $value) { ?>
                    <tr>
                        <td class="number" align="center"><?= $k + 1; ?></td>
                        <td class="item"><?= $value['item_name']; ?></td>
                        <td class="error">
                            <?php foreach ($value['messages'] as $messages) {
                                foreach ($messages as $msg) {
                                    echo "*$msg <br/>";
                                }
                            } ?>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        <?php } else {
            echo "<div>Your items has been added.</div>";
        } ?>
    </div>
<?php } else { ?>
    <div class="error_exception">
        <div class="error_message"><?= $data['message']; ?>.</div>
    </div>
<?php } ?>

<br>
<br>
<div class="footer" style="font-size: 9pt;color: #C4C4C4; text-align: center">
    <a href="https://zuragan.com">Powered by Zuragan.</a>
</div>
<br>
<br>

</body>
</html>
