<?php
namespace app\api\controller;

class Complaint extends Base
{

    /**
     * 1、接口只要成功返回，无论是否有数据，状态码都应该是相同的。200是http的状态码，可以不需要接口直接回显。
     * 2、get参数接收不要放在函数内部。一般对于非表单提交的情况，都是用get方式
     * 3、列表接口，参数一定要有默认值。前端不可能保证每次都把那么多参数完整传过来的
     * 4、接口返回的数据主字段要统一，比如都放在data中。因为前端会封装请求方法，请求成功时直接拿data里面全部数据再处理。
     */

     /**
      * 吐槽
      *
      * @param integer $id      空投token_id
      * @param string $content  吐槽正文
      * @return void
      */
    public function make($id, $type = 1, $content = '')
    {   
    	return $this->output();
        $this->auth();
        $uid    = $this->uid;
        $data = [
            'tokenid' => $id,
            'user_id' => $uid,
            'text'    => $content,
            'ctime'   => time(),
        ];
        $res=db('complaint')->allowField(true)->isUpdate(false)->data($data, true)->save();
        $res && $this->output();
    }

}
