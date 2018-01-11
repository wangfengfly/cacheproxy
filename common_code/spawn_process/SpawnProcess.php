<?php

/**
 * Author: wangfeng
 * Date: 2018/1/5
 * Time: 14:18
 * 多进程并行处理多个函数任务
 */
class SpawnProcess {
    private $pids;
    private $funcs;

    public function __construct(array $funcs){
        $this->funcs = $funcs;
    }

    public function run(){
        if(!$this->funcs){
            return false;
        }

        foreach($this->funcs as $func){
            $pid = pcntl_fork();
            if($pid == -1){
                echo "fork new process failed.\n";
                $this->waitpid();
                exit;
            }else if($pid == 0){
                $res = call_user_func_array(array($func[0], $func[1]), $func[2]);
                echo "res====$res\n";
                exit(0);
            }else{
                $this->pids[] = $pid;
            }
        }

        $this->waitpid();
        echo "done.\n";
    }

    private function waitpid(){
        foreach($this->pids as $_pid){
            pcntl_waitpid($_pid, $status);
            echo "pid===$_pid done, status=$status \n";
        }
    }

}