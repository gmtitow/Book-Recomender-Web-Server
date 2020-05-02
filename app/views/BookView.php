<?php


namespace App\Views;


class BookView extends AbstractView
{
    public static function handleBook(array $book, string $author_full_name, array $related_genres) {
        $handledBook = [];

        $handledBook['book_id'] = $book['book_id'];
        $handledBook['name'] = $book['name'];
        $handledBook['description'] = $book['description'];

        //Автор
        $handledBook['author_name'] = $author_full_name;

        $handledBook['genres'] = $related_genres;
        $handledBook['rating'] = isset($book['rating'])?$book['rating']:$book['rating_parsed'];

        return $handledBook;
    }

    public static function handleBookInfo(array $book, string $author_full_name,
                                          array $related_genres, array $reviews, bool $reviewExists = null) {
        $handledBook = [];

        $handledBook['book_id'] = $book['book_id'];
        $handledBook['name'] = $book['name'];
        $handledBook['description'] = $book['description'];

        //Автор
        $handledBook['author_name'] = $author_full_name;

        $handledBook['genres'] = $related_genres;
        $handledBook['rating'] = isset($book['rating'])?$book['rating']:$book['rating_parsed'];

        //отзывы
        $handledBook['reviews']['pagination'] = $reviews['pagination'];
        $handledBook['reviews']['data'] = [];

        if ($reviewExists!==null) {
            $handledBook['review_exists'] = $reviewExists;
        }

        foreach ($reviews['data'] as $review) {
            $handledBook['reviews']['data'][] = ReviewView::handleReview($review);
        }

        return $handledBook;
    }

    public static function handleBookShort(array $book, string $author_full_name) {
        $handledBook = [];

        $handledBook['book_id'] = $book['book_id'];
        $handledBook['name'] = $book['name'];
        $handledBook['description'] = $book['description'];

        //Автор
        $handledBook['author_name'] = $author_full_name;

        return $handledBook;
    }

    public static function handleReadBook(array $book, string $author_full_name, int $rating) {
        $handledBook = self::handleBookShort($book,$author_full_name);
        $handledBook['rating'] = $rating;

        return $handledBook;
    }
}