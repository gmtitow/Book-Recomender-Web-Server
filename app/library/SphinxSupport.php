<?php


namespace App\library;


use App\Libs\SphinxClient;
use App\Libs\SupportClass;
use App\Libs\TileSystem;
use App\Models\UserLocation;

class SphinxSupport
{
    public static function handleFilters(SphinxClient $cl, array $filters, array $restrictions = null){
        foreach ($filters as $key=>$value){
            if($restrictions== null || in_array($key,$restrictions)) {
                switch ($key) {
                    case 'price_min':
                        $cl->SetFilterRange('price_min', $value, PHP_INT_MAX, false);
                        break;
                    case 'price_max':
                        $cl->SetFilterRange('price_max', 0, $value, false);
                        break;
                    case 'categories':
                        $cl->setFilter('category_id', $value, false);
                        break;
                    case 'rating_min':
                        $cl->SetFilterRange('rating', $value, PHP_INT_MAX, false);
                        break;
                    case 'company_id':
                        $cl->SetFilter('company_id', $value, false);
                        break;
                    case 'age_min':
                        $cl->SetFilterRange('age', $value, PHP_INT_MAX, false);
                        break;
                    case 'age_max':
                        $cl->SetFilterRange('age', 0, $value, false);
                        break;
                    case 'with_photo':
                        if($value)
                            $cl->SetFilter('path_to_photo', [''], true);
                        else
                            $cl->SetFilter('path_to_photo', [''], false);
                        break;
                    case 'city_id':
                        $cl->SetFilter('city_id', $value, false);
                        break;
                    case 'online':
                        $cl->SetFilter('online', $value, false);
                        break;
                    case 'male':
                        $cl->SetFilter('male', [$value], false);
                        break;
                    case 'last_time':
                        {
//                            $cl = self::addSelect($cl,'IF(DATE(`last_time`)
//                            BETWEEN "2019-05-07 14:25:11+00" AND "2019-07-08 14:25:11+00"
//                            ,1,0) as validate_last_time');
//                            $cl->SetFilter('validate_last_time', [1], false);
                            $cl->SetFilterRange('last_time',strtotime($value),time(),false);
                            break;
                        }
                }
            }
        }

        return $cl;
    }

    public static function translateToCenterDiagonal(array $low_left, array $high_right){
        $center['longitude'] = ($high_right['longitude'] + $low_left['longitude']) / 2;
        $center['latitude'] = ($high_right['latitude'] + $low_left['latitude']) / 2;

        $diagonal['longitude'] = $high_right['longitude'];
        $diagonal['latitude'] = $high_right['latitude'];

        return ['center'=>$center,'diagonal'=>$diagonal];
    }

    public static function addPosition(SphinxClient $cl, array $center){
        self::addSelect($cl,'GEODIST(`latitude`, `longitude`, '.deg2rad($center['latitude']).',
            '.deg2rad($center['longitude']).') as g');
        return $cl;
    }

    public static function addFilterByPosition(SphinxClient $cl,
                                               array $low_left, array $high_right){

        $points = self::translateToCenterDiagonal($low_left,$high_right);
        $cl = self::addPosition($cl,$points['center']);

        $radius = SupportClass::codexworldGetDistanceOpt($points['center']['latitude'], $points['center']['longitude'],
            $points['diagonal']['latitude'], $points['diagonal']['longitude']);

        $cl->SetFilterFloatRange("g", 0, $radius, false);


        return $cl;
    }

    public static function addSelect(SphinxClient $cl,
                                               string $str){
        if($cl->select!=null){
            $cl->SetSelect($cl->select.', ' . $str);

            $cl->select =$cl->select.', ' . $str;
        } else {
            $cl->SetSelect('*, ' . $str);

            $cl->select ='*, ' . $str;
        }

        return $cl;
    }

    public static function handleSort(SphinxClient $cl, array $sorts, array $restrictions = null)
    {
        $sort_finish = '';
        $first = true;
        foreach($sorts as $sort){
            if ($first)
                $first = false;
            else {
                $sort_finish.=', ';
            }

            if($restrictions== null || in_array($sort['field'],$restrictions)) {
                $sort_finish .= $sort['field'] . ' '. $sort['direction'];
            }
        }

//        $sort_finish.='@relevance desc';

        if(trim($sort_finish)!='')
            $cl->SetSortMode(SPH_SORT_EXTENDED,$sort_finish);

//        $cl->SetSortMode(SPH_SORT_EXTENDED,'weight() DESC, rating desc');

        return $cl;
    }

    public static function getMatches($results){
        $allmatches = [];
        if ($results!= null)
            foreach ($results as $result) {
                if ($result['total'] > 0) {
                    $allmatches = array_merge($allmatches, $result['matches']);
                }
            }
        return $allmatches;
    }

    public static function initSphinx(): SphinxClient{
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);

        return $cl;
    }

    public static function getQuadKey( array $lowLeft, array $highRight){
        return $quadCommon = TileSystem::getQuadKeyByViewport($highRight['latitude'], $highRight['longitude'],
            $lowLeft['latitude'], $lowLeft['longitude']);
    }

    public static function getQuadKeyLevel($quadCommon){
        return ((strlen($quadCommon) + 2) > 23 ? 23 : (strlen($quadCommon) + 2));
    }

    public static function setClustersConditions(SphinxClient $cl, array $lowLeft, array $highRight): int{
        $quadCommon = self::getQuadKey($lowLeft,$highRight);

        $quads = TileSystem::getClusters($quadCommon, strlen($quadCommon));
        $result_level = self::getQuadKeyLevel($quadCommon);
        $result_quadkey = 'quadkey' . $result_level;
        $cl = self::addSelect($cl,'AVG(avglongitude) as avglongitude_2, AVG(avglatitude) as avglatitude_2');
        $cl->setGroupBy($result_quadkey, SPH_GROUPBY_ATTR);
        $cl->SetFilterRange('quadkey23', $quads['quadCodeLeft'], $quads['quadCodeRight']);

        return $result_level;
    }
}