<?php
namespace app\admin\validate;
/**
* 评论验证
*/
class Login extends \think\Validate{

	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule = [
		// 'username' => 'require',
		'password' => 'require|checkPwd',
	];

	/**
	 * 错误提示
	 * @var array
	 */
	protected $message = [
		'username.require' => '用户名不能为空',
		'password.require' => '密码不能为空',
		'password.checkPwd' => '密码错误',
	];

	/**
	 * 自定义密码验证
	 * @param  int $value
	 * @param  string $rule
	 * @param  array $data 表单数据
	 * @return boolean
	 */
	protected function checkPwd($value, $rule, $data) {
		$admin = db('Admin')->where('username', $data['username'])->find();

		if ($admin['password'] === md5(md5($data['password']). $admin['salt'])) {
			session('admin.id', $admin['id']);
			session('admin.username', $admin['username']);
			return true;
		}
		return false;
	}
}