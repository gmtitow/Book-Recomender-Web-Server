<?php


namespace App\services;


use App\library\SphinxSupport;
use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;
use App\Models\Authors;
use App\Models\Books;
use App\Models\Genres;
use App\Models\Reviews;
use App\Views\BookView;

class ReviewService extends AbstractService
{

    const ADDED_CODE_NUMBER = 5000;

    const ERROR_UNABLE_CREATE_REVIEW = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_REVIEW_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_REVIEW = 3 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_CHANGE_REVIEW = 4 + self::ADDED_CODE_NUMBER;

    public function createReview(array $reviewData)
    {
        if (Reviews::checkReviewExists($reviewData['user_id'],$reviewData['book_id'])) {
            throw new ServiceException("Unable create more than one review for book",self::ERROR_UNABLE_CREATE_REVIEW);
        }

        $review = new Reviews();
        $this->fillReview($review, $reviewData);

        if ($review->create() == false) {
            SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_CREATE_REVIEW, 'Unable to create review');
        }

        return $review;
    }

    public function getReviewById(int $reviewId)
    {
        $review = Reviews::findFirstByReviewId($reviewId);

        if (!$review) {
            throw new ServiceException('Review don\'t exists', self::ERROR_REVIEW_NOT_FOUND);
        }
        return $review;
    }

    public function fillReview(Reviews $review, array $data)
    {
        if (!empty(trim($data['book_id'])))
            $review->setBookId($data['book_id']);

        if (!empty(trim($data['rating'])))
            $review->setRating($data['rating']);

        if (isset($data['review_text']))
            $review->setReviewText($data['review_text']);

        if (!empty(trim($data['user_id'])))
            $review->setUserId($data['user_id']);
    }

    public function deleteReview(Reviews $review)
    {
        if ($review->delete() == false) {
            SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_DELETE_REVIEW, 'Unable to delete offer');
        }

        return $review;
    }

    public function changeReview(Reviews $review, array $reviewData)
    {
        $this->fillReview($review, $reviewData);

        if ($review->update() == false) {
            SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_CHANGE_REVIEW, 'Unable to change offer');
        }

        return $review;
    }
}