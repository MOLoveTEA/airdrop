<?php
namespace app\spider\model;

use think\Model;

class Token extends Model
{
    protected $pk                 = 'id';
    protected $createTime         = 'ctime';
    protected $updateTime         = 'mtime';
    protected $autoWriteTimestamp = true;

    public function tokenDetail()
    {
        return $this->hasOne('TokenDetail', 'token_id');
    }

    //通过币种全称查询数据库是否存在
    public function isExistsToken($token_name)
    {
        return $this->where('fullname', $token_name)->find();

    }

    /**
     * 获取当前数据库所有的token
     * @return array
     */
    public function getCurrentToken()
    {
        $arr= $this->column('fullname');
        array_walk($arr,function(&$v,$k){$v=strtolower($v);});
        return $arr;

    }

    /**
     * 与数据库字段进行转换
     *
     * @param array $token 一维数组
     * @return void
     */
    public function mapping($token)
    {

        $map = config('dictionary.token');
        foreach ($map as $k => $v) {
            foreach ($token as $kToken => $vToken) {
                if ($k == $kToken) {
                    $token[$v] = $vToken;
                    unset($token[$kToken]);
                }
            }

        }
        return $token;

    }
}
