<?php

/**
 * Author: wangfeng
 * Date: 2018/1/11
 * Time: 16:06
 * 当业务中需要多次查库，然后合并结果时，
 * 可以用异步查询+协程方式
 * 因为查库操作是IO，比较耗时
 */
class DB{
    private $mysqli;

    public function __construct(){
        $host = '';
        $username = '';
        $passwd = '';
        $database = '';
        $this->mysqli = new mysqli($host, $username, $passwd, $database);
    }

    public function __destruct(){
        $this->mysqli->close();
    }

    /**
     * @param $sql
     * @return mixed
     * 异步查询
     */
    public function query($sql){
        $this->mysqli->query($sql, MYSQLI_ASYNC);
        return $this->mysqli;
    }

    /**
     * @return mixed
     * 从结果集中获取一行记录
     */
    public function fetch_row(){
        $links = array($this->mysqli);
        $done = 0;
        do{
            $_links = $_errors = $_reject = $links;
            if(!mysqli_poll($_links,$_errors,$_reject,1)){
                continue;
            }
            foreach($_links as $link){
                if($result = $link->reap_async_query()){
                    $row = $result->fetch_array(MYSQLI_ASSOC);
                    if(is_object($result)){
                        mysqli_free_result($result);
                    }
                    return $row;
                }else{
                    die(sprintf("MySQLi Error: %s", mysqli_error($link)));
                }
                $done++;
            }
        }while($done<count($links));
    }

    /**
     * @return mixed
     * fetch_row的第二种实现
     */
    public function fetch_row2(){
        $links = array($this->mysqli);

        $_links = $_errors = $_reject = $links;
        $i = 0;
        while(!mysqli_poll($_links,$_errors,$_reject,1) && $i<20){
            $i++;
        }
        if($i>=20){
            die('mysqli_poll error');
        }

        if(count($_links)>0){
            $link = $_links[0];
            if($result = $link->reap_async_query()){
                $row = $result->fetch_array(MYSQLI_ASSOC);
                if(is_object($result)){
                    mysqli_free_result($result);
                }
                return $row;
            }else{
                die(sprintf("MySQLi Error: %s", mysqli_error($link)));
            }
        }
        return false;
    }

    /**
     * @return array
     * 从结果集中获取多行
     */
    public function fetch_rows(){
        $links = array($this->mysqli);
        $done = 0;
        do{
            $_links = $_errors = $_reject = $links;
            if(!mysqli_poll($_links,$_errors,$_reject,1)){
                continue;
            }
            foreach($_links as $link){
                if($result = $link->reap_async_query()){
                    $rows = array();
                    while($row = $result->fetch_array(MYSQLI_ASSOC)){
                        $rows[] = $row;
                    }
                    if(is_object($result)){
                        mysqli_free_result($result);
                    }
                    return $rows;
                }else{
                    die(sprintf("MySQLi Error: %s", mysqli_error($link)));
                }
                $done++;
            }
        }while($done<count($links));
    }

}