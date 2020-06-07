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
//            if (NGINX_ENABLED) {
//
//                header('X-Accel-Redirect: ' . $file['path_to']);
//                exit;
//            }
            if (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Length: ' . filesize($file['path_to']));
            header('Content-Type: ' . 'txt');
            header('Content-Disposition: attachment; filename="' . $file['full_name'].'"');
            header('Access-Control-Max-Age: '. '86400');
            readfile($file['path_to']);
            exit;
        } else {
            throw new ServiceException("File not found", self::ERROR_FILE_NOT_FOUND);
        }
    }

    public function returnToClientFileRawBody(Files $file) {
        if (NGINX_ENABLED) {
//            header('Content-Type: ' . $file->getMimeType());

            header('X-Accel-Redirect: ' . $file->getFullName());
            exit;
        } else if (APACHE_XSENDFILE_ENABLED){
//            $path = $this->relativePathIntoAbsolute($file->getFullPath());
//            header('X-SendFile: ' . realpath($path));
        } else {
//            if (ob_get_level()) {
//                ob_end_clean();
//            }
//
//            $path = $this->relativePathIntoAbsolute($file->getFullPath());
//            header('Content-Length: ' . filesize($path));
//            header('Content-Type: ' . $file->getMimeType());
//            header('Content-Disposition: attachment; filename=' . $file->getCurrentName());
//            header('Access-Control-Allow-Methods: '. 'GET, PUT, POST, DELETE, OPTIONS, CONNECT, HEAD, PURGE, PATCH');
//            header('Access-Control-Allow-Headers: '. 'Content-Type, Authorization, X-Requested-With');
//            header('Access-Control-Max-Age: '. '86400');
//            header('Access-Control-Allow-Credentials: '. 'true');
//            header('Access-Control-Allow-Origin: '. '*');
//            readfile($path);
//            exit;
        }

//        header('Content-Type: ' . $file->getMimeType());
//        header('Content-Disposition: attachment; filename=' . $file->getCurrentName());
        exit;
    }
}