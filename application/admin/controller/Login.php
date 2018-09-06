<?php
namespace app\admin\controller;

use think\facade\Env;
use think\facade\Url;
use Gregwar\Captcha\CaptchaBuilder;
Url::root('/admin.php?s=');
/**
 * 登录
 */
class Login
{

    private $codeKey = 'admin.login_verify';

    public function index()
    {

        return view('index');
    }

    /**
     * 输出验证码
     * @return resource
     */
    public function captcha()
    {
		$Captcha = new CaptchaBuilder();
		$Captcha->build()->output();
		$code = $Captcha->getPhrase();
        session($this->codeKey, $code);
    }

    /**
     * 获取当时时间
     * @return int
     */
    public function timestamp()
    {
        echo $_SERVER['REQUEST_TIME'];
    }

    /**
     * 进行身份验证
     * @return json
     */
    public function auth($username, $password, $code, $timestamp)
    {
        if (!session($this->codeKey) || $code != session($this->codeKey) || !$code) {
            session($this->codeKey, null);
            return $this->output(null, 412, '验证码错误');
        }
        // 数据验证
        $validate = validate('Login');
        if (!$validate->check(['username' => $username, 'password' => $password])) {
            return $this->output(null, 412, $validate->getError());
        }
        return $this->output();
    }

    /**
     * 注销登陆
     * @return redirect
     */
    public function out()
    {
        session('admin', null);
        return redirect('index');
    }

    /**
     * 数据输出
     * @param  array  $data     结果集
     * @param  integer $code    错误码
     * @param  string  $message 提示信息
     * @return json
     */
    protected function output($data = null, $code = 0, $message = 'success')
    {
        if (empty($data)) {
            return json(['code' => $code, 'message' => $message, 'data' => []]);
        }

        return json(['code' => $code, 'message' => $message, 'data' => $data]);
    }
}
