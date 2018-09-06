<?php
namespace app\spider\controller;

use think\Controller;

class Index extends \think\Controller
{
    /**
     * 1、等号两边一律加空格
     * 2、逗号后面加空格
     * 3、尽量不要新开变量，改用数组，不然后期写数据库不方便
     * 4、不要在主函数写太多var_dump
     * 5、不要在一个函数里写完逻辑，否则运维、调试很麻烦。比如处理列表，处理详情可以分开
     */

    /**
     * 6、方法内变量名尽量简短。因为大部分方法都封装起来了，变量不会在外部使用，只需要保证该方法内不会混淆就行了
     *    比如matchTokenDetail函数内，可直接命名为$content，不需要$content_detail。
     * 7、if的替代函数：A && B 仅当A条件true时，执行B语句
     * 8、只会匹配到一个值的情况下用 preg_match。preg_match在匹配到第一个值后会停止查询，效率比preg_match_all高
     */

    /**
     * 9、尽可能减少使用sql查询。对于大部分项目来说，服务器的性能瓶颈都是出现在数据库上，而不是php
     * 所以宁愿使用1条查询查1000条数据，也不要1000次查一条数据。在判断token是否存在时，最好一次性将所有token的id和fullname取出来，再用foreach判断
     * 10、多表同时插的时候可以用事务操作。保证数据一致性
     */

    /* 爬虫主地址 */
    private $spiderUrl = 'https://www.airdropsmob.com/';

    /* 当前数据库已有的token */
    private $currentList;

    protected function initialize()
    {
        $this->currentList = model('token')->getCurrentToken();
        // 取消php运行时间限制，以防跑到一半报错
        set_time_limit(0);
        //$this->sumPages = $this->getPages() ? $this->getPages() : 1;
    }

    public function debug()
    {
        $url = 'https://www.airdropsmob.com/?s=';
        foreach ($variable as $key => $value) {
            # code...
        }
        $val = $this->getPageContent(['https://www.airdropsmob.com/page/1/?s', 'https://www.airdropsmob.com/page/2/?s']);
        var_dump($val);
        return;
        preg_match_all('/class=\"page-numbers.*.s\'>(.*)<\/a>/iU', $val, $match_value);

        // $data = $this->getTokenList();
        // $data = $this->matchTokenDetail('https://www.airdropsmob.com/laneaxis-axis/');
    }

    /**
     * 爬虫入口文件
     * @param  integer $page     当前抓取的页码
     * @param  integer $limit    最大抓取页数
     * @return [type]            [description]
     */
    public function index($page = 1, $maxpage = 0)
    {
        if ($maxpage == 0) {
            $maxpage = $this->getPages() ? $this->getPages() : 1;
        }
        $token_list = $this->getTokenList($page);
        $urls       = [];
        // remark:获取到tokenlist的时候，直接判断哪些在数据库已经存在了，存在的也不用getDetail了。
        foreach ($token_list as $i => $val) {
            $string = strtolower($val['token_name']);
            if (in_array($string, $this->currentList)) {
                array_splice($token_list, $i, 1);
                continue;
            }
            $urls[] = $val['url'];
        }

        if (count($urls)) {
            $details = $this->getPageContent($urls);
            foreach ($details as $i => $val) {
                $detail = $this->matchTokenDetail($val);
                // var_dump($detail);
                $data  = array_merge($detail, $token_list[$i]);
                $model = model('Token');
                $token = $model->mapping($data);
                $res   = $model->allowField(true)->isUpdate(false)->data($token, true)->save();

                if (!$res) {
                    continue;
                } else {
                    $this->currentList[] = $token['fullname'];
                }
                $token['token_id'] = $model->id;
                $res               = model('TokenDetail')->allowfield(true)->isUpdate(false)->data($token, true)->save();
            }
        }

        if ($page < $maxpage) {
            $page++;
            $redirectUrl = 'http://' . $_SERVER['SERVER_NAME'] . '/index.php?s=index/index/page/' . $page . '/maxpage/' . $maxpage;
            $this->redirect($redirectUrl, 302);
        }
    }

    /**
     * @param text $content 页面html内容
     * 获取文章内容页面数据
     */
    private function matchTokenDetail($content)
    {
        $event = controller('spider/Translate');

        // 数组批量赋值方法
        $detail = array(
            'ticker'       => '',
            'platform'     => '',
            'per_airdrop'  => '',
            'ico_price'    => '',
            'total_supply' => '',
            'whitepapers'  => '',
            'ico_date'     => '',
            'howget'       => '',
            'information'  => '',
            'official_url' => '',
            'blog_post'    => '',
        );

        //处理平台
        preg_match_all('/expire2.*">(.*)<\/div\>/iU', $content, $matches);
        foreach ($matches[1] as $key => $val) {

            // 正则会把括号内的东西返回，所以匹配几个值就写几个括号。还有不要乱用end
            // Ticker
            preg_match('/Ticker:(.*)<\/h3/iU', $val, $match) && $detail['ticker'] = trim($match[1]);

            // Platform
            preg_match("/Platform:(.*)<\/h3/iU", $val, $match) && $detail['platform'] = trim($match[1]);

            // Per airdrop
            preg_match("/airdrop:(.*)<div/iU", $val, $match) && $detail['per_airdrop'] = trim($match[1]);

            //处理 ico token price
            preg_match("/price:(.*)<\/h3/iU", $val, $match) && $detail['ico_price'] = trim($match[1]);

            //处理lco total supply
            preg_match("/supply:(.*)<\/h3/iU", $val, $match) && $detail['total_supply'] = trim($match[1]);

            // whitepapers
            preg_match("/href=\"(.*)\"/iU", $val, $match) && $detail['whitepapers'] = trim($match[1]);

            //处理ico date
            preg_match("/date:(.*)<\/h3/iU", $val, $match) && $detail['ico_date'] = $this->processIcoDate(trim($match[1]));

        }

        //处理howget entry-content clear
        preg_match('/entry-content clear.*">(.*)<\/div>/iU', $content, $match) && $detail['howget'] = $match[1];
        $str                                                                                        = $this->generateBreak($detail['howget']);
        $detail['howget_cn']                                                                        = $event->translate($str);

        //处理information
        preg_match('/class="description.*">(.*)<\/p>/iU', $content, $match) && $detail['information'] = $match[1];
        $str                                                                                          = $this->generateBreak($detail['information']);
        $detail['information_cn']                                                                     = $event->translate($str);

        //处理官网
        preg_match('/<a href="(http.*)".*Official Website/iU', $content, $match) && $detail['official_url'] = trim($match[1]);

        //处理难易度 easy blog_post
        preg_match('/blog_post[\s\S]*><h3>(.*)<\/h3>/iU', $content, $match) && $detail['blog_post'] = trim($match[1]);

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

        // 单项目匹配，最后赋值到list里
        $item = [];

        $url = $this->spiderUrl . 'page/' . $page . '/?s';

        // 获取这个页面的源代码
        $content = $this->getPageContent($url);

        // 根据源代码，匹配出项目列表代码
        $items = $this->matchListItem($content);

        foreach ($items as $key => $val) {

            // 全称&缩写
            // 注：匹配结果只有一个的情况下可以用preg_match
            preg_match('/post_info"><h2><a.*">(.*)<\/a><\/h2>/iU', $val, $match);
            $arr = preg_split("/(\(|\)|\[|\])/", $match[1]);

            // 该token是否已存在，存在就不往下匹配了
            if (in_array(trim($arr[0]), $this->currentList)) {
                continue;
            }

            $item['token_name'] = trim($arr[0]);
            if (isset($arr[1])) {
                $item['token_abbr'] = trim($arr[1]);
            } else {
                $item['token_abbr'] = '';
            }
            // 详情url
            $arr                 = preg_split("/(href=\"|\/\")/", $val);
            $item['url']         = $arr[1];
            $item['detail_path'] = preg_replace('/^.*\//i', '', $item['url']);

            // 处理价值 value_amount id="dolar"
            preg_match_all('/value_amount.*">(.*)<\/span\>/iU', $val, $match_value);
            foreach ($match_value[1] as $k_dolar => $v_dolar) {
                $item['value_amount'] = substr($v_dolar, 5);
            }

            // 处理时间
            preg_match_all('/expire2.*">(.*)<\/div\>/iU', $val, $match_expire);
            $item['expire'] = $match_expire[1][0];

            //进一步处理时间
            preg_match('/(today)|(CLOSED)|(in\s(.*)\sd)/iU', $match_expire[1][0], $match);
            if (empty($match)) {
                $item['expire'] = '';
                $item['status'] = 3;
            } else {
                $end = end($match);
                switch ($end) {
                    case 'today':
                        $item['expire'] = strtotime(date('Y-m-d' . '00:00:00', time() + 3600 * 24));
                        $item['status'] = 1;
                        break;
                    case 'colse':
                        $item['expire'] = '';
                        $item['status'] = 2;
                        break;
                    default:
                        $item['expire'] = strtotime(date('Y-m-d' . '00:00:00', time() + 3600 * 24 * intval($end)));
                        $item['status'] = 0;
                        break;
                }
            }

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
     * @param  array $url url数组，多条时返回数组
     * @return array
     */
    private function getPageContent($url)
    {
        !is_array($url) && $url = [$url];
        if (!count($url)) {
            return [];
        }

        mb_internal_encoding("UTF-8");
        $timeout = 0;

        $mh = curl_multi_init();
        foreach ($url as $i => $val) {
            $ch[$i] = curl_init();
            curl_setopt($ch[$i], CURLOPT_URL, $val);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch[$i], CURLOPT_HEADER, 0);
            curl_setopt($ch[$i], CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch[$i], CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1); // 处理301情况（应该是那个网站为了防爬虫做的）
            // curl_setopt($ch[$i], CURLINFO_HEADER_OUT, true);
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, $timeout);
            curl_multi_add_handle($mh, $ch[$i]);
        }

        $active = null;
        // While we're still active, execute curl
        do {
            $mrc = curl_multi_exec($mh, $active);
            usleep(2000);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            // Wait for activity on any curl-connection
            if (curl_multi_select($mh) == -1) {
                usleep(1);
            }
            // Continue to exec until curl is ready to give us more data
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }

        foreach ($url as $i => $val) {
            $content = curl_multi_getcontent($ch[$i]);
            // var_dump($header = curl_getinfo($ch[$i]));
            $content = preg_replace("\r\n|\r|\n", '', $content);
            $content = preg_replace('/<link.*?\/>|<script.*?<\/script>|<meta.*?>/i', '', $content);
            $res[$i] = $content;
            curl_multi_remove_handle($mh, $ch[$i]);
        }
        curl_multi_close($mh);
        count($res) == 1 && $res = $res[0];

        return $res;
    }

    /**
     * 处理ico_date 日期
     *
     * @param [type] $val
     * @return void
     */
    private function processIcoDate($val)
    {
        return $val ? strtotime(str_replace('/', '-', $val)) : '';
    }

    private function getPages()
    {
        $url = 'https://www.airdropsmob.com/?s=';
        $val = $this->getPageContent($url);
        preg_match_all('/class=\"page-numbers.*.s\'>(.*)<\/a>/iU', $val, $match_value);
        return $match_value[1][0];
    }

    public function checkLimitTime()
    {
        $model = model('Token');
        $model->update(['status' => 2], 'expired < UNIX_TIMESTAMP()');
        $model->update(['status' => 0], 'expired > UNIX_TIMESTAMP()');
    }

    /**
     * 根据html内容生成换行符，并去除标签
     * @param  text $html html文本
     * @return text
     */
    protected function generateBreak($html)
    {
        $str = preg_replace('/<\/div>|<\/p>|<\/li>|<\/ul>|<\/h\d>/iU', "\r\n", $html);
        $str = preg_replace('/<.*?>/i', '', $str);
        return $str;
    }
}
