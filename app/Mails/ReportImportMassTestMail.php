<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Mails;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportImportMassTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build()
    {
        return $this
            ->view('emails.item.test_email_template');
    }

}