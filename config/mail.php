<?php
/**
 * @author Jehan Afwazi Ahmad <jehan.afwazi@gmail.com>.
 */

return [
    'driver' => env('MAIL_DRIVER', 'smtp'),
    'host' => env('MAIL_HOST', 'smtp.gmail.com'),
    'port' => env('MAIL_PORT', '587'),
    'username' => env('MAIL_USERNAME', 'mailtestdebug@gmail.com'),
    'password' => env('MAIL_PASSWORD', 'localhost123'),
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
    'address' => env('MAIL_ADDRESS', 'mailtestdebug@gmail.com'),
    'name' => env('MAIL_NAME', 'mail_dummy'),
    'from' => ['address' => 'mailtestdebug@gmail.com', 'name' => 'dummy']
];