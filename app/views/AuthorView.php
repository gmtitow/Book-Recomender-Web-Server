<?php


namespace App\Views;


class AuthorView extends AbstractView
{
    public static function handleAuthor(array $author) {
        $handledAuthor = [];

        $handledAuthor['author_id'] = $author['author_id'];
        $handledAuthor['full_name'] = $author['full_name'];

        return $handledAuthor;
    }
}