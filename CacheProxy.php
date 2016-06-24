<?php
/**
 * Created by wangfengfly.
 * Email: 985004673@qq.com
 * Date: 2016/6/24
 * Time: 16:35
 */

class CacheProxy{
    protected $obj = null;
    const TYPE_R = 0;
    const TYPE_W = 1;
    const TYPE_D = 2;
    private $t;
    private $redisname;

    public function __construct($obj, $redisname)
    {
        $this->obj = $obj;
        $this->t = self::TYPE_W;
        $this->redisname = $redisname;
    }

    public function setType($t){
        $this->t = $t;
    }

    public function __call($name, $arguments)
    {
        if(!method_exists($this->obj, $name)){
            exit("method: $name does not exist");
        }
        $k = $this->generateKey($name, $arguments);
        $redis = RedisPool::getInstance("config.ini")->getRedisConnection($this->redisname);
        if($redis === false){
            return false;
        }
        switch($this->t){
            case self::TYPE_R:
                $res = $redis->get($k);
                return $res;
            case self::TYPE_W:
                $res = call_user_func_array(array($this->obj, $name), $arguments);
                $redis->set($k, json_encode($res));
                return $res;
            case self::TYPE_D:
                return $redis->delete($k);

        }
    }

    protected  function generateKey($function, $param){
        $k = sprintf('function_%s_%s_%s', get_class($this->obj), $function, md5(json_encode($param, JSON_NUMERIC_CHECK)));
        return $k;
    }
}