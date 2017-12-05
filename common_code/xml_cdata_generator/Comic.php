<?php
/**
 * Created by PhpStorm.
 * User: aleser
 * Date: 2017/10/31
 * Time: 10:43
 */

class Comic extends Page_MBase {
    const LIST_URL = 'http://www.mhshu.com/Sogou/recent/';
    const MANHUA_URL = 'http://www.mhshu.com/Sogou/comic/';
    const CHAPTERS_URL = 'http://www.mhshu.com/Sogou/chapters/';
    private $conn;
    private $xmlpath;
    private $builder;
    const XML_URL = 'http://10.134.109.235:8088/xml/';

    public function __construct(){
        $this->conn = new SOSO_Base_Data_Connection();
        $this->xmlpath = __DIR__.'/../../xml/';
        $this->builder = new Util_ArrayToXML();
    }

    public function run(){
        global $argv;
        if(isset($argv[2]) && is_callable(array($this,$argv[2]))){
            $method = $argv[2];
            $this->$method();
        }
    }

    public function getManhua(){
        $ids = $this->getManhuaIds();
        if(is_array($ids) && count($ids)>0){
            $booktotal = 0;
            $locs = array();
            $datalist = array();
            $data = array();
            foreach ($ids as $bookid){
                if($booktotal>=20){
                    $data['sdd']['datalist'] = $datalist;
                    $xmlstr = $this->builder->buildXML($data, null);
                    $now = time();
                    $filename = $this->xmlpath.$now.'.xml';
                    file_put_contents($filename, $xmlstr);
                    $locs[] = self::XML_URL.$now.'.xml';
                    $booktotal=0;
                    $datalist = array();
                    $data = array();
                }
                $params = array('bookId'=>$bookid, 'timestamp'=>time());
                $params['sign'] = md5(http_build_query($params).'&SECRET');
                $url = self::MANHUA_URL.'?'.http_build_query($params);
                $book_data = $this->conn->get($url);
                $chapter_data = $this->conn->get(self::CHAPTERS_URL.'?'.http_build_query($params));
                $book_data = json_decode($book_data, true);
                $chapter_data = json_decode($chapter_data, true)['data']['chapters'];

                $item = $book_data['data'];
                $item['webName'] = '看漫画';
                $item['vip'] = 0;
                $item['startChapter'] = 1;
                $item['latestChapter'] = count($chapter_data)-1;
                //add cdata
                foreach($item as $k=>$v){
                    $item['#'.$k] = $v;
                    unset($item[$k]);
                }

                foreach($chapter_data as $i=>$cha){
                    $cha['seq'] = $i;
                    foreach($cha as $k=>$v){
                        $cha['#'.$k] = $v;
                        unset($cha[$k]);
                    }
                    $item['freechapter'][] = array('cha'=>$cha);
                }
                $datalist[] = array('item'=>$item);
                $booktotal++;
            }
            //不足20个的处理
            if($booktotal>0){
                $data['sdd']['datalist'] = $datalist;
                $xmlstr = $this->builder->buildXML($data, null);
                $now = time();
                $filename = $this->xmlpath.$now.'.xml';
                file_put_contents($filename, $xmlstr);
                $locs[] = self::XML_URL.$now.'.xml';
            }

            $sitemapindex = array();
            foreach($locs as $loc){
                $sitemapindex[] = array('loc'=>$loc, 'lastmod'=>date('Y-m-d'));
            }
            $index = $this->fetch('vr/tpl.index.htm', array('data'=>$sitemapindex));
            file_put_contents($this->xmlpath.'index.xml', $index);
        }
    }

    private function getManhuaIds(){
        $list_data = $this->conn->get(self::LIST_URL);
        $list_data = json_decode($list_data, true);
        if($list_data['code']=='0') {
            return $list_data['data']['list'];
        }
    }

}