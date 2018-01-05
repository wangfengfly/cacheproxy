<?php

/**
 * Author: wangfeng
 * Date: 2018/1/4
 * Time: 15:51
 */
class Cron_Demo2{

    /*public function work($page, $pagesize){
        $book = new Book();
        $chapter = new Chapter();
        $deadlinks = array();

        $bookd = $book->orderBy('id',SOSO_ORM_Restrictions::ASC)->setPage($page, $pagesize)->find();
        if($bookd){
            $bids = array();
            foreach($bookd as $item){
                $bids[] = $item['id'];
            }
            $chapd = $chapter->in('bid', $bids)->find();
            foreach($chapd as $item){
                $url = $item['url'];
                $status = $this->getStatuscode($url);
                if($status != '200'){
                    $deadlinks[] = $url;
                }
            }
        }
        var_dump($deadlinks);
    }*/

    public function work($page, $pagesize){
        $db = new PDO('mysql:host=hostname;dbname=comic;charset=utf8', 'username', 'passwd');
        $deadlinks = array();
        $limit = ($page-1)*$pagesize;
        $stmt = $db->query("select * from book order by id asc limit $limit,$pagesize");
        $bookd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if($bookd){
            $bids = array();
            foreach($bookd as $item){
                $bids[] = $item['id'];
            }
            $in = '('.implode(',', $bids).')';
            $stmt = $db->query("select * from chapter where bid in $in");
            $chapd = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($chapd as $item){
                $url = $item['url'];
                $status = $this->getStatuscode($url);
                if($status != '200'){
                    echo "$url   $status\n";
                    $deadlinks[] = $url;
                }
                sleep(1);
            }
        }
        var_dump($deadlinks);
    }


    public function run(){
        $book = new Book();
        $n = $book->count();
        $pagesize = 1000;
        $pages = ceil($n/$pagesize);
        $funcs = array();
        for($i=1; $i<=$pages; $i++){
            $funcs[] = array($this, 'work', array($i,$pagesize));
        }
        $sp = new SpawnProcess($funcs);
        $sp->run();
    }

    private function getStatuscode($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6);
        $op = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code;
    }

}