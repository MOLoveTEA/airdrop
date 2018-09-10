<?php
namespace app\api\controller;
use app\common\HttpsRequest;

class Token extends Base
{
    /**
     * 获取token列表
     * @param  integer $status   token状态
     * @param  string  $search   搜索关键字
     * @param  integer $page     页码
     * @param  integer $pageSize 分页大小
     * @return json
     */
    public function listing($status = -1, $search = '', $platform = '', $diff = '', $page = 1, $pageSize = 10)
    {
        $cdt = [];

        if ($status != -1) {
            $cdt['status'] = $status;
        }
        if (!empty($search)) {
            $cdt['fullname|abbr'] = ['like', "%{$search}%"];
        }
        if ($platform != '') {
            $cdt['platform'] = $platform;
        }
        if ($diff != '') {
            $cdt['difficulty_degree'] = $diff;
        }

        $start = $pageSize * ($page - 1);

       
        $data['list'] = db('token')->where($cdt)->limit($start, $pageSize)->order(['listorder'=>'desc','ctime'=>'desc'])->select();
        $data['list'] = $this->convent($data['list']);
        $data['count'] = db('token')->where($cdt)->count();
        return $this->output($data);
    }

    /**
     * token 详情
     * @param integer $id token id
     * @return json
     */
    public function detail($id)
    {
        $data = db('token')->alias('t')
            ->join('TokenDetail td', 'td.token_id= t.id')
            ->where('t.id', $id)->find();
        $data = $this->convent($data);
        // 判断用户是否已吐槽
        $data['user_feedback'] = 0;
        return $this->output($data);

    }
    /**
     * 字典转换
     * @param array $input
     * @return void
     */
    private function convent($input)
    {
        $platform          = config('dictionary.platform');
        $difficulty_degree = config('dictionary.difficulty_degree');

        foreach ($input as $k => $v) {
            if (isset($v['platform'])) {
                if (in_array(strtoupper($v['platform']), $platform)) {
                    $input[$k]['platform'] = $platform[strtoupper($v['platform'])];
                }
            }
            if (isset($v['difficulty_degre'])) {
                if (in_array(strtoupper($v['difficulty_degree']), $difficulty_degree)) {

                    $input[$k]['difficulty_degree'] = $difficulty_degree[strtoupper($v['difficulty_degree'])];
                }
            }

        }
        return $input;
    }

    /**
     * 获取平台列表
     * @return json 平台名称与简称映射
     */
    public function platformList()
    {
        $list = [];
        foreach (config('dictionary.platform') as $key => $val) {
            $list[] = [
                'name'  => $val,
                'value' => $key,
            ];
        }
        return $this->output(['list' => $list]);
    }

    /**
     * 获取网页信息
     *
     * @param [type] $url 想要获取信息的网址
     * @return void
     */
    public function webview($url)
    {
    	return HttpsRequest::get(urldecode($url), false);
    }
}
