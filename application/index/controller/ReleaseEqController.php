<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

use app\index\service\impl\ReleaseEqServiceImpl;


class ReleaseEqController extends Controller {

  	public function releaseEarthquake() {
		$request = Request::instance();
		$data = $request->param();
		$releaseEqServiceImpl = new ReleaseEqServiceImpl();
		return $releaseEqServiceImpl->releaseEq($data);
	}
	  
}
