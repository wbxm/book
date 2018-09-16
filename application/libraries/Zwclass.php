<?php
use QL\QueryList;

class Zwclass{
    private $baseurl = "http://wap.cnki.net/";

    private $uservalidate = "touch/usercenter/Account/Validator";
    private $searchurl = "touch/web/Article/Search";
    private $logouturl = "touch/usercenter/Home/Logout";
    private $cookiefile = "application/cache/zw.cookie";


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
        $this->useraname = $this->CI->config->item('zwusername');
        $this->password = $this->CI->config->item('zwpassword');
    }

    //获取下载文件存储目录
    public function getconfigs(){
        $this->storagedir = $this->CI->config->item('storagedir');
    }

    //设置用户名密码
    public function setconfigup($username,$password){
        $this->CI->config->set_item('zwusername',$username);
        $this->CI->config->set_item('zwpassword',$password);
        $this->getconfig();
    }

    public function login(){
        $url = $this->baseurl.$this->uservalidate;
        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile);
        $ql->post($url,[
            'username' => $this->useraname,
            'password' => $this->password,
            'keeppwd' => 'keepPwd',
            'app:' => ''
        ],[
            'cookies' => $jar
        ]);
        $html = $ql->getHtml();
        $json = json_decode($html);
        dump($json);
    }

    public function getlist($keyword,$page = 1){
        $url = $this->baseurl.$this->searchurl;
        $postdata = [
            'searchtype' => '0',
            'dbtype' => '',
            'pageindex' => "$page",
            'pagesize' => 10,
            'theme_kw' => '',
            'title_kw' => '',
            'full_kw' => '',
            'author_kw' => '',
            'depart_kw' => '',
            'key_kw' => '',
            'abstract_kw' => '',
            'source_kw' => '',
            'teacher_md' => '',
            'catalog_md' => '',
            'depart_md' => '',
            'refer_md' => '',
            'name_meet' => '',
            'collect_meet' => '',
            'keyword' => "$keyword",
            'remark' => '',
            'fieldtype' => '101',
            'sorttype' => '0',
            'articletype' => '0',
            'screentype' => '0',
            'isscreen' => '',
            'subject_sc' => '',
            'research_sc' => '',
            'depart_sc' => '',
            'sponsor_sc' => '',
            'author_sc' => '',
            'teacher_sc' => '',
            'subjectcode_sc' => '',
            'researchcode_sc' => '',
            'departcode_sc' => '',
            'sponsorcode_sc' => '',
            'authorcode_sc' => '',
            'teachercode_sc' => '',
            'starttime_sc' => '',
            'endtime_sc' => '',
            'timestate_sc' => ''
        ];
        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\FileCookieJar($this->cookiefile);
        $ql->post($url,$postdata,[
            'cookies' => $jar
        ]);

        $allnum = $ql->find('#totalcount')->text();
        $rules = [
            'title' => ['.c-company__body-title','text'],
            'url' => ['.c-company-top-link','href'],
            'abstract' => ['.c-company__body-content','text'],
            'writer' => ['.c-company__body-author','text'],
            'source' => ['.c-company__body-name .color-green','text']
        ];

        $get = $ql->rules($rules)->range('#searchlist_div .c-company__body-item')->query();
        $list = $ql->getData()->all();

        dump($allnum,$list,$ql);
        //echo $ql->getHtml();
    }
}

