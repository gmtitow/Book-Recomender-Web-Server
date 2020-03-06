<?php

namespace App\Controllers\HttpExceptions;

use App\Controllers\AbstractHttpException;

/**
 * Class Http400Exception
 *
 * Execption class for Bad Request Error (400)
 *
 * @package App\Lib\Exceptions
 */
class Http403Exception extends AbstractHttpException
{
    protected $httpCode = 403;
    protected $httpMessage = 'Forbidden';
}
