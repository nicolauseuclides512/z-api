<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class UpdateEmailTemplateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')
            ->where('key', 'web.template.notification.email.resend_verification')
            ->update([
                'value' =>
                    '{"en":"Dear %salutation% %name%\n\nThankyou for your payment.\nHerewith payment receipt according to your payment at %date order%.\n\nInvoice\tNumber\t: %invoice_number%\nBank \t\t\t: %banks_name%\nNomor Rekening  : %account_number%\nNama\t\t\t: %name%\n\nFeel free to contact us anytime to get information of your order.\n\nRegards,\n\nzuragan.com.","id":"Yth. %salutation% %name%\n\nSalam sejahtera,\nTerima kasih sudah melakukan pembayaran.\nBerikut kami sertakan receipt sesuai dengan order dan pembayaran yang sudah Anda lakukan.\n\nNomor Invoice\t: %invoice_number%\nBank \t\t\t: %bank_name%\nNomor Rekening  : %account_number%\nNama\t\t\t: %name%\n\nSilakan menghubungi kami kapan saja untuk mendapatkan informasi mengenai order Anda.\n\nSalam hangat,\n\n\nzuragan.com."}'
            ]);

        DB::table('settings')
            ->where('key', 'web.template.notification.email.invoice')
            ->update([
                'value' =>
                    '{"en":"Dear %salutation% %name%\n\nThankyou for your order through %portal_name%.zuragan.com\nYour invoice number is %invoice_number%.\t\nHerewith invoice according to your order at %date_order%.\n\nPlease complete and CONFIRM your payment to fulfill your order.\nFeel free to contact us anytime to get information of your order.\n\nRegards,\n\nzuragan.com.","id":"Yth. %salutation% %name%\n\nSalam,\nTerima kasih atas order anda melalui %organization name%\nNomor invoice anda adalah %invoice number%.\nBerikut kami sertakan invoice order sesuai dengan order yang anda lakukan pada %date order%.\n\nSilakan menghubungi kami kapan saja untuk mendapatkan informasi mengenai order Anda.\n\n\nSalam hangat,\n\n%Organization Name%"}'
            ]);

        DB::table('settings')
            ->where('key', 'web.template.notification.email.payment_receipt')
            ->update([
                'value' =>
                    '{"en":"Dear %salutation% %name%\n\nThankyou for your payment.\nHerewith payment receipt according to your payment at %date order%.\n\nInvoice\tNumber\t: %invoice_number%\nBank \t\t\t: %banks_name%\nNomor Rekening  : %account_number%\nNama\t\t\t: %name%\n\nFeel free to contact us anytime to get information of your order.\n\nRegards,\n\nzuragan.com.","id":"Yth. %salutation% %name%\n\nSalam sejahtera,\nTerima kasih sudah melakukan pembayaran.\nBerikut kami sertakan receipt sesuai dengan order dan pembayaran yang sudah Anda lakukan.\n\nNomor Invoice\t: %invoice_number%\nBank \t\t\t: %bank_name%\nNomor Rekening  : %account_number%\nNama\t\t\t: %name%\n\nSilakan menghubungi kami kapan saja untuk mendapatkan informasi mengenai order Anda.\n\nSalam hangat,\n\n\nzuragan.com."}'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
