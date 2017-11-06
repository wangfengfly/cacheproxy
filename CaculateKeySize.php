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
        $keys = shell_exec($command.' scan 0');
        $keys = explode("\n", $keys);
        $total = 0;
        $content = array();
        while(count($keys)>0 && $keys[0]!=0){
            $cursor = $keys[0];
            $keysn = count($keys);
            for($i=1; $i<=$keysn; $i++){
                $key = $keys[$i];
                if($key){
                    $ret = shell_exec($command." debug object $key");
                    $ret = explode(' ', $ret);
                    $size = explode(':', $ret[4]);
                    $total += $size[1];
                    $content[$key] = $size[1];
                }
            }
            $keys = shell_exec($command." scan $cursor");
            $keys = explode("\n", $keys);
        }

        arsort($content);
        $str = '';
        $head = '';
        $i=0;
        foreach($content as $key=>$size){
            if($size) {
                $line = $key . ":\t" . $size . " Bytes\n";
                $str .= $line;
                if ($i <= 50) {
                    $head .= $line . "<br>";
                    $i++;
                }
            }
        }
        $filename = md5(trim($host).$passwd);
        file_put_contents("/tmp/$filename.csv", $str);
    }

}