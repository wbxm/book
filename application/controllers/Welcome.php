<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use QL\QueryList;
class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
//		//$this->load->view('welcome_message');
//        //采集某页面所有的图片
//        //$data = QueryList::get('http://cms.querylist.cc/bizhi/453.html')->find('img')->attrs('src');
//        //打印结果
//        //print_r($data->all());
//
        $url = "http://wap.cnki.net/touch/usercenter/Account/Validator";
//        //$url = "http://httpbin.org/post";
        $ql = QueryList::getInstance();
        $jar = new \GuzzleHttp\Cookie\CookieJar();
//        dump($jar);
//        $get = $ql->get('http://wap.cnki.net/touch/usercenter',[],[
//            'cookies' => $jar
//        ]);
//        dump($jar);
//
//
//
//
        $ql->post($url,[
            'username' => 'sztsgzw',
            'password' => '400231',
            'keeppwd' => 'keepPwd',
            'app:' => ''
        ],[
            'proxy' => '192.168.2.40:8888',
            'cookies' => $jar
        ]);

        dump($jar);

        dump($jar->toArray());
        $jsonjar = json_encode($jar->toArray());

        $jararr = json_decode($jsonjar);

        //$jarout = $jar->fromArray($jararr);
        $jar->clear();
        dump($jar);
        foreach ($jararr as $cookiearr)
        {
           $str = "";
           foreach ($cookiearr as $key=>$value)
           {
               $str .= $key."=".$value.";";
           }
            $cookie =  $jar->SetCookie(GuzzleHttp\Cookie\SetCookie::fromString($str));
           dump($cookie);
           //$cookie =  GuzzleHttp\Cookie\SetCookie::fromString();
        }

        dump($jar);
        $ql->get('http://wap.cnki.net/touch/usercenter/Zone/Index',[],['cookies' => $jar,'proxy' => '192.168.2.40:8888']);

        echo $ql->getHtml();

	}

}
