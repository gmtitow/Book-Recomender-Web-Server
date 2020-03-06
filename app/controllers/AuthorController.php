<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class AuthorController
 * Контроллер для работы непосредственно с книгами.
 *
 * @url author
 */
class AuthorController extends AbstractController
{
    /**
     * @url find
     * @method POST
     * @access public
     *
     * @params query string
     * @params genre_id int
     *
     * @params page int
     * @params page_size int
     *
     */
    public function getAuthorsAction() {
        $expectation = [
            'query' => [
                'type'=>'string',
                'default'=>''
            ],
            'genre_id' => [
                'type'=>'int'
            ],
            'page' => [
                'type'=>'int',
                'default' => 1
            ],
            'page_size' => [
                'type'=>'int',
                'default'=> 10
            ],
        ];

        $data = self::getInput('POST',$expectation);

        try {
            $filters = [];

            if (isset($data['genre_id']))
                $filters['genres'] = array($data['genre_id']);

            $authors = $this->authorService->getAuthors($data['query'], $filters, $data['page'], $data['page_size']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successPaginationResponse('All ok', $authors['data'], $authors['pagination']);
    }
}

