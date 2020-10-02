<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use app\index\service\impl\ScreenServiceImpl;

class ScreenController extends Controller {

    public function autoScreen() {
        $ScreenServiceImpl = new ScreenServiceImpl;
        return $ScreenServiceImpl->writeBat();
    }

}
