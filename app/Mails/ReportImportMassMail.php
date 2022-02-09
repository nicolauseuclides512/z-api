<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */


namespace App\Mails;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportImportMassMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $subject = "Report Import Item Mass";

    const STATUS_EXCEPTION = -1;

    private $user, $data, $status;

    public static function inst($user, $data, $status = 0)
    {
        return new self($user, $data, $status);
    }

    public function __construct($user, $data, $status = 0)
    {
        $this->user = $user;
        $this->data = $data;
        $this->status = $status;
    }

    public function build()
    {
        return $this
            ->view('emails.item.report_import_mass')
            ->with([
                'status' => $this->status,
                'user' => $this->user,
                'data' => $this->data
            ]);
    }

}