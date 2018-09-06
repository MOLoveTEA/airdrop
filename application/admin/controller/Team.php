<?php
namespace app\admin\controller;
/**
 * 队伍管理
 */
class Team extends Base{

	protected function listing($model, $cdt, $page) {
		return $model->where($cdt)
			->order($this->order)
			->field("*")->select();
	}

	public function insert() {
		$input = input('post.');
		if ($input['id']) {
			$has_id = db($this->table)->where('id', $input['id'])->find();
			if ($has_id) {
				return $this->showError(500, '编号已存在');
			}
		} else {
			if (!$input['school'] || !$input['title']) {
				return $this->showError(500, '请输入学校或队名');
			}
			$input['id'] = 1 + db($this->table)->order('id desc')->limit(1)->value('id');
		}
		$res = db($this->table)->insert($input);
		if($res)
			return $this->output('操作成功');
		return $this->showError(500, '操作失败');
	}

}
