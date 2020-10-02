<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;
$originList = array(
    'http://localhost:8080',
    'http://192.168.16.44:8080',
);

if (in_array($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'], $originList)) {
    header('Access-Control-Allow-Origin:' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT']);
}
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE");
header("Access-Control-Allow-Headers: application/x-www-form-urlencoded,DNT,X-Mx-ReqToken,Keep-Alive,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type, Accept-Language, Origin, Accept-Encoding");

// UserController
Route::post('api/user/test', 'UserController/test');
Route::post('api/user/login', 'UserController/login');
Route::get('api/user/createVerifyCode', 'UserController/createVerifyCode');
Route::get('api/user/isLogin', 'UserController/isLogin');
Route::get('api/user/logout', 'UserController/logout');

// MultiPushController
Route::get('api/push/webPush', 'MultiPushController/webPush');
Route::get('api/push/phonePush', 'MultiPushController/phonePush');
Route::get('api/push/wechatPush', 'MultiPushController/wechatPush');

// EarthInfoController
Route::post('api/obtain/eqData', 'EarthInfoController/getEqData');
Route::get('api/obtain/getPushData', 'EarthInfoController/getPushData');
Route::post('api/obtain/analysisPushData', 'EarthInfoController/analysisPushData');
Route::post('api/obtain/query_date', 'EarthInfoController/query_date');
Route::post('api/obtain/getNearEarth', 'EarthInfoController/getNearEarth');
Route::post('api/obtain/getHisSearchData', 'EarthInfoController/getHisSearchData');
Route::post('api/obtain/getSearchThdData', 'EarthInfoController/getSearchThdData');


// ReleaseEqController
Route::post('api/release/releaseEq', 'ReleaseEqController/releaseEarthquake');

//ScreenController
Route::post('api/screen/autoScreen', 'ScreenController/autoScreen');

//test
Route::post('api/arcgis', 'Index/arcgis');
Route::get('api/getarcgis', 'Index/getArcgis');


return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
];
