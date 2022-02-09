<?php
return [
    'notification' => [
        'email' => [
            'forgot_password' => [
                'en' =>
<<<EOT
Dear %salutation% %name%

We have received your request to reset your password. Please click on the link below to setup a new password.

%link%

Delete this email immediately if you did not request to set up a new password.
Your phone number, email or password are confidential.
Do not share any information of your account to anyone.

Thank you,

zuragan.com
EOT
                ,
                'id' =>
<<<EOT
Yth. %salutation% %name%

Kami telah menerima permintaan Anda untuk mengatur ulang password zuragan.com. Silakan  klik tombol di bawah ini untuk mengatur ulang password Anda.

%link%

Segera hapus e-mail ini jika Anda tidak pernah meminta untuk mengatur ulang password.
Segala bentuk informasi seperti nomor kontak, alamat e-mail, atau password kamu bersifat rahasia. 
Jangan menginformasikan data-data tersebut kepada siapa pun, termasuk kepada pihak yang mengatasnamakan Totokoko.


Terima kasih,


Totokoko.com
EOT
            ],
            'payment_receipt' => [
                'en' =>
<<<EOT
Dear %salutation% %name%

Thankyou for your payment.
Herewith payment receipt according to your payment at %date order%.

Invoice	Number	: %invoice_number%
Bank 			: %banks_name%
Nomor Rekening  : %account_number%
Nama			: %name%

Feel free to contact us anytime to get information of your order.

Regards,

zuragan.com

Please ignore this email if you did not registered an account through Totokoko.
EOT
                ,
                'id' =>
<<<EOT
Yth. %salutation% %name%

Salam sejahtera,
Terima kasih sudah melakukan pembayaran.
Berikut kami sertakan receipt sesuai dengan order dan pembayaran yang sudah Anda lakukan.

Nomor Invoice	: %invoice_number%
Bank 			: %bank_name%
Nomor Rekening  : %account_number%
Nama			: %name%

Silakan menghubungi kami kapan saja untuk mendapatkan informasi mengenai order Anda.

Salam hangat,


zuragan.com
EOT
            ],
            'invoice' => [
                'en' =>
<<<EOT
Dear %salutation% %name%

Thankyou for your order through %portal_name%.zuragan.com
Your invoice number is %invoice_number%.	
Herewith invoice according to your order at %date_order%.

Please complete and CONFIRM your payment to fulfill your order.
Feel free to contact us anytime to get information of your order.

Regards,

zuragan.com
EOT
                ,
                'id' =>
<<<EOT
Yth. %salutation% %name%

Salam,
Terima kasih atas order anda melalui %organization name%
Nomor invoice anda adalah %invoice number%.
Berikut kami sertakan invoice order sesuai dengan order yang anda lakukan pada %date order%.

Silakan menghubungi kami kapan saja untuk mendapatkan informasi mengenai order Anda.


Salam hangat,

%Organization Name%
EOT
            ],
            'resend_verification' => [
                'en' =>
<<<EOT
RESEND EMAIL VERIFICATION

Dear %salutation% %name%,

You received this email because you request to resend your email verification.
Ignore this email if you did not request an email verification.

Please click the link below and verify that the email address you registered belongs to you.

%link%

We look forward to see you at Totokoko App. Thank you!

Regards,

zuragan.com
EOT
                ,
                'id' =>
<<<EOT
PERMINTAAN ULANG VERIFIKASI EMAIL

Yth. %salutation% %name%,

Anda melakukan permintaan ulang untuk melakukan verifikasi email.
Abaikan email ini jika anda merasa tidak pernah meminta untuk memverfikasi ulang email anda.

Silakan melakukan verifikasi email Anda dengan menekan tombol di bawah ini.

%link%

Kami menantikan kehadiran Anda di Totokoko App. Terima kasih!


Salam hangat,

zuragan.com
EOT
            ],
        ]
    ],
    'term_and_conditions' =>
<<<EOT
Syarat dan ketentuan berlaku
EOT
];
