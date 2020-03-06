<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 12.08.2018
 * Time: 9:17
 */

namespace App\Libs;

class ImageLoader
{
    const RESULT_ERROR_FORMAT_NOT_SUPPORTED = 1;
    const RESULT_ERROR_NOT_SAVED = 2;
    const RESULT_ALL_OK = 0;

    public static function load($subpath, $tempname, $imagename,$subdir, $width)
    {
        $imageFormat = pathinfo($imagename, PATHINFO_EXTENSION);

        $format = $imageFormat;
        if ($imageFormat == 'jpeg' || 'jpg')
            $imageFormat = IMAGETYPE_JPEG;
        elseif ($imageFormat == 'png')
            $imageFormat = IMAGETYPE_PNG;
        elseif ($imageFormat == 'gif')
            $imageFormat = IMAGETYPE_GIF;
        else {
            return ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED;
        }

        $image = new SimpleImage();
        $image->load($tempname);
        if($width!= null)
            $image->resizeToWidth($width);

        SupportClass::writeMessageInLogFile('Проверка на существовании директории '.IMAGE_PATH . $subpath . '/'. $subdir);
        if(!is_dir(IMAGE_PATH . $subpath . '/'. $subdir)) {
            SupportClass::writeMessageInLogFile('Нужной директории нет');
            $result = mkdir(IMAGE_PATH . $subpath . '/' . $subdir);
            SupportClass::writeMessageInLogFile('Результат создания директории '.IMAGE_PATH . $subpath . '/'. $subdir
                    .' = '.$result);
            $r = is_dir(IMAGE_PATH . $subpath . '/' . $subdir);
        }

        $result = $image->save(IMAGE_PATH . $subpath . '/'. $subdir .'/' . $imagename, $format);

        if($result)
            return ImageLoader::RESULT_ALL_OK;
        else{
            return ImageLoader::RESULT_ERROR_NOT_SAVED;
        }
    }

    public static function saveMiniPicture($image_name, $new_name, $x, $y, $width, $height){
        SupportClass::writeMessageInLogFile('Original image name: "'.$image_name.'"');
        SupportClass::writeMessageInLogFile('Miniature name: "'.$new_name.'"');
        SupportClass::writeMessageInLogFile('x='.$x.', y='.$y.', width='.$width.', height='.$height);
        $image = new SimpleImage();
        $load_result = $image->load($image_name);

        if(!$load_result){
            SupportClass::writeMessageInLogFile('Unable load image');
        }

        return $image->image_crop($new_name,$x,$y,$width,$height);
    }

    public static function formImageName($format, $imageId)
    {
        return $imageId . '.' . $format;
    }

    public static function formFullImageName($subpath, $format, $id, $imageId)
    {
        return IMAGE_PATH_TRUNCATED . $subpath . '/'.$id.'/' .ImageLoader::formImageName($format,$imageId);
    }

    public static function formDirName($subpath, $id)
    {
        return IMAGE_PATH_TRUNCATED . $subpath . '/'.$id;
    }

    public static function formFullImagePathFromImageName($subpath, $id, $imageName)
    {
        return IMAGE_PATH_TRUNCATED . $subpath . '/'.$id.'/' .$imageName;
    }

    public static function delete($imageName){
        $result = unlink(BASE_PATH."/public/".$imageName);
        return $result;
    }

    public static function getImageName(string $fullname){
//        $i = 0;
//        for($i=strlen($fullname)-1; $i > 0 && $fullname[$i]!='/' && $fullname[$i]!="\\";$i--);
//
//        $image_name = substr($fullname,$i+1);
//        $path = substr($fullname,0,$i+1);
        $path = pathinfo($fullname,PATHINFO_DIRNAME);
        $ext = pathinfo($fullname,PATHINFO_EXTENSION);
        $image_name = pathinfo($fullname,PATHINFO_FILENAME);

        return ['name'=>$image_name,'path'=>$path, 'ext'=>$ext];
    }
}