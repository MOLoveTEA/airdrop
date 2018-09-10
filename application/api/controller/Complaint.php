<?php
namespace app\api\controller;

class Complaint extends Base
{
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
