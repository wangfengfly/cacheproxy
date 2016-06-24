<?php
/**
 * Created by wangfengfly.
 * User: wangfeng211731
 * Date: 2016/6/24
 * Time: 17:11
 */

class Config{
    private $filename;
    private static $instance;

    private function __construct($filename)
    {
        $this->filename = $filename;
    }

    public static function getInstance($filename){
        if(is_null(self::$instance))
            self::$instance = new Config($filename);
        return self::$instance;
    }


}