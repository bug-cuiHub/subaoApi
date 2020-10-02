<?php
namespace app\index\service\impl;

use think\Session;

use app\index\service\IUserService;
use app\index\model\UserModel;
use app\index\common\ServerResponse;
use app\index\common\ResponseCode;


class UserServiceImpl implements IUserService {

    // protected function escapeData($data) {
    //     return addslashes(strip_tags(trim($data)));
    // } 

    public function login($data) {
        $userModel = new UserModel();
        $serverResponse = new ServerResponse();
        $res = $userModel->verifyLogin($data['username']);
        
        // var_dump($data);
        // var_dump($this->escapeData($data['username']));
        // var_dump($this->escapeData($data['password']));
        
        if($res == null) {
            $msg = '用户名错误';
            return $serverResponse->createByErrorMsg($msg);
        } else {
            if ($res == md5($data['password'])) {
                if(Session::get('captcha') == strtolower($data['input_captcha']) || $data['input_captcha']=='success') { //success为万能验证码，ckw加
                    $msg = '登录成功';
                    Session::set('subaoID', $data['username']);
                    return $serverResponse->createBySuccessMsg($msg);
                } else {
                    $msg = '验证码错误';
                    return $serverResponse->createByErrorMsg($msg);
                }
            } else {
                $msg = '密码错误';
                return $serverResponse->createByErrorMsg($msg);
            }
        }
    }
}