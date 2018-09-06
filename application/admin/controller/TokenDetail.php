<?php
namespace app\admin\controller;

/**
 * 队伍管理
 */
class TokenDetail extends Base
{
    protected $pk = 'token_id';
    protected $order = 'token_id desc';

    /**
	 * 编辑页显示
	 * @param  int $id
	 * @return void
	 */
	protected function editFilter($id) {
		$data = db($this->table)->where('token_id', $id)->find();
		$this->assign([
			'data'   => $data,
			'_title' => '编辑数据',
		]);
	}

}
