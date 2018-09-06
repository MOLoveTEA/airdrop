<?php
namespace app\api\controller;
use app\common\HttpsRequest;
use think\facade\Log;

class Login extends Base
{
    /**
     * 1、请求很常用，可以做个公有类
     * 2、函数返回记得return
     * 3、session_id方法用错了
     */

    public function weixin($jscode)
    {
        $APPID = config('weixin.appid');
        $SECRET = config('weixin.appsecret');

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $APPID . '&secret=' . $SECRET . '&js_code=' . $jscode . '&grant_type=authorization_code';
        $res = HttpsRequest::get($url);
        Log::record($url);
        Log::record($res);

        if (isset($res['openid'])) {
            $model = model('admin/User');

            // 用户id可以一次拿出来，isExistsOpenid直接返回id即可，一定要减少数据库操作
            $uid = $model->isExistsOpenid($res['openid']);
            if (!$uid) {
                $uid = db('user')->insertGetId(['openid' => $res['openid'], 'ctime' => time(), 'mtime' => time()]);
            }
            session('user.id', $uid);
            session('user.openid', $res['openid']);
            session('user.session_key', $res['session_key']);
            $data = [
                'phpsessid' => session_id(),
                'expire'    => config('session.expire') + time(),
            ];
            return $this->output($data);
        }
        return $this->outputError(404);

    }

}
