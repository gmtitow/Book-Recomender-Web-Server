<?php

namespace App\Services;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Models\ActivationCodes;
use App\Models\BookLists;
use App\Models\BookListsBooks;
use App\Models\Books;
use App\Models\ChangesAuthentication;
use App\Models\Users;
use App\Models\Group;
use App\Models\Phones;

use App\Libs\SupportClass;

/**
 * business logic for users
 *
 * Class UsersService
 */
class BookListsService extends AbstractService
{
    const ADDED_CODE_NUMBER = 26000;

    /** Unable to create user */
    const ERROR_UNABLE_CREATE_BOOK_LIST = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_BOOK_LIST = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_BOOK_LIST_NOT_FOUND = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_BOOK_LIST = 4 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_ADD_BOOK_IN_LIST = 5 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_BOOK_FROM_LIST = 6 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_RATING_IN_BOOK_RECORD = 7 + self::ADDED_CODE_NUMBER;
    const ERROR_BOOK_RECORD_NOT_FOUND = 8 + self::ADDED_CODE_NUMBER;

    const ERROR_INNER_LOGIC_ERROR = 5 + self::ADDED_CODE_NUMBER;

    /**
     * Creating a new book list
     *
     * @param array $data
     * @param bool $is_main
     * @return BookLists
     */
    public function createBookList(array $data, bool $is_main = null)
    {
        $this->db->begin();
        try {
            $bookList = new BookLists();

            $bookList->setUserId($data['user_id']);
            $bookList->setListName($data['list_name']);

            if ($is_main!= null) {
                $bookList->setIsMain($is_main);
            }

            if ($bookList->save() == false) {
                $this->db->rollback();
                SupportClass::getErrorsWithException($bookList,self::ERROR_UNABLE_CREATE_BOOK_LIST,'unable to create book list');
            }

        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
        $this->db->commit();

        return $bookList;
    }


    /**
     * @param BookLists $bookList
     * @param array $data
     * @return BookLists
     */
    public function changeBookList(BookLists $bookList, array $data){

        if ($bookList->getIsMain()) {
            throw new ServiceExtendedException('Unable change book list',
                self::ERROR_UNABLE_CHANGE_BOOK_LIST,null,null,['unable change main list']);
        }

        if (!empty($data['list_name']))
            $bookList->setListName($data['list_name']);

        if (!$bookList->update()) {
            SupportClass::getErrorsWithException($bookList,self::ERROR_UNABLE_CHANGE_BOOK_LIST,
                        'Unable change book list');
        }

        return $bookList;
    }

    /**
     * Delete an existing book list
     *
     * @param BookLists $bookList
     */
    public function deleteBookList(BookLists $bookList)
    {
        if ($bookList->getIsMain()) {
            throw new ServiceExtendedException('Unable delete book list',
                self::ERROR_UNABLE_DELETE_BOOK_LIST,null,null,['unable delete main list']);
        }
        try {
            if (!$bookList->delete()) {
                SupportClass::getErrorsWithException($bookList,self::ERROR_UNABLE_DELETE_BOOK_LIST,
                    'Unable delete book list');
            }
        } catch (\PDOException $e) {
            throw new ServiceException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param int $listId
     * @return BookLists
     */
    public function getBookListById(int $listId){
        $bookList = BookLists::findFirstByListId($listId);

        if (empty($bookList)) {
            throw new ServiceException("Book list not found",self::ERROR_BOOK_LIST_NOT_FOUND);
        }

        return $bookList;
    }

    /**
     * @param int $listId
     * @param int $bookId
     * @return BookListsBooks
     */
    public function getBookRecordByIds(int $listId, int $bookId){
        $bookRecord = BookListsBooks::findFirst(['list_id = :list_id: and book_id = :book_id:',
            'bind'=>['list_id'=>$listId,'book_id'=>$bookId]]);

        if (empty($bookRecord)) {
            throw new ServiceException("Book record not found",self::ERROR_BOOK_RECORD_NOT_FOUND);
        }

        return $bookRecord;
    }

    /**
     * @param BookLists $list
     * @param Books $book
     * @param int $rating
     * @return BookListsBooks
     */
    public function addBookInList(BookLists $list, Books $book, int $rating){

        $bookRecord = new BookListsBooks();

        $bookRecord->setListId($list->getListId());
        $bookRecord->setBookId($book->getBookId());
        $bookRecord->setRating($rating);

        if (!$bookRecord->create()) {
            SupportClass::getErrorsWithException($bookRecord,self::ERROR_UNABLE_ADD_BOOK_IN_LIST,'Unable add book in list');
        }

        return $bookRecord;
    }

    /**
     * @param BookListsBooks $bookRecord
     */
    public function deleteBookFromList(BookListsBooks $bookRecord){

        if (!$bookRecord->delete()) {
            SupportClass::getErrorsWithException($bookRecord,self::ERROR_UNABLE_DELETE_BOOK_FROM_LIST,'Unable delete book from list');
        }
    }

    /**
     * @param BookListsBooks $bookRecord
     * @param int $rating
     * @return BookListsBooks
     */
    public function changeRatingInBookRecord(BookListsBooks $bookRecord, int $rating){

        $bookRecord->setRating($rating);
        if (!$bookRecord->update()) {
            SupportClass::getErrorsWithException($bookRecord,self::ERROR_UNABLE_DELETE_BOOK_FROM_LIST,'Unable change rating');
        }

        return $bookRecord;
    }
}
