<?php


namespace App\services;


use App\library\SphinxSupport;
use App\Libs\Database\CustomQuery;
use App\Libs\SupportClass;
use App\Models\Authors;
use App\Models\Books;
use App\Models\Genres;
use App\Models\Promotions;
use App\Models\PromotionsBooks;
use App\Models\Reviews;
use App\Views\BookView;

class PromotionService extends AbstractService
{

    const ADDED_CODE_NUMBER = 8000;

    const ERROR_UNABLE_CREATE_PROMOTION = 1 + self::ADDED_CODE_NUMBER;
    const ERROR_PROMOTION_NOT_FOUND = 2 + self::ADDED_CODE_NUMBER;
    const ERROR_UNABLE_DELETE_PROMOTION = 3 + self::ADDED_CODE_NUMBER;

    /**
     * @param array $promotionData
     * @param array $referencedBookDescriptions => [{type => string,
     *              ?book_id => int, ?author_id => int, ?genre_id => int,
     *              ?query=>string, factor => float}]
     * @return Promotions
     */
    public function createPromotion(array $promotionData, array $referencedBookDescriptions)
    {
        $this->db->begin();
        $promotion = new Promotions();
        $this->fillPromotion($promotion, $promotionData);

        if ($promotion->create() == false) {
            SupportClass::getErrorsWithException($promotion, self::ERROR_UNABLE_CREATE_PROMOTION, 'Unable to create promotion');
        }
        $sqlQueryToAddBook = new CustomQuery([
           'columns'=>'book, :promotion_id, price* :factor',
           'from'=>'books',
           'where'=>'book_id = :book_id',
           'bind'=>[

           ]
        ]);

        $sqlQueryToAddDescription = new CustomQuery([
            'columns'=>'book, :promotion_id, price* :factor',
            'from'=>'books',
            'where'=>'',
            'bind'=>[

            ]
        ]);

        foreach ($referencedBookDescriptions as $bookDescription) {
            if ($bookDescription['type'] == 'book') {

                $insertQuery = 'insert into promotions_books (book_id, promotion_id, price) ';

                $sqlQueryToAddBook->setBind([
                    'promotion_id'=>$promotion->getPromotionId(),
                    'factor' => $bookDescription['factor'],
                    'book_id' => $bookDescription['book_id'],
                ]);

                $insertQuery.=$sqlQueryToAddBook->getSql() . ' ON CONFLICT DO NOTHING';

                $result = SupportClass::execute($insertQuery,$sqlQueryToAddBook->getBind());

                $sqlQueryToAddBook->setBind([]);

            } elseif($bookDescription['type'] == 'description') {
                $insertQuery = 'insert into promotions_books (book_id, promotion_id, price) ';

                $sqlQueryToAddDescription->addBind(['factor' => $bookDescription['factor']]);

                if (isset($bookDescription['author_id']))
                    $sqlQueryToAddDescription->addWhere('author_id = :author_id',
                        ['author_id'=>$bookDescription['author_id']]);

                if (isset($bookDescription['genre_id']))
                    $sqlQueryToAddDescription->addWhere(
                        'exists (genres_books where genre_id = :genre_id and genres_books.book_id = books.book_id)',
                        ['genre_id'=>$bookDescription['genre_id']]);

                if (isset($bookDescription['query'])) {
                    $sqlQueryToAddDescription->addWhere(
                        'name ~* :query or description ~* :query',
                        ['query'=>$bookDescription['query']]);
                }

                $insertQuery.=$sqlQueryToAddBook->getSql() . ' ON CONFLICT DO NOTHING';

                $result = SupportClass::execute($insertQuery,$sqlQueryToAddBook->getBind());

                $sqlQueryToAddBook->setWhere("");
                $sqlQueryToAddBook->setBind([]);
            }
        }

        $this->db->commit();

        return $promotion;
    }

    public function getPromotionById(int $promotionId)
    {
        $promotion = Promotions::findById($promotionId);

        if (!$promotion) {
            throw new ServiceException('Promotion not found', self::ERROR_PROMOTION_NOT_FOUND);
        }
        return $promotion;
    }

    public function fillPromotion(Promotions $promotion, array $data)
    {
        if (!empty(trim($data['description'])))
            $promotion->setDescription($data['description']);

        if (isset($data['time_start']))
            $promotion->setTimeStart(date(POSTGRES_DATE_FORMAT,$data['time_start']));

        if (isset($data['time_end']))
            $promotion->setTimeEnd(date(POSTGRES_DATE_FORMAT,$data['time_end']));
    }

    public function fillPromotionBookReference(PromotionsBooks $promotion, array $data)
    {
    }

    public function deleteReview(Reviews $review)
    {
        if ($review->delete() == false) {
            SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_DELETE_PROMOTION, 'Unable to delete offer');
        }

        return $review;
    }

    public function changeReview(Reviews $review, array $reviewData)
    {
        $this->fillPromotion($review, $reviewData);

        if ($review->update() == false) {
            SupportClass::getErrorsWithException($review, self::ERROR_UNABLE_CHANGE_REVIEW, 'Unable to change offer');
        }

        return $review;
    }
}