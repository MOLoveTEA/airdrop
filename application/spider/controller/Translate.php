<?php
namespace app\spider\controller;

use think\Controller;

class Translate extends \think\Controller
{

    protected $url_backup = 'https://translate.google.cn/translate_a/single?client=t&sl=en&tl=zh-CN&hl=zh-CN&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&ie=UTF-8&oe=UTF-8&source=bh&ssel=0&tsel=0&kc=1&';

    /* 谷歌翻译地址 GET*/
    protected $url = 'http://translate.google.cn/translate_a/single?client=gtx&dt=t&ie=UTF-8&oe=UTF-8&sl=auto&tl=zh-CN';

    /* 谷歌token */
    protected $tk = 0;

    protected function initialize() {
        mb_internal_encoding("UTF-8");
        $this->tk = $this->getGoogleToken();
    }

    /**
     * 英文翻译成中文
     * @param  string $q 将要翻译的内容
     * @return string
     */
    public function translate($q)
    {
        if ($q == '') 
            return '';
        
        $match = '';
        $url = $this->url. '&q='. urlencode($q). '&tk='. $this->tk;
        $conts = $this->curl_https($url);
        $conts  = json_decode($conts);
        $string = '';
        if (is_array($conts)) {
            foreach ($conts[0] as $key => $value) {
                $value = str_replace('\n', "\r\n", $value[0]);
                $string = $string. $value;
            }
            $match = $string;
        }
        return $match;
    }

    public function curl_https($url)
    {
        $timeout = 0;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        $conts = curl_exec($ch);
        curl_close($ch);
        return $conts;
    }

    public function getGoogleToken()
    {
        $timeout = 10;
        $url     = "https://translate.google.cn";
        $conts = $this->curl_https($url);
        if (preg_match("#TKK\=eval\('\(\(function\(\)\{var\s+a\\\\x3d(-?\d+);var\s+b\\\\x3d(-?\d+);return\s+(\d+)\+#isU", $conts, $arr)) {
            $token = $arr[3] . '.' . ($arr[1] + $arr[2]);
            return $token;
        } else {
            exit('GoogleTokenFailed');
        }
    }

}
