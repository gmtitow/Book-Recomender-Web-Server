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
use App\views\PromotionView;
use App\views\ReviewView;

/**
 * Class PromotionController
 *
 * @url promotion
 */
class PromotionController extends AbstractController
{
    /**
     * Добавляет акцию
     *
     * @url add
     *
     * @access moderator
     * @method POST
     *
     * @params description string
     * @params !time_start int
     * @params !time_end int
     *
     * @params book_descriptions array => {
     *      !type => string,
     *      book_id => int
     *      author_id => int
     *      genre_id => int,
     *      query=>string
     *      !factor => float
     * }
     *
     */
    public function addPromotionAction()
    {
        //GENERATED VALIDATION
        {
            $expectation = [
                'description' => [
                    'type' => 'string',
                ],
                'time_start' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'time_end' => [
                    'type' => 'int',
                    'is_require' => true,
                ],
                'book_descriptions' => [
                    'type' => 'array',
                    'sub_data' => [
                        'type' => [
                            'type' => 'string',
                            'is_require' => true,
                        ],
                        'book_id' => [
                            'type' => 'int',
                        ],
                        'author_id' => [
                            'type' => 'int',
                        ],
                        'genre_id' => [
                            'type' => 'int',
                        ],
                        'query' => [
                            'type' => 'string',
                        ],
                        'factor' => [
                            'type' => 'float',
                            'is_require' => true,
                        ],
                    ],
                ],
            ];

            $data = self::getInput('POST', $expectation, null, false);
        }
        //END GENERATED VALIDATION

        try {
            $userId = self::getUserId();

            if (!isset($data['book_descriptions']))
                $data['book_descriptions'] = [];

            $promotion = $this->promotionService->createPromotion($data,$data['book_descriptions']);

            $handledReview = PromotionView::handlePromotion($promotion->toArray());

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Promotion was successfully created', $handledReview);
    }

    /**
     * Удаляет отзыв
     *
     * @url delete
     *
     * @access moderator
     * @method DELETE
     *
     * @params! promotion_id int
     *
     */
    public function deletePromotionAction()
    {
        $expectation = [
            'promotion_id' => [
                'type'=>'int',
                'is_require'=>true
            ],
        ];

        $data = self::getInput('DELETE', $expectation);

        try {
            $promotion = $this->promotionService->getPromotionById($data['promotion_id']);

            $this->promotionService->deletePromotion($promotion);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                case ReviewService::ERROR_REVIEW_NOT_FOUND:
                    throw new Http400Exception($e->getMessage(), $e->getCode(), $e);
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successResponse('Promotion was successfully deleted');
    }

    /**
     * Возвращает акции
     *
     * @url get
     *
     * @access moderator
     * @method GET
     *
     * @params page int
     * @params page_size int
     *
     */
    public function getPromotionsAction()
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

        try {

            $promotions = $this->promotionService->getPromotions($data['page'],$data['page_size']);

            $handledPromotions = [];

            foreach($promotions['data'] as $promotion) {
                $handledPromotions[] = PromotionView::handlePromotion($promotion);
            }

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successPaginationResponse('', $handledPromotions,$promotions['pagination']);
    }

    /**
     * Возвращает акции
     *
     * @url get/books
     *
     * @access moderator
     * @method GET
     *
     * @params !promotion_id int
     * @params page int
     * @params page_size int
     *
     */
    public function getBooksForPromotionAction()
    {
        $expectation = [
            'promotion_id' => [
                'type'=>'int',
                'is_require'=>true
            ],
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

        try {

            $promotions = $this->promotionService->getBookForPromotion($data['promotion_id'],$data['page'],$data['page_size']);

        } catch (ServiceExtendedException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        } catch (ServiceException $e) {
            switch ($e->getCode()) {
                default:
                    throw new Http500Exception(_('Internal Server Error'), $e->getCode(), $e);
            }
        }

        return self::successPaginationResponse('', $promotions['data'],$promotions['pagination']);
    }
}

