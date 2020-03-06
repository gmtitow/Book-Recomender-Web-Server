<?php


namespace App\services;


use App\library\SphinxSupport;
use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;
use App\Models\Authors;
use App\Models\Books;
use App\Models\Files;
use App\Models\Genres;
use App\Views\AuthorView;
use App\Views\BookView;

class FileService extends AbstractService
{

    const ADDED_CODE_NUMBER = 6000;
    const ERROR_FILE_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;

    public function returnFileToClient(int $bookId, string $ext = 'txt') {
        $file = Files::findForBook($bookId,$ext);

        if (count($file)< 1) {
            throw new ServiceException("File not found", self::ERROR_FILE_NOT_FOUND);
        }

        $file = $file[0];

        if (file_exists($file['path_to'])) {
            SupportClass::file_force_download($file['path_to']);
        } else {
            throw new ServiceException("File not found", self::ERROR_FILE_NOT_FOUND);
        }
    }
}