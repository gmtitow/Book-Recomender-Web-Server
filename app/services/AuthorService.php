<?php


namespace App\services;


use App\library\SphinxSupport;
use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;
use App\Models\Authors;
use App\Models\Books;
use App\Models\Genres;
use App\Views\AuthorView;
use App\Views\BookView;

class AuthorService extends AbstractService
{

    const ADDED_CODE_NUMBER = 4000;

    public function getAuthors(string $query, array $filters, $page, $page_size) {
        $cl = SphinxSupport::initSphinx();

        $sorts_divided = SupportClass::divideSorts('weight() desc');

        $cl = SphinxSupport::handleSort($cl,$sorts_divided);

        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);

        $page = $page > 0 ? $page : 1;
        $offset = ($page - 1) * $page_size;

        $cl->SetLimits($offset, $page_size, 100000);

//        $cl->SetFieldWeights(['name'=>100,'full_name'=>90,'description'=>1]);

        if (isset($filters['genres'])) {
            $cl->SetFilter('genre_id',$filters['genres'],false);
        }

        $cl->AddQuery($cl->EscapeString($query),'authors_disc_index');

        $results = $cl->RunQueries();

        $result = SphinxSupport::getMatches($results);

        return ['data'=>$this->handleAuthorsFromSearch($result),'pagination' => ['total' => intval($results[0]['total_found'])]];
    }

    private function handleAuthorsFromSearch($search_res_authors) {
        $handledAuthors = [];
        if ($search_res_authors != null)
            foreach ($search_res_authors as $author) {
                $author_info = SupportClass::translateInPhpArrFromPostgreJsonObject($author['attrs']['author']);

                $handledAuthors[] = AuthorView::handleAuthor($author_info);
            }
        return $handledAuthors;
    }
}