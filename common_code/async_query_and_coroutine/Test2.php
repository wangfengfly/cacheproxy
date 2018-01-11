<?php

/**
 * Author: wangfeng
 * Date: 2018/1/11
 * Time: 16:27
 */
class Test2{

    public function run(){
        function book(){
            $db = new DB();
            $mysqli = $db->query('select * from book limit 2');
            echo "book query sent\n";
            yield $mysqli;
            $rows = $db->fetch_rows();
            yield $rows;
        }

        function site(){
            $db = new DB();
            $mysqli = $db->query('select * from site where id=1');
            echo "site query sent\n";
            yield $mysqli;
            $row = $db->fetch_row();
            yield $row;
        }

        $book = book();
        $site = site();

        $book->current();
        $site->current();

        $book->next();
        $site->next();

        $bd = $book->current();
        $sd = $site->current();
        var_dump($bd);
        var_dump($sd);
    }

}