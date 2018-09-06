<?php
namespace app\spider\model;

use think\Model;

class TokenDetail extends Model
{
    protected $pk                 = 'token_id';
    protected $createTime         = 'ctime';
    protected $updateTime         = 'mtime';
    protected $autoWriteTimestamp = true;

    public function token()
    {
        return $this->belongsTo('Token', 'id');
    }
}
