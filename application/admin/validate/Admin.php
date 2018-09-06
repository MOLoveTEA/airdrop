<?php
namespace app\admin\validate;
/**
* 评论验证
*/
class Admin extends \think\Validate{

	/**
	 * 验证规则
	 * @var array
	 */
	protected $rule = [
		'username' => 'require|length:5,10|alphaDash|unique:admin',
		'password' => 'require|length:6,16',
	];

	/**
	 * 错误提示
	 * @var array
	 */
	protected $message = [
		'username.require'   => '用户名不能为空',
		'username.length'    => '用户名长度5~10',
		'username.alphaDash' => '用户名只能是字母和数字，下划线_及破折号-',
		'username.unique'    => '存在重复的用户名',
		'password.require'   => '密码不能为空',
		'password.length'    => '用户名长度6~16',
	];

	protected $scene = [
		'insert' => ['username','password'],
		'update' => ['password'],
    ];

}