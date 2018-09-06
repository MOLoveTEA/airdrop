<?php
namespace app\common;
class HttpsRequest {
	/**
	 * 发送https的get请求
	 * @param  string $url 
	 * @return obj
	 */
	public static function get($url, $isDecode = true){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		$output = curl_exec($curl);
		curl_close($curl);
		return $isDecode ? json_decode($output, true) : $output;
	}

	/**
	 * 发送https的POST请求
	 * @param  string  $url
	 * @param  mix     $data json | array
	 * @param  boolean $resDecode 是否对请求结果解码
	 * @param  boolean $paramEncode 是否对请求参数进行json编码
	 * @return obj
	 */
	public static function post($url, $data = null, $resDecode = true, $paramEncode = true)
	{
		if ($paramEncode && is_array($data)) {
			$data = json_encode($data, 256);
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(null != $data){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		$output = curl_exec($curl);
		curl_close($curl);
		return $resDecode ? json_decode($output, true) : $output;
	}
}