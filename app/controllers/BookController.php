<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Libs\SupportClass;
use App\Models\BooksFiles;
use App\Models\Reviews;
use App\Services\BookListsService;
use App\services\BookService;
use App\services\FileService;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use App\Views\BookView;
use App\Models\Files;

/**
 * Class BookController
 * Контроллер для работы непосредственно с книгами.
 *
 * @url book
 */
class BookController extends AbstractController
{
    /**
     * @url find
     * @method POST
     * @access public
     *
     * @params author_id int
     * @params genre_id int
     * @params query string = ''
     *
     * @params book_ids array of int
     *
     * @params page int
     * @params page_size int
     *
     */
    public function getBooksAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'author_id' => [
                    'type' => 'int',
                ],
                'genre_id' => [
                    'type' => 'int',
                ],
                'query' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'book_ids' => [
                    'type' => 'array',
                    'sub_data' => [
                        'type' => 'int',
                    ],
                ],
                'page' => [
                    'type' => 'int',
                ],
                'page_size' => [
                    'type' => 'int',
                ],
            ];

            $data = self::getInput('POST', $expectation, null, false);
        }
        //END GENERATED VALIDATION

        try {
            $filters = [];

            if (isset($data['genre_id']))
                $filters['genres'] = array($data['genre_id']);

            if (isset($data['author_id']))
                $filters['authors'] = array($data['author_id']);

            if (isset($data['book_ids']))
                $filters['book_ids'] = $data['book_ids'];

            $books = $this->bookService->getBooks($data['query'], $filters, $data['page'], $data['page_size']);

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

        return self::successPaginationResponse('All ok', $books['data'], $books['pagination']);
    }

    /**
     * @url recommends
     * @method POST
     * @access private
     *
     * @params genre_id int
     * @params query string = ''
     *
     * @params! list_id int
     *
     * @params page int = 1 >= 1
     * @params page_size int = 10
     *
     */
    public function getRecommendsAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'genre_id' => [
                    'type' => 'int',
                ],
                'query' => [
                    'type' => 'string',
                    'default' => '',
                ],
                'list_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'page' => [
                    'type' => 'int',
                    'min' => 1,
                    'default' => 1,
                ],
                'page_size' => [
                    'type' => 'int',
                    'default' => 10,
                ],
            ];

            $data = self::getInput('POST', $expectation, null, false);
        }
        //END GENERATED VALIDATION

        try {

            $list = $this->bookListsService->getBookListById($data['list_id']);

            if ($list->getUserId() != $this->getUserId())
                self::returnPermissionException();

            $filters = [];

            if (isset($data['genre_id']))
                $filters['genres'] = array($data['genre_id']);

            $books = $this->bookService->getRecommends($data['list_id'], $filters, $data['page'], $data['page_size']);

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

        return self::successPaginationResponse('All ok', $books['data'], $books['pagination']);
    }

    /**
     * @url /form-recommends
     * @method POST
     * @access private
     *
     * @params! list_id
     */
    public function formNewRecommendationListAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'list_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
            ];

            $data = self::getInput('POST', $expectation, null, false);
        }
        //END GENERATED VALIDATION
        try {
            $list = $this->bookListsService->getBookListById($data['list_id']);

            if ($list->getUserId() != $this->getUserId())
                self::returnPermissionException();

            $this->bookService->formNewRecommendationList($data['list_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case BookListsService::ERROR_BOOK_LIST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return $this->successResponse('All successfully done');
    }

    /**
     * @url get/info
     *
     * @method GET
     * @access public
     *
     * @params! book_id int
     *
     */
    public function getBookInfoAction()
    {
        $expectation = [
            'book_id' => [
                'type' => 'int',
                'is_require' => true
            ],
        ];

        $data = self::getInput('GET', $expectation);

        try {

            $book = $this->bookService->getBookById($data['book_id']);

            $reviews = Reviews::findForBook($data['book_id'], 1, 10);

            $exists = null;
            if ($this->isAuthorized()) {
                $userId = $this->getUserId();
                $exists = Reviews::checkReviewExists($userId, $data['book_id']);
            }

            $handledBook = $this->bookService->handleBookInfo($book->toArray(), $reviews, $exists);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case BookService::ERROR_BOOK_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successPaginationResponse('All ok', $handledBook);
    }

    /**
     * @url download/file/txt
     *
     * @method GET
     * @access public
     *
     * @params! book_id int
     *
     */
    public function downloadFileAction()
    {
        $expectation = [
            'book_id' => [
                'type' => 'int',
                'is_require' => true
            ],
        ];

        $data = self::getInput('GET', $expectation);

        try {

            $this->bookService->getBookById($data['book_id']);
            $this->fileService->returnFileToClient($data['book_id']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case FileService::ERROR_FILE_NOT_FOUND:
                case BookService::ERROR_BOOK_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }
    }

    /**
     * @url change/paths
     *
     * @method GET
     * @access public
     *
     * @params! book_id int
     *
     */
    public function changePathsAction()
    {

        $CHARS_DELETED_FROM_FILES = ['\\', '/', ':', '*', '?', '<', '>', '|', '.', '"', '\'', '-'];
        $CHARS_DELETED_FROM_FILES = ['\\', '/', ':', '*', '?', '<', '>', '|', '.', '"', '\''];
        $page = 1;
        $page_size = 50;
        $end = false;

        while (!$end) {
//            $files = SupportClass::executeWithPagination(
//                "select f.*,b.name as book_name,b.book_id,a.full_name as author_name,a.author_id
//                            from files f inner join books_files using(file_id) inner join books b using(book_id) inner join authors a using (author_id)
//                            order by f.file_id asc",[],
//                $page,$page_size);

            $files = SupportClass::executeWithPagination(
                "select *
                            from files
                            order by file_id asc", [],
                $page, $page_size);

            if (count($files['data']) < $page_size)
                $end = true;

            foreach ($files['data'] as $file) {
//                $new_path = 'E:/OpenServer/domains/parser-books/books/';

//                $dir_author = 'E://OpenServer/domains/parser-books/books/' . trim(str_replace($CHARS_DELETED_FROM_FILES,'',$file['author_name']));
//
//                if(strlen($dir_author) >= 256) {
//                    $dir_author = 'E://OpenServer/domains/parser-books/books/' . trim($file['author_id']);
//                }
//
//                $book_name_in_file_system = trim(str_replace($CHARS_DELETED_FROM_FILES,'',$file['book_name']));
//                $dir_book = $dir_author . '/' . $book_name_in_file_system;
//
//                if(strlen($dir_book) >= 256) {
//                    $dir_book = $dir_author . '/' . trim($file['book_id']);
//                }
//
//                if(!file_exists($dir_book.'/'.$file['full_name'])) {
//                    return $this->successResponse("Новый путь для файла невалиден",
//                        ['page'=>$page,'page_size'=>$page_size,'full_path'=>$dir_book.'/'.$file['full_name'],'file_id'=>$file['file_id']]);
//                }
//
//                SupportClass::execute("update files set path_to = :new_path where file_id = :file_id",
//                    ['new_path'=>$dir_book, 'file_id'=>$file['file_id']]);

                $path_to = $file['path_to'];

                $path_to[0] = 'E';

                SupportClass::execute("update files set path_to = :new_path where file_id = :file_id",
                    ['new_path' => $path_to, 'file_id' => $file['file_id']]);
            }

            $page += 1;
        }


        return self::successResponse('All ok');
    }

    /**
     * @url add/image
     *
     * @method POST
     * @access moderator
     *
     * @params! book_id int
     * @params! image_name string
     *
     */
    public function setImageAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'book_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'image_name' => [
                    'type' => 'string',
                    'is_require' => true,
                ],
            ];

            $data = self::getInput('POST', $expectation, null, false);
        }
        //END GENERATED VALIDATION

        $this->db->begin();
        $book = $this->bookService->getBookById($data['book_id']);

        $file = new Files();

        $image_name = pathinfo($data['image_name'],PATHINFO_BASENAME);
        $extension = pathinfo($data['image_name'],PATHINFO_EXTENSION);
        $file->setName($image_name);
        $file->setExtension($extension);
        $file->setPathTo('/public/images/'.$data['image_name']);
        $file->setFullName($data['image_name']);

        if (!$file->create())
            throw new Http500Exception("Something wrong, unable create file");

        $book->setCoverFileId($file->getFileId());

        if (!$book->update())
            throw new Http500Exception("Something wrong, unable update book");

        $this->db->commit();

        return self::successResponse('All ok');
    }
}

