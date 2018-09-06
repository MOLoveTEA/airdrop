<?php
namespace app\admin\controller;

/**
 * 队伍管理
 */
class Complaint extends Base
{
    protected $pk = 'id';
    protected $order = 'a.id desc';
    
    protected function listing($model, $cdt, $page)
    {
        //var_dump($model);exit;
        return $model->alias('a')->join('ts_token t','t.id=a.token_id')->where($cdt)
            ->order($this->order)
            ->field("a.*,t.fullname")->select();

    }

    	/**
	 * 编辑页
	 * @param  int $id
	 * @return response
	 */
	public function edit($id){
		$this->editFilter($id);
		$this->assign([
			'_actionUrl' => url('update', ['id' => $id]),
		]);
		return view('edit');
	}

    	/**
	 * 编辑页数据
	 * @param  int $id
	 * @return void
	 */
	protected function editFilter($id) {
		$data = db($this->table)->where('id', $id)->find();
		$this->assign([
			'data'   => $data,
			'_title' => '编辑数据',
		]);
	}
    // public function insert() {
    //     $input = input('post.');
    //     if ($input['id']) {
    //         $has_id = db($this->table)->where('id', $input['id'])->find();
    //         if ($has_id) {
    //             return $this->showError(500, '编号已存在');
    //         }
    //     } else {
    //         if (!$input['school'] || !$input['title']) {
    //             return $this->showError(500, '请输入学校或队名');
    //         }
    //         $input['id'] = 1 + db($this->table)->order('id desc')->limit(1)->value('id');
    //     }
    //     $res = db($this->table)->insert($input);
    //     if($res)
    //         return $this->output('操作成功');
    //     return $this->showError(500, '操作失败');
    // }

    // public function index(){
    //     db($this->table)->where()-select();
    // }
}
