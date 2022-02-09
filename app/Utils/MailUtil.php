<?php
/**
 * @author Jehan Afwazi Ahmad <jee.archer@gmail.com>.
 */

namespace App\Utils;

use App\Exceptions\AppException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;

class MailUtil
{
    /**
     * @param string $to
     * @param string $cc
     * @param string $bcc
     * @param string $subject
     * @param string $message
     * @param string $from
     * @param array $attachments
     * @param bool $sendToMe
     * @return bool
     * @throws \Exception
     */
    public static function send($to = '',
                                $cc = '',
                                $bcc = '',
                                $subject = '',
                                $message = '',
                                $from = '',
                                $attachments = [],
                                $sendToMe = true)
    {
        try {

            $mail = new PHPMailer;
            $mail->SMTPDebug = 4;
            $mail->IsSMTP();
            $mail->isHTML(true);
            $mail->CharSet = env('MAIL_CHARSET', 'utf-8');
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = env('MAIL_SMTP_AUTH', true);
            $mail->Username = env('MAIL_USERNAME', 'mailtestdebug@gmail.com');
            $mail->Password = env('MAIL_PASSWORD', 'localhost123');
            $mail->SMTPSecure = env('MAIL_SMTP_SECURE', 'tls');
            $mail->Port = env('MAIL_PORT', 587);
            $mail->WordWrap = env('MAIL_WORD_WRAP', 50);
            $mail->From = env('MAIL_FROM', 'noreply@zuragan.com');
//            $mail->From = env('MAIL_FROM', 'mailtestdebug@gmail.com');
//            $mail->FromName = $from ? explode('@', $from)[0] : env('FROM_NAME', 'Zuragan');
            $mail->FromName = $from ?? env('FROM_NAME', 'Zuragan');
            $mail->addReplyTo($from);

            $recipient = explode(',', preg_replace('/\s+/', '', $to));
            if ($sendToMe) {
                array_push($recipient, $from);
            }

            foreach ($recipient as $k => $v) {
                $name = explode('@', $v)[0];
                $mail->addAddress($v, $name);
            }

            if (!empty($cc)) {
                $recipientCc = explode(',', preg_replace('/\s+/', '', $cc));
                foreach ($recipientCc as $k => $v) {
                    $name = explode('@', $v)[0];
                    $mail->addCC($v, $name);
                }
            }

            if (!empty($bcc)) {
                $recipientBcc = explode(',', preg_replace('/\s+/', '', $bcc));
                foreach ($recipientBcc as $k => $v) {
                    $name = explode('@', $v)[0];
                    $mail->addBCC($v, $name);
                }
            }

            $mail->Subject = $subject;
            $mail->Body = $message;

            if (!empty($attachments)) {
                foreach ($attachments as $k => $v) {
                    if (!is_file($v['url'])) {
                        $mail->addStringAttachment(file_get_contents($v['url']), $v['name']);
                    } else {
                        $mail->AddAttachment($v['url'], $v['name']);
                    }
                }
            }

            if (!$mail->send()) {
                Log::error(
                    'code => 500 ' .
                    'Message could not be sent to . caused ' . $mail->ErrorInfo);

                throw AppException::inst(
                    'Message could not be sent to',
                    Response::HTTP_INTERNAL_SERVER_ERROR,
                    $mail->ErrorInfo);
            }

            Log::info('send email to ' . $to . ' Done.');

            return true;

        } catch (\Exception $e) {
            Log::error('code => 500 ' . 'Message could not be sent. caused ' . $e->getMessage());
            throw $e;
        }
    }
}