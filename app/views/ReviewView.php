<?php


namespace App\views;


class ReviewView extends AbstractView
{
    public static function handleReview(array $review, int $userId = null) {
        $handledReview = [];

        $handledReview['review_id'] = $review['review_id'];
        $handledReview['book_id'] = $review['book_id'];
        $handledReview['user_id'] = $review['user_id'];
        $handledReview['review_text'] = $review['review_text'];
        $handledReview['rating'] = $review['rating'];
        $date = new \DateTime($review['review_date']);
        $handledReview['review_date'] = $date->format(POSTGRES_DATE_FORMAT);

        return $handledReview;
    }

    public static function handleReviewWithBookInfo(array $reviewAndBook, string $author_full_name, int $userId = null) {
        $handledReview = [];
        $handledReview['review'] = self::handleReview($reviewAndBook,$userId);
        $handledReview['book_info'] = BookView::handleBookShort($reviewAndBook,$author_full_name);

        return $handledReview;
    }
}