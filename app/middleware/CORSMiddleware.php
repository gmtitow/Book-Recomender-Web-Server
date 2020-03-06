<?php
namespace App\Middleware;

use Phalcon\Events\Event;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\MiddlewareInterface;

/**
 * CORSMiddleware
 *
 * CORS checking
 */
class CORSMiddleware implements MiddlewareInterface
{
    /**
     * Before anything happens
     *
     * @param Event $event
     * @param Micro $application
     *
     * @returns bool
     */
    public function beforeHandleRoute(Event $event, Micro $application)
    {  
        if ($application->request->getHeader('ORIGIN')) {
            $origin = $application->request->getHeader('ORIGIN');
        } else {
            $origin = '*';
        } 
        $application
            ->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader(
                'Access-Control-Allow-Methods',
                'GET, PUT, POST, DELETE, OPTIONS, CONNECT, HEAD, PURGE, PATCH'
            )
            ->setHeader(
                'Access-Control-Allow-Headers',
                'Content-type'
            ) 
            ->setHeader(
                'Access-Control-Max-Age',
                '1000'
            ) 
            ->setHeader('Access-Control-Allow-Credentials', 'true');

        /*if ($application->request->isMethod('OPTIONS'))
        {
            $headers = [
                'Access-Control-Allow-Origin'      => '*',
                'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
                'Access-Control-Allow-Credentials' => 'true',
                'Access-Control-Max-Age'           => '86400',
                'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With'
            ];
            return $application
                ->response->json('{"method":"OPTIONS"}', 200, $headers);
        }*/
    }

    /**
     * Calls the middleware
     *
     * @param Micro $application
     *
     * @returns bool
     */
    public function call(Micro $application)
    {
        return true;
    }
}



