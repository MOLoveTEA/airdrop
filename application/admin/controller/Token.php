<?php
namespace app\admin\controller;

/**
 * 队伍管理
 */
class Token extends Base
{
    public function __construct(){
        parent::__construct();
    }
    
    protected function listing($model, $cdt, $page)
    {
        return $model->where($cdt)
            ->order($this->order)
            ->field("*")->select();
    }

    protected function dataAdapter(&$input) {
        $input['expired'] = strtotime($input['expired']);
    }

}
