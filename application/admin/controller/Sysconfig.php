<?php
namespace app\admin\controller;

/**
 * 系统配置项
 */
class Sysconfig extends Base
{
    protected $pk = 'id';
    protected $order = 'listorder';
    
    protected function listing($model, $cdt, $page)
    {
        //var_dump($model);exit;
        return $model->alias('a')->where($cdt)
            ->order($this->order)
            ->field("*")->select();

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

}
