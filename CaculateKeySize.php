<?php
/**
 * Created by PhpStorm.
 * User: aleser
 * Date: 2017/11/3
 * Time: 15:08
 */

class CaculateKeySize{

    public function run(){
        $command = 'redis-cli -h a.redis.sogou -p 1976 -a hahaunimportant';
        $keys = shell_exec($command.' keys "*"');
        $keys = explode("\n", $keys);
        $total = 0;
        $content = array();
        foreach($keys as $key){
            if($key){
                $ret = shell_exec($command." debug object $key");
                $ret = explode(' ', $ret);
                $size = explode(':', $ret[4]);
                $total += $size[1];
                $content[$key] = $size[1];
            }
        }
        arsort($content);
        $str = '';
        foreach($content as $key=>$size){
            $str .= $key."\t".$size."\n";
        }
        file_put_contents('./size.csv', $str);
        var_dump($total);
    }

}