<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/15/015
 * Time: 23:55
 */
class Downloadfile{
    public $title = "";
    public $writer = "";
    public $abstract = "";
    public $subject = "";
    public $source = "";
    public $filename = "";
    public $storagepath = "";
    public $sitetype = "";
    public $page = "";
    public $size = "";


    public function getdownloadfile($filename){
        return null;
    }

    public function inseartfile(Downloadfile $downloadfile){
        echo $downloadfile->page;
        return true;
    }

}
