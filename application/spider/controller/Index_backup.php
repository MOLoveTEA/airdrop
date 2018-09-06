<?php
namespace app\spider\controller;

class Index
{
    /**
     * 1、等号两边一律加空格
     * 2、逗号后面加空格
     * 3、尽量不要新开变量，改用数组，不然后期写数据库不方便
     * 4、不要在主函数写太多var_dump
     * 5、不要在一个函数里写完逻辑，否则运维、调试很麻烦。比如处理列表，处理详情可以分开
     */

    /**】，k'i
     * 获取列表的思路有点错误，应该是先获取每一页的每一项，再获取那一项的信息
     * 比如拿到的应该是(abc)(abc)(abc)这样，而不是(aaa)(bbb)(ccc)，否则数据一多，处理错位问题就麻烦了
     */

    /* 爬虫主地址 */
    private $spiderUrl = 'https://www.airdropsmob.com/';

    public function debug()
    {
        $list = $this->getTokenList();
        var_dump($list);
    }

    public function index()
    {
        $tokens       = [];
        $tokens_url   = [];
        $tokens_abbr  = [];
        $tokens_dolar = [];

        $token_list = $this->getTokenList();

        foreach ($token_list as $key => $val) {
            $token_detail     = $this->getTokenDetail($val['url']);
            $token_list[$key] = array_merge($token_list[$key], $token_detail);
        }
        var_dump($token_list);

    }
    /**
     * 获取文章内容页面数据
     */
    private function getTokenDetail($url)
    {
        $detail                 = [];
        $detail['ticker']       = '';
        $detail['platform']     = '';
        $detail['per_airdrop']  = '';
        $detail['ico_price']    = '';
        $detail['total_supply'] = '';
        $detail['whitepapers']  = '';
        $detail['ico_date']     = '';
        $detail['howget']       = '';
        $detail['information']  = '';
        $detail['official_url'] = '';
        $detail['blog_post']    = '';
        //缺少部分数据空值处理
        $content_de = file_get_contents($url);
        //处理平台
        preg_match_all('/expire2.*">(.*)<\/div\>/iU', $content_de, $match_expire2);
        //var_dump($match_expire2); //exit;
        foreach ($match_expire2[1] as $k_expire2 => $v_expire2) {
            //处理ticker

            if (preg_match('/Ticker:(.*)<\/h3/iU', $v_expire2, $ticker)) {
                //var_dump($ticker);exit;
                $detail['ticker'] = trim(end($ticker));
            }

            if (preg_match("/(Platform:(.*)<\/h3)/iU", $v_expire2, $platform)) {
                //var_dump($platform);
                $detail['platform'] = trim(end($platform));
            }
            //处理tokens per airdrop
            if (preg_match("/(airdrop:(.*)<div)/iU", $v_expire2, $per_airdrop)) {
                $detail['per_airdrop'] = trim(end($per_airdrop));
            }
            //处理 ico token price
            if (preg_match("/(price:(.*)<\/h3)/iU", $v_expire2, $ico_price)) {
                $detail['ico_price'] = trim(end($ico_price));
            }
            //处理lco total supply
            if (preg_match("/(supply:(.*)<\/h3)/iU", $v_expire2, $total_supply)) {
                $detail['total_supply'] = trim(end($total_supply));
            }
            //whitepapers
            if (preg_match("/(href=\"(.*)\")/iU", $v_expire2, $whitepapers)) {
                $detail['whitepapers'] = trim(end($whitepapers));
            }
            //处理ico date
            if (preg_match("/(date:(.*)<\/h3)/iU", $v_expire2, $ico_date)) {
                $detail['ico_date'] = trim(end($ico_date));
            }

        }

        //处理howget entry-content clear
        preg_match_all('/entry-content\sclear.*">(.*)<\/div>/iU', $content_de, $match_howget);
        $detail['howget'] = end($match_howget[1]);

        //处理information
        preg_match_all('/class=\"description.*">(.*)<\/p>/iU', $content_de, $match_information);
        $detail['information'] = end($match_information[1]);

        //处理官网
        preg_match_all('/<a\shref=(.*)Official\sWebsite/iU', $content_de, $match_official_web);
        //再处理截取字符串

        $str_ow = implode('1', end($match_official_web));

        preg_match('/\"(.*)\"/iU', $str_ow, $official_url);

        $detail['official_url'] = trim(end($official_url));
        //处理难易度 easy blog_post
        preg_match_all('/blog_post[\s\S]*><h3>(.*)<\/h3>/iU', $content_de, $match_blog_post);
        $detail['blog_post'] = trim(end($match_blog_post[1]));

        return $detail;
    }

    /**
     * 获取Token列表所有数据
     * @param  int $page 获取多少页的数据，默认为1
     * @return array
     */
    private function getTokenList($page = 1)
    {
        $list = [];

        for ($i = 0; $i < $page; $i++) {
            // 单项目匹配，最后赋值到list里
            $item = [];

            $url = $this->spiderUrl . 'page/' . $i . '/?s';

            // 获取这个页面的源代码
            $content = $this->getPageContent($url);

            // 根据源代码，匹配出项目列表代码
            $items = $this->matchListItem($content);

            foreach ($items as $key => $val) {

                // 详情url
                $arr         = preg_split("/(href=\"|\/\")/", $val);
                $item['url'] = $arr[1];

                // 全称缩写
                // 注：匹配结果只有一个的情况下可以用preg_match
                preg_match('/post_info"><h2><a.*">(.*)<\/a><\/h2>/iU', $val, $match);
                $arr                = preg_split("/(\(|\)|\[|\])/", $match[1]);
                $item['token_name'] = trim($arr[0]);
                if (isset($arr[1])) {
                    $item['token_abbr'] = trim($arr[1]);
                } else {
                    $item['token_abbr'] = '';
                }

                // 处理价值 value_amount id="dolar"
                preg_match_all('/value_amount.*">(.*)<\/span\>/iU', $val, $match_value);
                foreach ($match_value[1] as $k_dolar => $v_dolar) {
                    $item['value_amount'] = substr($v_dolar, 5);
                }

                // 处理时间
                preg_match_all('/expire2.*">(.*)<\/div\>/iU', $val, $item['expire']);

                // 处理星星
                preg_match_all('/rating.*">(.*)<\/div\>/iU', $val, $match_rating);
                foreach ($match_rating[1] as $k_rating => $v_rating) {
                    $item['rating'] = substr_count($v_rating, '&#x2605');
                }

                // 处理图标
                preg_match_all('/logo_search.*">(.*)<\/div\>/iU', $val, $match_pic);
                foreach ($match_pic[1] as $k_pic => $v_pic) {
                    $arr_pic      = preg_split("/(\"|\")/", $v_pic);
                    $item['logo'] = $arr_pic[1];
                }

                // 往列表里增加一项
                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * 匹配出列表页中所有的项目
     * @param  string $html 整个页面源代码
     * @return array        所有项目，html代码
     */
    private function matchListItem($html)
    {
        preg_match('/<main(.*?)<\/main>/i', $html, $main);
        preg_match_all('/<article.*>(.*)<\/article>/iU', $main[1], $items);
        return $items[1];
    }

    /**
     * 获取页面源代码，顺便去除换行
     * @param  string $url 地址
     * @return string
     */
    private function getPageContent($url)
    {
        $content = file_get_contents($url);
        $content = str_replace("\r\n", '', $content);
        return $content;
    }
}
