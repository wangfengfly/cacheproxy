<?php
/**
 * Created by wangfengfly.
 * User: wangfeng211731
 * Date: 2016/6/24
 * Time: 16:55
 */

class RedisPool{
    private static $instance = null;
    private $pool = null;
    private $conf_name;

    private function __construct($filename)
    {
        $this->conf_name = $filename;
    }

    public static function getInstance($filename){
        if(is_null(self::$instance) || self::$instance->conf_name != $filename)
            self::$instance = new RedisPool($filename);
        return self::$instance;
    }

    public function setConfigFilename($filename){
        $this->conf_name = $filename;
    }

    public function getRedisConnection($name){
        if(!extension_loaded("redis")){
            throw new Exception("redis not installed");
            return false;
        }

        if(!$this->pool[$name]){
            $this->pool[$name] = new Redis();
            $conf = parse_ini_file($this->conf_name, true);
            if(!isset($conf[$name]['host']) || !$conf[$name]['port']){
                throw new ErrorException("not set redis server hostname and port.");
                return false;
            }
            $con_res = $this->pool[$name]->pconnect($conf[$name]['host'], $conf[$name]['port'], 1);
            if($con_res === false){
                throw new Exception('connect to redis server failure');
                return false;
            }
        }

        return $this->pool[$name];

    }
}
