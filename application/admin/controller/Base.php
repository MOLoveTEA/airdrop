<?php
namespace app\admin\controller;
use think\Controller;
use think\facade\Url;
Url::root('/admin.php?s=');

class Base extends \think\Controller{

	protected $_ctrl;

	/**
	 * 图片上传目录
	 * @var string
	 */
	protected $imgFolder;

	/**
	 * 上传图片的name属性值
	 * @var string
	 */
	protected $imageName = 'image';

	/**
	 * 数据表
	 * @var string
	 */
	protected $table;

	/**
	 * 页尺寸
	 * @var integer
	 */
	protected $pageSize = 15;

	/**
	 * 表默认主键
	 * @var string
	 */
	protected $pk = 'id';

	/**
	 * 启用/禁用状态文字
	 * @var string
	 */
	protected $status = [0 => '已禁用', 1 => '已启用'];

	/**
	 * like '%var%'
	 * 建立key-value关系，用于取别名
	 * @var string
	 */
	protected $searchLike = ['title' => 'title'];

	protected $order = 'id desc';

	public function __construct(){
		parent::__construct();
		$this->_checkLogin();
		if(in_array(request()->action(), ['index', 'add', 'edit'])){
			$request = request();
			$baseurl = $request->domain() . $request->baseFile().'?s=';
			$this->_ctrl = $request->controller() . '/index';
			$this->assign([
				'baseurl' => $baseurl,
				'_ctrl'   => $this->_ctrl,
			]);
			$this->pageInit();
			if('index' != request()->action())
				$this->editInit();
		}
		if(null == $this->table)
			$this->table = request()->controller();
		$this->assign([
			'_table'  => $this->table,
		]);
		$this->imgFolder = strtolower($this->table);
	}

	protected function pageInit() {}

	protected function editInit() {}

	protected function _checkLogin()
	{
		if (!session('admin.id')){
			$this->redirect('login/index');
		}
		return true;
	}

	/**
	 * 主页查询过滤器
	 * @return void
	 */
	protected function indexFilter(){
		$input = input();
		// 自定义排序方式
		if(isset($input['order'])) {
			$this->orderBy($input['order']);
			unset($input['order']);
		}
		$cdt = $this->condition($input);
		$page = input('page', 1);
		$model = db($this->table);
		$list = $this->listing($model, $cdt, $page);
		$count = $this->count($model, $cdt);
		$this->pageList($page, $count);
		$this->assign([
			'page'       => $page,
			'list'       => $list,
		]);
	}

	protected function listing($model, $cdt, $page) {
		return $model->where($cdt)->page($page)->limit($this->pageSize)->order($this->order)->field("*")->select();
	}

	protected function count($model, $cdt) {
		return  $model->where($cdt)->count();
	}

	/**
	 * 渲染主页
	 * @return response
	 */
	public function index(){
		$this->indexFilter();
		return view('index');
	}

	/**
	 * 生成查询条件。like表达式为 ['column', 'like', '%']
	 * @param  array $request
	 * @return array
	 */
	protected function condition($request){
		if(isset($request['page']))
			unset($request['page']);
		$cdt = [];
		foreach ($request as $key => $value) {
			if(isset($this->searchLike[$key])){
				if($value)
					$cdt[] = [$key, 'like', "%{$value}%"];
				continue;
			}
			elseif('is_active' == $key) {
				if(0 == $value || 1 == $value)
					$cdt[$key] = $value;
				continue;
			}
			elseif('orderBy' == $key) {
				$this->orderBy($value);
				continue;
			}
			elseif($value)
				$cdt[$key] = $value;
		}
		return $cdt;
	}

	protected function orderBy($order) {
		if(!$order)
			return;
		$this->order = $order;
	}

	/**
	 * 生成分布按钮列
	 * @param  int  $page    当前页码
	 * @param  int  $count   总数据量
	 * @param  integer $section 页码延伸量
	 * @return void
	 */
	protected function pageList($page, $count, $section = 4) {
		$pageStart = $page - $section;
		$pageStart < 1 && $pageStart = 1;
		$pageEnd = $page + 4;
		$max = ceil($count/$this->pageSize);
		0 == $max && $max = 1;
		$pageEnd > $max && $pageEnd = $max;
		$pageList = '';
		for($i = $pageStart; $i <= $pageEnd; ++$i){
			$pageList .= '<li><a href="javascript:;"';
			if($page != $i) {
				$pageList .= "onclick=\"builder('{$this->_ctrl}',$i)\"";
			}else{
				$pageList .= 'style="background-color:#ddd"';
			}
			$pageList .= ">$i</a></li>";
		}
		$this->assign([
			'pageList' => $pageList,
		]);
	}

	/**
	 * 切换active状态
	 * @param  string $table 表格
	 * @param  int $id
	 * @return mix
	 */
	public function activeSwitch($table, $id){
		$table = 'sd_' . strtolower($table);
		$sql = 'UPDATE '.$table.' set is_active = is_active#1 where id = ?';
		$res = db()->execute($sql, [$id]);
		echo $res;
	}

	/**
	 * 编辑页数据
	 * @param  int $id
	 * @return void
	 */
	protected function editFilter($id) {
		$data = db($this->table)->where('id', $id)->find();
		$this->assign([
			'data'   => $data,
			'_title' => '编辑数据',
		]);
	}

	/**
	 * 编辑页
	 * @param  int $id
	 * @return response
	 */
	public function edit($id){
		$this->editFilter($id);
		$this->assign([
			'_actionUrl' => url('update', ['id' => $id]),
		]);
		return view('edit');
	}

	protected function dataAdapter(&$input) {
		// 上传图片
		if(input('file.'.$this->imageName)) {
			$Upload = new \app\common\Upload;
			$output = $Upload->exec($this->imageName, $this->imgFolder);
			if(0 == $output['code'])
				$input[$this->imageName] = $output['filename'];
			else return $this->error(412, $output['message']);
		}
		if(isset($input[$this->imageName]) && empty($input[$this->imageName]))
			unset($input[$this->imageName]);
	}

	public function update($id){
		$input = input('post.');
		if ($id == 0) {
			return $this->insert($input);
		}
		$callback = $this->dataAdapter($input);
		if(is_array($callback)) {
			return $this->showError($callback[0], $callback[1]);
		}
		$input['mtime']=time();
		$res = db($this->table)->where($this->pk, $id)->update($input);
		if(false === $res)
			return $this->showError(500, '操作失败');
		return $this->output('操作成功');
	}

	public function add(){
		$this->assign([
			'_title'     => '新增数据',
			'_actionUrl' => url('insert'),
		]);
		return view('add');
	}

	public function insert($input){
		$input = input('post.');
		$callback = $this->dataAdapter($input);
		if(is_array($callback)) {
			return $this->showError($callback[0], $callback[1]);
		}
		$res = db($this->table)->insert($input);
		if($res)
			return $this->output('操作成功');
		return $this->showError(500, '操作失败');
	}

	/**
	 * 生成主键
	 * @param  int $base 基数
	 * @return long
	 */
	protected function uuid($base = 1){
		return date('ymd') * $base . substr(str_replace('.', '', microtime(true)), 5, 12) . mt_rand(0, 9);
	}

	public function delete($id){
		$res = db($this->table)->where($this->pk, $id)->delete();
		if($res){
			db('LogDelete')->insert(['admin_id' => session('admin.id'), 'tablename' => $this->table, 'data_id' => $id]);
		}
		return $this->output();
	}

	/**
	 * 数据输出
	 * @param  array  $data     结果集
	 * @param  integer $code    错误码
	 * @param  string  $message 提示信息
	 * @return json
	 */
	protected function output($data = null, $code = 0, $message = 'success') {
		if(empty($data))
			return json(['code' => $code, 'message' => $message, 'data' => []]);
		return json(['code' => $code, 'message' => $message, 'data' => $data]);
	}

	/**
	 * 输出错误信息
	 * @param  int    $code    错误码
	 * @param  string $message 提示信息
	 * @return json
	 */
	protected function showError($code, $message) {
		return json(['code' => $code, 'message' => $message]);
	}

}
