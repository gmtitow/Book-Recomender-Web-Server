<?php


namespace App\views;



class PromotionView extends AbstractView
{
    public static function handlePromotion(array $promotion) {
        $handledPromotion = [];

        $handledPromotion['promotion_id'] = $promotion['promotion_id'];
        $handledPromotion['description'] = $promotion['description'];
        $handledPromotion['time_start'] = date(USUAL_DATE_FORMAT, strtotime($promotion['time_start']));
        $handledPromotion['time_end'] = date(USUAL_DATE_FORMAT, strtotime($promotion['time_end']));

        return $handledPromotion;
    }
}