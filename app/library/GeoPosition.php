<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 25.03.2019
 * Time: 10:12
 */

namespace App\Libs;


class GeoPosition
{
    const MinLatitude = -85.05112878;
    const MaxLatitude = 85.05112878;
    const MinLongitude = -180;
    const MaxLongitude = 180;

    public static function checkLongitude($longitude)
    {
        $longitude = filter_var($longitude,FILTER_VALIDATE_FLOAT);

        if($longitude==null || !SupportClass::checkDouble($longitude))
            return false;
        if($longitude>=-180 && $longitude <= 180)
            return true;

        return false;
    }

    public static function checkLatitude($latitude)
    {
        $latitude = filter_var($latitude,FILTER_VALIDATE_FLOAT);

        if($latitude==null || !SupportClass::checkDouble($latitude))
            return false;
        if($latitude>=-85.05112878 && $latitude <= 85.05112878)
            return true;

        return false;
    }
    
    public static function handleCluster($data, $levelOfDetail, $avgLongitude, $avgLatitude, $count, $quadkey23){
        $data['position']['longitude'] = $avgLongitude;
        $data['position']['latitude'] = $avgLatitude;
        $data['count'] = $count;
        $data['type'] = 2;

        //$levelOfDetail = 21;

        $tileX = null;
        $tileY = null;
        $level = null;
        TileSystem::quadKeyToTileXY(TileSystem::quadKeyDecTo4($quadkey23),$tileX,$tileY,$level);

        $pixelXLeftHeight = null;
        $pixelYLeftHeight = null;

        $detailTiles = pow(2,/*($levelOfDetail+2)*/(23-$levelOfDetail-4));

        TileSystem::tileXYToPixelXY($tileX-$detailTiles,$tileY-$detailTiles,$pixelXLeftHeight,$pixelYLeftHeight);

        $pixelXRightBottom = null;
        $pixelYRightBottom = null;
        TileSystem::tileXYToPixelXY($tileX +$detailTiles,$tileY+$detailTiles,$pixelXRightBottom,$pixelYRightBottom);

        $longLeft = TileSystem::pixelXToLong($pixelXLeftHeight,$level);
        $longRight = TileSystem::pixelXToLong($pixelXRightBottom,$level);
        $latTop = TileSystem::pixelYToLat($pixelYLeftHeight,$level);
        $latBottom = TileSystem::pixelYToLat($pixelYRightBottom,$level);

        $data['scope']['right_bottom']['longitude'] = $longRight;//$longLeft;
        $data['scope']['right_bottom']['latitude'] = $latBottom;//$latTop;
        $data['scope']['left_top']['longitude'] = $longLeft;//$longRight;
        $data['scope']['left_top']['latitude'] = $latTop;//$latBottom;

        return $data;
    }
}