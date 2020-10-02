<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
use think\Request;

use app\index\service\impl\UserServiceImpl;
use app\index\model\UserModel;
use app\index\util\VerifyCode;
use app\index\common\ServerResponse;


class UserController extends Controller {
    // public function test(){
    //     $request = Request::instance();
    //     $data = $request->param();
    //     var_dump($data);
    //     return $data['username'];
    // }
    public function te(){
        echo('ok');
    }

    //登录判断
    public function login() {
        $request = Request::instance();
        $data = $request->param();
        $userServiceImpl = new UserServiceImpl();
        return $userServiceImpl->login($data);
    }

    //创建验证码
    public function createVerifyCode() {
        $verifyCode = new VerifyCode();
        return $verifyCode->createVerifyCode();
    }

    public function isLogin() {
        $serverResponse = new ServerResponse();
        $res = Session::get('subaoID');
        if($res == null) {
            return $serverResponse->createByErrorMsg("您还未登录，需要登录!");
        }
        return $serverResponse->createBySuccess();
    }

    public function logout() {
        $serverResponse = new ServerResponse();
        Session::delete('subaoID');
        return $serverResponse->createBySuccess();
    }
}