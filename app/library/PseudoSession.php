<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 20.12.2018
 * Time: 9:52
 */

namespace App\Libs;

class PseudoSession
{
    private $data;

    public function set($name,$data){
        $this->data[$name] = $data;
    }

    public function unset($name){
        unset($this->data[$name]);
    }

    public function get($name){
        return $this->data[$name];
    }
}