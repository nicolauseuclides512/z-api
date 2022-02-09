<?php

namespace App\Jobs;

use App\Utils\MailUtil;
use Illuminate\Support\Facades\Log;
use League\Flysystem\Exception;

class SendEmailJob extends Job
{

    protected $recipient, $cc, $bcc, $subject, $message, $attachment, $from, $sendToMe = true;

    public function __construct($recipient = '',
                                $cc = '',
                                $bcc = '',
                                $subject = '',
                                $message = '',
                                $from = '',
                                $attachment = '',
                                $sendToMe = true)
    {
        $this->recipient = $recipient;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->subject = $subject;
        $this->message = $message;
        $this->from = $from;
        $this->attachment = $attachment;
        $this->sendToMe = $sendToMe;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        try {
            Log::info('send mail job to ' . $this->recipient . ' started');

            $mail = MailUtil::send(
                $this->recipient,
                $this->cc,
                $this->bcc,
                $this->subject,
                $this->message,
                $this->from,
                $this->attachment,
                $this->sendToMe);

            if (!$mail)
                Log::info("Send Email Job to " . $this->recipient . " Failed.");

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            throw $e;
        }
    }
}
