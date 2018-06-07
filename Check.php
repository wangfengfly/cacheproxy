<?php

/**
 * Author: wangfeng
 * Date: 2018/6/6
 * Time: 17:13
 * 检测笑话中是否包含敏感词
 */
ini_set('memory_limit', -1);
set_time_limit(0);
class Cron_Check{
    private $filename;
    const PAGESIZE = 1000;
    private $logger;
    const WORDS_PER_PROCESS = 300;//每个进程处理的单词数

    public function run(){
        global $argv;
        if(count($argv) < 3){
            exit('Usage: php entry.php Cron_CheckSensitive filename.csv');
        }
        $this->filename = $argv[2];

        $words = file($this->filename);
        if(!$words){
            exit('file content is empty.');
        }

        $this->logger = HahaLog::getLogging('cli');
        $this->logger->info('begin');

        $data = array();
        while($res = array_splice($words,0,self::WORDS_PER_PROCESS)){
            $data[] = $res;
        }

        $pids = array();
        foreach($data as $i=>$item){
            $pid = pcntl_fork();
            if($pid < 0){
                die('cannot fork');
            }else if($pid){
                $pids[] = $pid;
            }else{
                $joke = new Joke();
                $command = $joke->getCommand();
                foreach($item as $word){
                    $word = trim($word);
                    $this->logger->info('word='.$word);
                    do {
                        $time = date('Y-m-d H:i:s');
                        $unpass = Joke::STATUS_UNPASS;
                        $pass = Joke::STATUS_PASS;
                        $sql = "update joke set status=$unpass,update_time='$time' where id!=317217 and id!=63499 and status=$pass and (";
                        $sql .= "title like '%" . $word . "%' ";
                        $sql .= " or text like '%" . $word . "%' ";
                        $sql .= " or tag like '%" . $word . "%') limit 10";
                        $pdo = $command->prepare($sql);
                        $res = $pdo->execute();
                        if($res == false){
                            exit(1);
                        }
                        $count = $pdo->rowCount();
                        if($count){
                            $this->logger->info($sql);
                            $this->logger->info('count='.$count);
                        }
                    }while($count);
                }
                $this->logger->info('Done');
                exit(0);
            }
        }

        foreach($pids as $pid){
            $status = 0;
            pcntl_waitpid($pid, $status);
            $this->logger->info("$pid exit with status: $status");
        }
        $this->logger->info('all done.');
    }

}