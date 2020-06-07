<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Models\Genres;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;

/**
 * Class GenreController
 * Контроллер для работы с жанрами.
 *
 * @url genre
 */
class GenreController extends AbstractController
{
    /**
     * @url get
     * @method GET
     * @access public
     *
     */
    public function getGenresAction() {

        try {
            $genres = Genres::find(['columns'=>'genre_id, genre_name','order'=>'genre_id desc']);
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

        return self::successPaginationResponse('All ok', $genres->toArray());
    }
}

