<?php

namespace App\Controllers;

use App\Controllers\HttpExceptions\Http400Exception;
use App\Controllers\HttpExceptions\Http403Exception;
use App\Controllers\HttpExceptions\Http422Exception;
use App\Controllers\HttpExceptions\Http500Exception;
use App\Models\Reviews;
use App\services\ReviewService;
use App\Services\ServiceException;
use App\Services\ServiceExtendedException;
use App\views\ReviewView;

/**
 * Class BookController
 * Контроллер для работы непосредственно с книгами.
 *
 * @url review
 */
class ReviewController extends AbstractController
{
    /**
     * Добавляет отзыв
     *
     * @url add
     *
     * @access private
     * @method POST
     *
     * @params! rating int
     * @params review_text
     * @params! book_id int
     *
     */
    public function addReviewAction()
    {
        $expectation = [
            'rating' => [
                'type'=>'int',
                'is_require' => true
            ],
            'book_id' => [
                'type'=>'int',
                'is_require' => true
            ],
            'review_text' => [
                'type'=>'string'
            ]
        ];

        $data = self::getInput('POST',$expectation);

        $data['user_id'] = self::getUserId();
        $this->db->begin();
        try {
            $userId = self::getUserId();

            $review = $this->reviewService->createReview($data);
            $review = $this->reviewService->getReviewById($review->getReviewId());

            $handledReview = ReviewView::handleReview($review->toArray());

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_UNABLE_CREATE_REVIEW:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                case ReviewService::ERROR_UNABLE_CREATE_REVIEW:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Review was successfully created', $handledReview);
    }

    /**
     * Изменяет отзыв
     *
     * @url edit
     *
     * @access private
     * @method PUT
     *
     * @params! review_id int
     * @params rating int
     * @params review_text
     *
     */
    public function editReviewAction()
    {
        $expectation = [
            'review_id' => [
                'type'=>'int',
                'is_require'=>true
            ],
            'rating' => [
                'type'=>'int'
            ],
            'review_text' => [
                'type'=>'string'
            ]
        ];

        $data = self::getInput('POST',$expectation);

        $userId = self::getUserId();
        $data['user_id'] = $userId;
        $this->db->begin();
        try {
            $review = $this->reviewService->getReviewById($data['review_id']);

            if ($review->getUserId() != $userId) {
                self::returnPermissionException();
            }

            $review = $this->reviewService->changeReview($review,$data);

            $handledReview = ReviewView::handleReview($review->toArray());

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_UNABLE_CHANGE_REVIEW:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Review was successfully changed', $handledReview);
    }

    /**
     * Удаляет отзыв
     *
     * @url delete
     *
     * @access private
     * @method DELETE
     *
     * @params! review_id int
     *
     */
    public function deleteReviewAction()
    {
        $expectation = [
            'review_id' => [
                'type'=>'int',
                'is_require'=>true
            ],
        ];

        $data = self::getInput('DELETE',$expectation);

        $userId = self::getUserId();
//        $data['user_id'] = $userId;
        $this->db->begin();
        try {
            $review = $this->reviewService->getReviewById($data['review_id']);

            if ($review->getUserId() != $userId) {
                self::returnPermissionException();
            }

            $review = $this->reviewService->deleteReview($review);

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_UNABLE_DELETE_REVIEW:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successResponse('Review was successfully deleted');
    }

    /**
     * Возвращает отзывы пользователя
     *
     * @url get
     *
     * @access private
     * @method GET
     *
     * @params page int
     * @params page_size int
     *
     */
    public function getAllReviewAction()
    {
        $expectation = [
            'page' => [
                'type'=>'int',
                'default' => 1
            ],
            'page_size' => [
                'type'=>'int',
                'default'=> 10
            ],
        ];

        $data = self::getInput('GET',$expectation);

        $userId = self::getUserId();
//        $data['user_id'] = $userId;
        $this->db->begin();
        try {

            $reviews = Reviews::findForUser($userId,$data['page'],$data['page_size']);

            $handledReviews = [];

            foreach($reviews['data'] as $review) {
                $handledReviews[] = ReviewView::handleReviewWithBookInfo($review,$review['author_full_name']);
            }

        } catch (ServiceExtendedException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_UNABLE_DELETE_REVIEW:
                    $exception = new Http422Exception($e->getMessage(), $e->getCode(), $e);
                    throw $exception->addErrorDetails($e->getData());
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            $this->db->rollback();
            switch ($e->getCode()) {
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        $this->db->commit();
        return self::successPaginationResponse('', $handledReviews,$reviews['pagination']);
    }
}

