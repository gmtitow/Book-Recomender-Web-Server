<?php


namespace App\Views;

use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;
use App\Models\Files;
use App\Models\PromotionsBooks;

class BookView extends AbstractView
{
    public static function handleBook(array $book, string $author_full_name, array $related_genres) {
        $handledBook = [];

        $handledBook['book_id'] = $book['book_id'];
        $handledBook['name'] = $book['name'];
        $handledBook['description'] = $book['description'];
        $handledBook['price'] = $book['price'];

        //Это костыли, такого во View быть не должно
        $handledBook['image_path'] = null;
        if ($book['cover_file_id'] != null) {
            $file = Files::findFirstByFileId($book['cover_file_id']);

            if (!empty($file))
                $handledBook['image_path'] = $file->getPathTo();
        }

        $query = new CustomQuery([
            'columns'=>'price',
            'from' =>'promotions_books inner join promotions using(promotion_id)',
            'where'=>'book_id = :book_id and time_start < CURRENT_TIMESTAMP and time_end > CURRENT_TIMESTAMP',
            'order'=>'created_at desc',
            'limit'=>'1',
            'bind'=>[
                'book_id'=>$book['book_id']
            ]
        ]);

        $new_price = SupportClass::executeQuery($query);

        if (empty($new_price))
            $new_price = null;
        else
            $new_price = $new_price[0]['price'];
        $handledBook['new_price'] = $new_price;
        //Костыли закончились

        //Автор
        $handledBook['author_name'] = $author_full_name;

        $handledBook['genres'] = $related_genres;
        $handledBook['rating'] = isset($book['rating'])?$book['rating']:$book['rating_parsed'];

        return $handledBook;
    }

    public static function handleBookInfo(array $book, string $author_full_name,
                                          array $related_genres, array $reviews, bool $reviewExists = null) {
//        $handledBook = [];
//
//        $handledBook['book_id'] = $book['book_id'];
//        $handledBook['name'] = $book['name'];
//        $handledBook['description'] = $book['description'];
//
//        //Автор
//        $handledBook['author_name'] = $author_full_name;
//
//        $handledBook['genres'] = $related_genres;
//        $handledBook['rating'] = isset($book['rating'])?$book['rating']:$book['rating_parsed'];

        $handledBook = self::handleBook($book,$author_full_name,$related_genres);

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

        $handledBook['image_path'] = null;
        if ($book['cover_file_id'] != null) {
            $file = Files::findFirstByFileId($book['cover_file_id']);

            if (!empty($file))
                $handledBook['image_path'] = $file->getPathTo();
        }

        return $handledBook;
    }
}