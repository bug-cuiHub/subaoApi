<?php
namespace app\index\util;


class WechatPush {

    public function push($Dbdata, $eqUnique) {
        $data = array(
            // 模板消息内容，根据模板详情进行设置
            'addres'    =>$Dbdata[0]['district'],
            'longtitude' => $Dbdata[0]['lon'],
            'lattitude' => $Dbdata[0]['lat'],
            'datatime' => $Dbdata[0]['time'],
            'magnitude' => $Dbdata[0]['magn'],
            'url' => 'http://172.17.130.182:8080/#/home/' . $eqUnique
        );

        $value = ['appid' => 'wx5957c95a261f6e8a','appsecret' => 'c47a5129aba06dc2fc161b4931503568'];
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$value['appid']."&secret=".$value['appsecret'];
        if (cookie('access_token')){
            $access_token=cookie('access_token');
        }else{
            $cont = json_decode($this->getToken($url));
            $access_token=$cont->access_token;
            setcookie('access_token',$access_token,7200);
        }//获取access_token
        $all = $this->get_allopenid($access_token);//获取关注的用户，因为只能推送关注的用户。
        $openids = $all['data']['openid'];//关注用户的openID
        $openids = array_values($openids);//这里是筛选没有退订的用户，并重新排序键值对。这些推送最好是可以退订的，不然用户举报可能会封号哦。我是根据用户回复“退订”关键词来标记的。这一步是有必要的。

        foreach($openids as $key => $value) {
            $params = json_encode($this->json_tempalte($value,$data),JSON_UNESCAPED_UNICODE);
            $fp = fsockopen('ssl://api.weixin.qq.com', 443, $error, $errstr, 1);
            $http = "POST /cgi-bin/message/template/send?access_token={$access_token} HTTP/1.1\r\nHost: api.weixin.qq.com\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($params) . "\r\nConnection:close\r\n\r\n$params\r\n\r\n";
            fwrite($fp, $http);
            $resp_str='';
            while(!feof($fp)) {
                $resp_str .= fgets($fp,512);//返回值放入$resp_str
            }
            var_dump($resp_str);
            fclose($fp);
            sleep(1);
        }//这里就是遍历推送到各个openID了、sleep（1）是为了防止推送过快出问题。
    }

    protected function getToken($url) {//这个方法是获取access_token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko)");
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
    protected function get_allopenid($access_token,$next_openid = '') {//这个方法是获取所有关注的用户的
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&next_openid=".$next_openid;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
        $data = curl_exec($ch);
        $data = json_decode($data,true);
        if ($data["count"] == 10000){
            $newdata = $this->get_allopenid($access_token,$data["next_openid"]);
            $data["data"]["openid"] = array_merge_recursive($data["data"]["openid"], $newdata["data"]["openid"]);
        }
        return $data;
    }
    protected function json_tempalte($openid,$data,$template_id = '1kHpyqPFgmfn5h6uA2164z78gJAWXgVWkI0qh2cTL38') {//这里是组装模版消息的方法
        $template=array(
            'touser'=>$openid,
            'template_id'=>$template_id,//这个是模版ID，获取方法自己看文档很详细。
            'url'=>$data['url'],
            'topcolor'=>"#7B68EE",
            'data'=>array(
                'addres'=>array('value'=>$data['addres'],'color'=>"#000"),
                'longtitude'=>array('value'=>$data['longtitude'],'color'=>'#F70997'),
                'lattitude'=>array('value'=>$data['lattitude'],'color'=>'#248d24'),
                'datatime'=>array('value'=>date("Y-m-d H:i:s"),'color'=>'#000'),
                'magnitude'  =>array('value'=>$data['magnitude'],'color'=>'#1784e8'),
                'url'=>array('value'=>$data['url'],'color'=>'#1784e8') )
        );
        return $template;
    }

}