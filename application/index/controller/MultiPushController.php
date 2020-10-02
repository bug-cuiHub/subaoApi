<?php
namespace app\index\controller;

use think\Controller;
use think\Session;

use app\index\common\ServerResponse;
use app\index\util\IntegratePattern;
use app\index\service\impl\MultiPushServiceImpl;
use app\index\service\impl\EarthInfoServiceImpl;


class MultiPushController extends Controller {

    // web端推送
	public function webPush() {
		$serverResponse = new ServerResponse();
		$integratePattern = new IntegratePattern();
		$earthInfoServiceImpl = new EarthInfoServiceImpl();

		$eqUnique = Session::get('eqUnique');
		if($eqUnique) {
			$msg = "成功获取所发布的地震,Web端推送成功！";
			$integratePattern->sendLogInfo($msg);
			return $serverResponse->createBySuccessMsgData($msg, $eqUnique);
		} else {
			$msg = "未能获取所发布的地震,推送失败!";
			$integratePattern->sendLogInfo($msg);
            return $serverResponse->createByErrorMsg($msg);
		}
	}

	// 手机端推送
	public function phonePush() {
		$serverResponse = new ServerResponse();
		$integratePattern = new IntegratePattern();
        $eqUnique = Session::get('eqUnique');
        $ip = $this->getIP();
		if($eqUnique) {
			//$url = 'http://172.17.130.182:8080/#/home/' . $eqUnique;
			$url = 'http://'.$ip.':8080/#/home/' . $eqUnique;
			$msg = "成功获取所发布的地震,手机端推送成功！";
			$integratePattern->sendLogInfo($msg);
			return $serverResponse->createBySuccessMsgData($msg, $url);
		} else {
			$msg = "未能获取所发布的地震,推送失败!";
			$integratePattern->sendLogInfo($msg);
            return $serverResponse->createByErrorMsg($msg);
		}
	}

	// 微信推送
	public function wechatPush() {
		$integratePattern = new IntegratePattern();
		$serverResponse = new ServerResponse();
		$eqUnique = Session::get('eqUnique');
		if($eqUnique) {
			$multiPushServiceImpl = new MultiPushServiceImpl();
			$redata = $multiPushServiceImpl->wechatPush($eqUnique);
			$msg = "成功获取所发布的地震,微信推送成功！";
			$integratePattern->sendLogInfo($msg);
			return $serverResponse->createBySuccessMsgData($msg,$redata);
		} else {
			$msg = "未能获取所发布的地震,推送失败!";
			$integratePattern->sendLogInfo($msg);
            return $serverResponse->createByErrorMsg($msg);
		}
	}

	// 获取操作系统为win2000/xp、win7的本机IP真实地址(失效)
	protected function getIP() {
		$preg = "/\A((([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\.){3}(([0-9]?[0-9])|(1[0-9]{2})|(2[0-4][0-9])|(25[0-5]))\Z/";
		exec("ipconfig", $out, $stats);
		if(!empty($out)) {
			foreach($out as $row) {
				if(strstr($row, "IP") && strstr($row, ":") && !strstr($row, "IPv6")) {
					$tmpIp = explode(":", $row);
					if(preg_match($preg, trim($tmpIp[1]))) {
						return trim($tmpIp[1]);
					}
				}
			}
        }
    }
}