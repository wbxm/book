<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use QL\QueryList;

class Qkclass {
    private $baseurl = "http://www.nssd.org/";
    private $loginurl = "login.aspx";
    private $codeurl = "ajax/getauthcode.ashx";
    private $uservalidate = "ajax/userinfo.ashx";
    private $searchurl = "articles/articlesearch.aspx";
    private $logouturl = "logout.aspx";
    private $cookiefile = "application/cache/qk.cookie";
    private $codefile = "application/cache/qkcode.gif";

    private $useraname = "";
    private $password = "";
    private $storagedir = "";
    private $CI;

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->CI->config->load('xmconfig');
        $this->getconfigup();
        $this->getconfigs();
    }

    //从配置文件读取用户名密码
    public function getconfigup(){
        $this->useraname = $this->CI->config->item('qkusername');
        $this->password = $this->CI->config->item('qkpassword');
    }

    //获取下载文件存储目录
    public function getconfigs(){
        $this->storagedir = $this->CI->config->item('storagedir');
    }

    //设置用户名密码
    public function setconfigup($username,$password){
        $this->CI->config->set_item('qkusername',$username);
        $this->CI->config->set_item('qkpassword',$password);
        $this->getconfig();
    }

    //搜索结果
    public function getlist($keyword,$page=null){
        $searchurl = $this->baseurl.$this->searchurl."?invokemethod=search&q=%7B\"search\"%3A\"".$keyword."\"%2C\"sType\"%3A\"all\"%7D&";
        if ($page){
            $searchurl = $this->baseurl.$this->searchurl."?invokemethod=search&q=%7B\"search\"%3A\"".$keyword."\"%2C\"sType\"%3A\"all\"%2C\"page\"%3A\"".$page."\"%7D&&hidpage=0&&hfldSelectedIds=&";
        }

        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile,true);

        $get = $ql->get($searchurl,null,[
            'cookies' => $jar
        ]);

        //搜索结果总数
        $allnum = $ql->find('.list_top .count i')->text();
        $num = intval(str_replace(',','',$allnum));

        $rules = [
            'title' => ['.title','text'],
            'url' => ['.title','href'],
            'abstract' => ['.abstract','text'],
            'writer' => ['.writer','text'],
            'subject' => ['.subject','text'],
            'source' => ['.media','text']
        ];

        $get = $get->rules($rules)->range('.full_list dl')->query();
        $list = $ql->getData()->all();

        $data['num'] = $num;
        $data['list'] = $list;
        //dump($data,gettype($data['list']),$this->useraname,$this->password);
    }

    public function storecode(){
        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile,true);
        $get = $ql->get($this->baseurl.$this->loginurl,null,[
            'cookies' => $jar
        ]);

        $get = $ql->get($this->baseurl.$this->codeurl,null,[
            'cookies' => $jar,
            'sink' => $this->codefile
        ]);
    }

    public function showcode(){
        header('content-type:image/gif;');
        readfile($this->codefile);
    }

    public function login($code){
        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile,true);
        $ql->post($this->baseurl.$this->uservalidate,[
            'type' => 'login',
            'zu' => $this->useraname,
            'zp' => $this->password,
            'zv' => $code
        ],[
            'cookies' => $jar
        ]);
        $res = $ql->getHtml();
        if ($res == 'ok'){
            return true;
        }
        else{
            return false;
        }
    }

    public function logout(){
        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile,true);

        $get = $ql->get($this->baseurl.$this->logouturl,null,[
            'cookies' => $jar
        ]);
    }
    public function islogin($html = null){
        $uid = "0";
        if($html){
            $uid = QueryList::html($html)->find('#hdzuserid')->val();
        }
        else{
            $ql = QueryList::getInstance();
            $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile, true);
            $ql->get($this->baseurl . $this->loginurl, null, [
                'cookies' => $jar
            ]);
            $uid = $ql->find('#hdzuserid')->val();
        }
        if ($uid == "" || $uid == "0" || $uid == null) {
            return false;
        } else {
            return true;
        }
    }

    public function articlecontent($articleurl,Downloadfile $downloadfile)
    {
        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile, true);

        $get = $ql->get($this->baseurl . $articleurl, null, [
            'cookies' => $jar
        ]);

        $allabstrack = $ql->find("#allAbstrack")->children()->remove()->end()->text();

        //有的页面里面没有内容
        if ($allabstrack == "")
            return null;
        $from = $downloadfile->source;
        preg_match("/共([\d,]*)页/", $from, $matches);
        $page = $matches[1];

        $downurl = $ql->find(".link .original a")->attr("href");
        $allurl = $this->baseurl . $downurl;
        $filename = md5($downurl) . '.pdf';
        $storgepath = $this->storagedir . '/' . $filename;
        $storgepath = str_replace("//","/",$storgepath);

        //判断是否下载过
//        $this->CI->load->model('Downloadfile', 'downloadfile');
//        $downloadfile = $this->CI->downloadfile;
        $file = $downloadfile->getdownloadfile($filename);
        if ($file == null) {
            $get = $ql->get($allurl, null, [
                'cookies' => $jar,
                'allow_redirects' => false
            ]);
            //302跳转路径
            $nexturl = $ql->find("h2 a")->attr("href");
            //是否需要登陆
            if (strpos($nexturl,"login.aspx")){
                return false;
            }
            else{
                $get = $ql->get($nexturl, null, [
                    'cookies' => $jar,
                    'sink' => $storgepath
                ]);
            }
            //保存入库
            $downloadfile->filename = $filename;
            $downloadfile->storagepath = $storgepath;
            $downloadfile->size = floatval(filesize($storgepath))/1024;
            $downloadfile->page = $page;
            $downloadfile->inseartfile($downloadfile);

        } else {
            return $file;
        }
    }

}