<?php
namespace app\api\controller;

use think\Controller;

class Base extends controller
{

    /* 状态码对应信息，可以自己补充 */
    protected $statusCode = [
        401 => '用户未登录',
        402 => '查询错误',
        403 => '表单验证不通过',
        404 => 'openid获取失败',
    ];

    /* 当前访问接口用户id */
    protected $uid;

    protected function initialize()
    {
        $this->uid = session('user.id');
    }

    /**
     * 定义一个公用授权方法。比如评论这些操作，需要用户登录的时候，直接调用这个方法就行了
     */
    public function auth()
    {
        if (null == session('user.id')) {
            $this->outputError(401);
            exit;
        }
    }

    /**
     * 正常输出数据
     * @param  array  $data 数据内容
     * @param  integer $code 状态码
     * @param  string  $msg  信息
     * @return json
     */
    public function output($data = null, $code = 0, $msg = 'ok')
    {
        if (empty($data)) {
            return json(['code' => $code, 'msg' => $msg, 'data' => []]);
        }

        return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
    }

    /**
     * 输出报错
     * @param  integer $code 状态码
     * @param  string $msg  错误信息
     * @return json
     */
    public function outputError($code, $msg = '')
    {
        if (empty($msg)) {
            $msg = $this->statusCode[$code];
        }

        return json(['code' => $code, 'msg' => $msg]);
    }

}
