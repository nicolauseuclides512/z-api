<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;

class AppException extends Exception
{
    protected $cause = null;

    // Redefine the exception so message isn't optional
    public function __construct($message, $code = 0, $cause = null)
    {
        // some code
        $this->cause = $cause;

        // make sure everything is assigned properly
        parent::__construct($message, $code);
    }

    // custom string representation of object
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message} {$this->cause}\n";
    }

    public function getCause()
    {
        if (method_exists($this->cause, 'toArray')) {
            $messages = $this->cause->toArray();
            return $messages;
        }
        return $this->cause;
    }

    public static function inst($message, $code = 500, $cause = null, Exception $previous = null)
    {
        return new self($message, $code, $cause);
    }

    public static function flash($code = 500, $message, $cause = null, Exception $previous = null)
    {
        return new self($message, $code, $cause);
    }

    public static function bad($message,
                               $cause = null,
                               $code = Response::HTTP_BAD_REQUEST,
                               Exception $previous = null)
    {
        return new self($message, $code, $cause);
    }

    public static function unprocessed($message,
                                       $cause = null,
                                       $code = Response::HTTP_UNPROCESSABLE_ENTITY,
                                       Exception $previous = null)
    {
        return new self($message, $code, $cause);
    }

    public static function internal($message,
                                    $cause = null,
                                    $code = Response::HTTP_INTERNAL_SERVER_ERROR,
                                    Exception $previous = null)
    {
        return new self($message, $code, $cause);
    }
}
