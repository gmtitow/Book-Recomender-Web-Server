<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Models\BookLists;
use App\Models\BookListsBooks;
use App\Models\Reviews;
use App\Services\BookListsService;
use App\services\ReviewService;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use App\views\ReviewView;

/**
 * Class BookListController
 *
 * @url book-list
 */
class BookListController extends AbstractController
{
    /**
     * Добавляет список книг
     *
     * @url add
     *
     * @access private
     * @method POST
     *
     * @params !list_name string
     *
     */
    public function addBookListAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'list_name' => [
                    'type' => 'string',
                    'is_require' => true,
                ],
            ];

            $data = self::getInput('POST', $expectation, null, false);
        }
        //END GENERATED VALIDATION


        $data['user_id'] = self::getUserId();
        $this->db->begin();
        try {

            $bookList = $this->bookListsService->createBookList($data);
            $bookList = $this->bookListsService->getBookListById($bookList->getListId());

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_UNABLE_CREATE_BOOK_LIST:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('book list was successfully created', $bookList->toArray());
    }

    /**
     * Изменяет список книг
     *
     * @url edit
     *
     * @access private
     * @method PUT
     *
     * @params! list_id int
     * @params! list_name string
     *
     */
    public function editBookListAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'list_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'list_name' => [
                    'type' => 'string',
                    'is_require' => true,
                ],
            ];

            $data = self::getInput('PUT', $expectation, null, false);
        }
        //END GENERATED VALIDATION


        $this->db->begin();
        $userId = $this->getUserId();
        try {
            $bookList = $this->bookListsService->getBookListById($data['list_id']);

            if ($bookList->getUserId() != $userId) {
                self::returnPermissionException();
            }

            $bookList = $this->bookListsService->changeBookList($bookList,$data);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_UNABLE_CHANGE_BOOK_LIST:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_BOOK_LIST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Book list was successfully changed', $bookList->toArray());
    }

    /**
     * Удаляет список книг
     *
     * @url delete
     *
     * @access private
     * @method DELETE
     *
     * @params! list_id int
     *
     */
    public function deleteBookListAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'list_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
            ];

            $data = self::getInput('DELETE', $expectation, null, false);
        }
        //END GENERATED VALIDATION

        $userId = self::getUserId();
//        $data['user_id'] = $userId;
        $this->db->begin();
        try {
            $bookList = $this->bookListsService->getBookListById($data['list_id']);

            if ($bookList->getUserId() != $userId) {
                self::returnPermissionException();
            }

            $this->bookListsService->deleteBookList($bookList);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_UNABLE_DELETE_BOOK_LIST:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_BOOK_LIST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Book list was successfully deleted');
    }

    /**
     * возвращает списки книг
     *
     * @url get
     *
     * @access private
     * @method GET
     *
     */
    public function getBookListsAction()
    {
        $userId = self::getUserId();
        $this->db->begin();
        try {
            $bookList = BookLists::findByUserId($userId);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_UNABLE_DELETE_BOOK_LIST:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_BOOK_LIST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Book list was successfully deleted', $bookList->toArray());
    }

    /**
     * Добавляет книгу в список
     *
     * @url add/book
     *
     * @access private
     * @method POST
     *
     * @params !list_id int
     * @params !book_id_id int
     * @params !rating int >=0 <= 10
     *
     * @return array
     */
    public function addBookInListAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'list_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'book_id_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'rating' => [
                    'type' => 'int',
                    'is_require' => true,
                    'min' => 0,
                    'max' => 10,
                ],
            ];

            $data = self::getInput('GET', $expectation, null, false);
        }
        //END GENERATED VALIDATION
        $userId = self::getUserId();
        try {
            $bookList = $this->bookListsService->getBookListById($data['list_id']);

            if ($bookList->getUserId() != $userId) {
                self::returnPermissionException();
            }

            $book = $this->bookService->getBookById($data['book_id']);

            $bookRecord = $this->bookListsService->addBookInList($bookList,$book,$data['rating']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case BookListsService::ERROR_UNABLE_DELETE_BOOK_LIST:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
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

        return self::successResponse('Book was successfully added in list', $bookRecord->toArray());
    }

    /**
     * Удаляет книгу из списка
     *
     * @url delete/book
     *
     * @access private
     * @method DELETE
     *
     * @params !list_id int
     * @params !book_id_id int
     *
     * @return array
     */
    public function deleteBookFromListAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'list_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'book_id_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
            ];

            $data = self::getInput('GET', $expectation, null, false);
        }
        //END GENERATED VALIDATION
        $userId = self::getUserId();
        try {
            $bookList = $this->bookListsService->getBookListById($data['list_id']);

            if ($bookList->getUserId() != $userId) {
                self::returnPermissionException();
            }

            $bookRecord = $this->bookListsService->getBookRecordByIds($data['list_id'],$data['book_id']);
            $this->bookListsService->deleteBookFromList($bookRecord);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                case BookListsService::ERROR_UNABLE_DELETE_BOOK_FROM_LIST:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case BookListsService::ERROR_BOOK_RECORD_NOT_FOUND:
                case BookListsService::ERROR_BOOK_LIST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Book was successfully added in list', $bookRecord->toArray());
    }

    /**
     * возвращает книги из списка
     *
     * @url get/books
     *
     * @access private
     * @method GET
     *
     * @params! list_id
     */
    public function getBooksFromListAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'list_id' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
            ];

            $data = self::getInput('GET', $expectation, null, false);
        }
        //END GENERATED VALIDATION

        $userId = self::getUserId();
        $this->db->begin();
        try {
            $bookList = $this->bookListsService->getBookListById($data['list_id']);

            if ($bookList->getUserId() != $userId) {
                self::returnPermissionException();
            }

            $bookRecords = BookListsBooks::findByListId($data['list_id']);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_UNABLE_DELETE_BOOK_LIST:
                    $exception = new Http400Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case BookListsService::ERROR_BOOK_LIST_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Book list was successfully deleted', $bookRecords->toArray());
    }
}

