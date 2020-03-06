<?php

namespace App\Services;
use Phalcon\DI\FactoryDefault as DI;

/**
 * Class ServiceException
 *
 * Runtime exception which is generated on the service level. It signals about an error in business logic.
 *
 * @package App\Exceptions
 */
class ServiceException extends \RuntimeException
{
    public function __construct(string $message = '', $code = 0,\Throwable $e = null, $suppress_logs = false) {
        $di = DI::getDefault();
        if(!$suppress_logs) {
            $di->getLogger()->critical(
                $code . ' ' . $message
            );
        }

        return parent::__construct($message, $code,$e);
    }
}
