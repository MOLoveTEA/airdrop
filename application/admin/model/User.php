<?php
namespace app\admin\model;

use think\Model;

class User extends Model
{
    protected $pk                 = 'id';
    protected $createTime         = 'ctime';
    protected $updateTime         = 'mtime';
    protected $autoWriteTimestamp = true;

    public function isExistsOpenid($openid){
       return $this->where('openid', $openid)->value('id');
    }
}
