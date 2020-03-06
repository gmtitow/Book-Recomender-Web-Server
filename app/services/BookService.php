<?php


namespace App\services;


use App\library\SphinxSupport;
use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;
use App\Models\Authors;
use App\Models\Books;
use App\Models\Genres;
use App\Views\BookView;

class BookService extends AbstractService
{

    const ADDED_CODE_NUMBER = 3000;

    const ERROR_BOOK_NOT_FOUND = 1 + self::ADDED_CODE_NUMBER;

    public function getBookById(int $book_id) {
        $book = Books::findFirstByBookId($book_id);

        if(!$book) {
            throw new ServiceException("Book not found", self::ERROR_BOOK_NOT_FOUND);
        }
        return $book;
    }

    public function getBooks(string $query, array $filters, $page, $page_size) {
        $cl = SphinxSupport::initSphinx();

        $sorts_divided = SupportClass::divideSorts('weight() desc | rating desc');

        $cl = SphinxSupport::handleSort($cl,$sorts_divided);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        $cl->SetLimits($offset, $page_size, 100000);

        $cl->SetFieldWeights(['name'=>100,'full_name'=>90,'description'=>1]);

        if (isset($filters['genres'])) {
            $cl->SetFilter('genre_id',$filters['genres'],false);
        }

        if (isset($filters['authors'])) {
            $cl->SetFilter('author_id',$filters['authors'],false);
        }

        $cl->AddQuery($cl->EscapeString($query),'books_disc_index');

        $results = $cl->RunQueries();

        $result = SphinxSupport::getMatches($results);

        return ['data'=>$this->handleBooksFromSearch($result),'pagination' => ['total' => intval($results[0]['total_found'])]];
    }

    public function getRecommends(string $query, array $filters, $page, $page_size) {
        $cl = SphinxSupport::initSphinx();

        $sorts_divided = SupportClass::divideSorts('rating desc');

        $cl = SphinxSupport::handleSort($cl,$sorts_divided);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        $cl->SetLimits($offset, $page_size, 100000);

        $cl->SetFieldWeights(['name'=>100,'full_name'=>90,'description'=>1]);

        if (isset($filters['genres'])) {
            $cl->SetFilter('genre_id',$filters['genres'],false);
        }

        if (isset($filters['authors'])) {
            $cl->SetFilter('author_id',$filters['authors'],false);
        }

        $cl->AddQuery($cl->EscapeString($query),'books_disc_index');

        $results = $cl->RunQueries();

        $result = SphinxSupport::getMatches($results);

        return ['data'=>$this->handleBooksFromSearch($result),'pagination' => ['total' => intval($results[0]['total_found'])]];

    }

    private function handleBooksFromSearch($search_res_books) {
        $handledBooks = [];
        if ($search_res_books != null)
            foreach ($search_res_books as $book) {
                $book_info = SupportClass::translateInPhpArrFromPostgreJsonObject($book['attrs']['book']);

                if (!$book_info) {
                    throw new ServiceException('Can\'t parse book info');
                }

                $author = Authors::findFirstByAuthorId($book_info['author_id']);


                if (!$author) {
//                    throw new ServiceException('Author not found');
                    $authorFullName = "Автор неизвестен";
                } else {
                    $authorFullName = $author->getFullName();
                }

                $query = new CustomQuery([
                    'columns' => 'genres.genre_id,genres.genre_name',
                    'from' => 'genres_books inner join genres using (genre_id)',
                    'where' => 'genres_books.book_id = :book_id',
                    'bind' => ['book_id'=>$book_info['book_id']]
                ]);

                $genres = SupportClass::execute($query->getSql(),$query->getBind());

                $handledBooks[] = BookView::handleBook($book_info,$authorFullName,$genres);
            }
        return $handledBooks;
    }

    public function handleBookInfo(array $book, array $reviews, bool $reviewExists = null) {
        $author = Authors::findFirstByAuthorId($book['author_id']);

        if (!$author)
            throw new ServiceException('Author not found');

        $query = new CustomQuery([
            'columns' => 'genres.genre_id,genres.genre_name',
            'from' => 'genres_books inner join genres using (genre_id)',
            'where' => 'genres_books.book_id = :book_id',
            'bind' => ['book_id'=>$book['book_id']]
        ]);

        $genres = SupportClass::execute($query->getSql(),$query->getBind());

        $handledBook = BookView::handleBookInfo($book,$author->getFullName(),$genres, $reviews, $reviewExists);

        return $handledBook;
    }
}