<?php
namespace app\api\controller;

class User extends Base
{

	/**
	 * 获取联系我们的二维码
	 * @return array 
	 */
    public function linkus()
    {
        $data = db('sysconfig')->field('code_name, value')->where('type', 'QRCODE')->select();
        return $this->output($data);
    }

    /**
     * 获取各标签名称（主要用于微信过审）
     * @return array
     */
    public function labelName()
    {
    	$data = db('sysconfig')->field('code_name, value')->where('type', 'LABEL')->select();
    	return $this->output($data);
    }

}
