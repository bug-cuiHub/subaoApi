<?php

namespace app\index\service\impl;

use app\index\service\IMultiPushService;
use app\index\model\PublishedSeismic;
use app\index\util\WechatPush;


class MultiPushServiceImpl implements IMultiPushService
{

    public function wechatPush($eqUnique)
    {
        $wechatPush = new WechatPush();
        $publishedSeismic = new PublishedSeismic();
        $res = $publishedSeismic->wechatPush($eqUnique);
        $resAll = array($res, $eqUnique);
        return $resAll;        
        //$wechatPush->push($res, $eqUnique);

        
		// $allData = array(
        //     array(
        //         'district'=> $data['district'],
        //         'lon'=> $data['lon'],
        //         'lat'=> $data['lat'],
        //         'time'=> $data['time'],
        //         'magn'=> $data['magn'],
        //     )
		// );  
    }
}
