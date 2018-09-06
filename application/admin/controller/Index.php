<?php
namespace app\admin\controller;

class Index extends Base{

	public function index() 
	{
		return view();
	}
	
	/**
	 * 主页数据报表
	 */
	public function dashboard()
	{
		$total_user = db('User')->count();
		$total_token = db('Token')->count();
		$today_user = db('User')->where('ctime', '>', date('Y-m-d', time()))->count();
		$today_token = db('Token')->where('ctime', '>', date('Y-m-d', time()))->count();
		$this->assign([
			'total_user'  => $total_user,
			'total_token' => $total_token,
			'today_user'  => $today_user,
			'today_token' => $today_token
		]);
		return view();
	}

}
