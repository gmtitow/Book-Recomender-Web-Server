<?php
namespace App\Libs;


class TileSystem
{
    const EarthRadius = 6378137;
    const MinLatitude = -85.05112878;
    const MaxLatitude = 85.05112878;
    const MinLongitude = -180;
    const MaxLongitude = 180;
    const MaxZoom = 23;

    private static function clip( $n, $minValue, $maxValue)
    {
        return min( max( $n, $minValue), $maxValue);
    }

    public static function mapSize( $levelOfDetail)
    {
        return 256 << $levelOfDetail;
    }

    public static function GroundResolution( $latitude, $levelOfDetail)
    {
        $latitude = self::clip( $latitude, self::MinLatitude, self::MaxLatitude);
        return cos( $latitude * pi() / 180) * 2 * pi() * self::EarthRadius / self::mapSize( $levelOfDetail);
    }

    public static function mapScale($latitude, $levelOfDetail, $screenDpi)
    {
        return self::groundResolution($latitude, $levelOfDetail) * $screenDpi / 0.0254;
    }

    public static function latToPixelY( $latitude, $levelOfDetail)
    {
        $latitude = self::clip( $latitude, self::MinLatitude, self::MaxLatitude);
        $sinLatitude = sin( $latitude * pi() / 180);
        $y = 0.5 - log((1 + $sinLatitude) / (1 - $sinLatitude)) / (4 * pi());
        $mapSize = self::mapSize( $levelOfDetail);
        $pixelY = (int) self::clip( $y * $mapSize + 0.5, 0, $mapSize - 1);
        return $pixelY;
    }

    public static function longToPixelX( $longitude, $levelOfDetail)
    {
        $longitude = self::clip( $longitude, self::MinLongitude, self::MaxLongitude);
        $x = ($longitude + 180) / 360;
        $mapSize = self::mapSize( $levelOfDetail);
        $pixelX = (int) self::clip( $x * $mapSize + 0.5, 0, $mapSize - 1);
        return $pixelX;
    }

    public static function pixelXToLong( $pixelX, $levelOfDetail)
    {
        $mapSize = self::mapSize( $levelOfDetail);
        $x = ( self::clip( $pixelX, 0, $mapSize - 1) / $mapSize) - 0.5;
        $longitude = 360 * $x;
        return $longitude;
    }

    public static function pixelYToLat( $pixelY, $levelOfDetail)
    {
        $mapSize = self::mapSize( $levelOfDetail);
        $y = 0.5 - (self::clip( $pixelY, 0, $mapSize - 1) / $mapSize);
        $latitude = 90 - 360 * atan( exp(-$y * 2 * pi())) / pi();
        return $latitude;
    }

    public static function pixelYToTileY( $pixelY)
    {
        return $pixelY / 256;
    }

    public static function pixelXToTileX( $pixelX)
    {
        return $pixelX / 256;
    }

    public static function tileXYToPixelXY( $tileX, $tileY, &$pixelX, &$pixelY)
    {
        $pixelX = $tileX * 256;
        $pixelY = $tileY * 256;
    }

    public static function tileXYToQuadKey( $tileX, $tileY, $levelOfDetail)
    {
        if ($levelOfDetail == 0)
            return "0";

        $quadKey = "";
        for ($i = $levelOfDetail; $i > 0; $i--) {
            $digit = 0;
            $mask = 1 << ($i - 1);
            if (($tileX & $mask) != 0) {
                $digit++;
            }
            if (($tileY & $mask) != 0) {
                $digit++;
                $digit++;
            }
            $quadKey .= $digit;
        }
        return $quadKey;
    }

    public static function latLongToQuadKeyDec( $lat, $long, $zoom)
    {
        return self::quadKey4ToDec( self::latLongToQuadKey( $lat, $long, $zoom));
    }

    public static function latLongToQuadKey( $lat, $long, $zoom)
    {
        $pixelX = self::longToPixelX( $long, $zoom);
        $pixelY =self::latToPixelY( $lat, $zoom);
        $tileX = self::pixelXToTileX( $pixelX);
        $tileY = self::pixelYToTileY( $pixelY);
        return self::tileXYToQuadKey( $tileX, $tileY, $zoom);
    }

    public static function pixelXYToQuadKey( $pixelX, $pixelY, $zoom)
    {
        $tileX = self::pixelXToTileX( $pixelX);
        $tileY = self::pixelYToTileY( $pixelY);
        return self::tileXYToQuadKey( $tileX, $tileY, $zoom);
    }

    public static function pixelXYToQuadKeyDec( $pixelX, $pixelY, $zoom)
    {
        return self::quadKey4ToDec(self::pixelXYToQuadKey( $pixelX, $pixelY, $zoom));
    }

    public static function quadKeyToTileXY( $quadKey, &$tileX, &$tileY, &$levelOfDetail)
    {
        $tileX = $tileY = 0;
        $levelOfDetail = strlen( $quadKey);
        for( $i = $levelOfDetail; $i > 0; $i--)
        {
            $mask = 1 << ($i - 1);
            switch( $quadKey[$levelOfDetail - $i])
            {
                case '0':
                    break;

                case '1':
                    $tileX |= $mask;
                    break;

                case '2':
                    $tileY |= $mask;
                    break;

                case '3':
                    $tileX |= $mask;
                    $tileY |= $mask;
                    break;

                default:;
            }
        }
    }

    public static function tileXYToQuadKeyDec( $tileX, $tileY, $levelOfDetail)
    {
        return self::quadKey4ToDec(self::tileXYToQuadKey( $tileX, $tileY, $levelOfDetail));
    }

    public static function quadKey4ToDec($quadkey)
    {
        return base_convert( $quadkey, 4, 10);
    }

    public static function quadKeyDecTo4($quadkey)
    {
        return base_convert( $quadkey, 10, 4);
    }

    public static function getQuadKeyByViewport($latHR, $lonHR, $latLL, $lonLL ,$zoom = null){
        $q1 = self::latLongToQuadKey($latHR,$lonHR,self::MaxZoom);
        $q2 = self::latLongToQuadKey($latLL,$lonLL,self::MaxZoom);

        $qCommon = '';
        for ($i = 0;$i < self::MaxZoom && $q1[$i]==$q2[$i];$i++){
            $qCommon.=$q1[$i];
        }

        if($zoom!=null)
            $zoom = strlen($qCommon);

        return $qCommon;
    }

    public static function getClusters( $quad_key, $zoom)
    {
        $q1 = $quad_key . str_repeat("0", TileSystem::MaxZoom - $zoom);
        $q2 = $quad_key . str_repeat("3", TileSystem::MaxZoom - $zoom);
        $mask_plus = 1;
        $mask = $quad_key . str_repeat("3", $mask_plus);
        $mask = $mask . str_repeat("0", TileSystem::MaxZoom - $zoom - $mask_plus);

        $q1 = TileSystem::quadKey4ToDec( $q1);
        $q2 = TileSystem::quadKey4ToDec( $q2);
        $mask = TileSystem::quadKey4ToDec($mask);

        return ['quadCodeLeft'=>$q1,'quadCodeRight'=>$q2,'mask'=>$mask];
    }
}