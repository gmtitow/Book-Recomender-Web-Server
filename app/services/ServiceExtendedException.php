<?php

namespace App\Services;
use Phalcon\DI\FactoryDefault as DI;
use phpDocumentor\Configuration\Merger\Annotation\Replace;

/**
 * Class ServiceExtendedException
 *
 * Runtime exception which is generated on the service level.
 *
 * @package App\Exceptions
 */
class ServiceExtendedException extends ServiceException
{
    //Some added data
    private $data = null;

    public function __construct(string $message = '', int $code = 0, \Throwable $e = null, $logger = null, $data = null) {
        $this->data = $data;
        $di = DI::getDefault();
        if($data!=null) {
            $di->getLogger()->critical(
                $code . ' "' . $message . '" errors:' . var_export($data, true)
            );
            return parent::__construct($message, $code,$e,true);
        } else
            return parent::__construct($message, $code,$e);
    }

    public function getData(){
        return $this->data;
    }
}
