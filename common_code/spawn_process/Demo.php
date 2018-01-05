<?php

/**
 * Author: wangfeng
 * Date: 2018/1/5
 * Time: 14:32
 */
class Cron_Demo{
    private $m;

    public function __construct(){
        $this->m = 1;
    }

    public function test1($val1, $val2){
        echo "test1\n";
        $val1 += $val2;
        sleep(2);
        return $val1;
    }

    public function test2($val1){
        echo "test2\n";
        $val1 *= 2;
        return $val1;
    }

    public function run(){
        $sp = new SpawnProcess(array(array($this,'test1',array(1,2)), array($this,'test2',array(50))));
        $sp->run();
    }

}