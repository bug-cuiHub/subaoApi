<?php

namespace app\index\controller;

use app\index\controller\MultiPushController;
use think\Db;
use think\Session;
use think\Request;
use think\Exception;

class Index
{
	public function Index()
	{
		echo ("hello world");
	}
	public function kow()
	{
		$MultiPushController = new MultiPushController();
		$MultiPushController->webPush();
	}
	public function arcgis()
	{
		$request = Request::instance();
		$data = $request->param();
		switch ($data["type"]) {
			case "add":
				try {
					var_dump($data);
					$data1 = [
						'id' =>  $data["id"],
						'latitude' =>  $data["latitude"],
						'longitude' =>  $data["longitude"],
						'position' =>  $data["position"],
						'info' =>  $data["info"]
					];
					$i = Db::table('arcgis')->insert($data1);
					return $i;
				} catch (Exception $e) {
					echo ($e->getMessage());
					return "pass";
				};
				break;
			case "updated":
				$data2 = [
					'id' => $data["id"],
					'latitude' =>  $data["latitude"],
					'longitude' =>  $data["longitude"],
					'position' =>  $data["position"],
					'info' =>  $data["info"]
				];
				var_dump($data2);
				$i = Db::table('arcgis')->where('id','=',$data2["id"])->update($data2);
				return $i;
				break;
			case "delete":
				$data2 = [
					'id' => $data["id"]
				];
				$i = Db::table('arcgis')->where('id','=',$data2["id"])->delete();
				return $i;
				break;
		}
	}
	public function getArcgis()
	{
		$result = Db::table('arcgis')
			->field("id, latitude, longitude, position, info")
			->select();
		return json_encode($result);
	}
}
