<?php 
/**
 * Description of Base
 *
 * @author zxg
 */
namespace Com\Model;
class Base {
    protected $errorStatusPrefix = '100';
    protected $tableD;
    protected $pageSize = 20;
    
    public function init() {
        $this->pageSize = LibF::C('site.page_size');
    }
    protected function logicReturn($status, $msg=null, $ext=null){
        if($status != 200){
            $status = $this->errorStatusPrefix . $status;
        }
        return array('status'=>$status, 'msg'=>$msg, 'ext'=>$ext);
    }
    protected function succLogicReturn($msg=null, $ext=null){
        return $this->logicReturn(200, '', $ext);
    }
    
    /**
     * 公用添加方法
     * @param type $param 数组，字段和数据库对应
     * @return $id 插入ID
     */
    public function _add($param) {
        if ($this->tableD->create($param)) {
            return $this->tableD->add($param);
        }
        return false;
    }
    
    /**
     * 公用修改
     * @param type $param 数组，字段和数据库对应
     * @return type
     */
    public function _edite($id, $param, $where = array()) {
        if(!empty($id)) {
            if(!empty($param)) {
                if(!empty($where)) {
                    return $this->tableD->data($param)->where($where)->save();
                }
            } else {
                return $this->tableD->where($where)->find();
            }
        }
        return false;
    }
    
    /**
     * 公用列表类
     */
    protected function _list($where) {
        //排序字段 默认为主键名
        if (isset($_REQUEST ['_order']) && isset($_REQUEST ['_sort'])) {
            $_order = $_REQUEST ['_order'];
            $_sort = $_REQUEST['_sort'];
            $order = "{$_order} {$_sort}";
        } else {
            $_order = 'ctime';
            $_sort = 'desc';
            $order = "{$_order} {$_sort}";
        }
        $page = max(1, intval($_REQUEST['page']));
    	//取得满足条件的记录数

    	$count = $this->tableD->where($where)->count();
    	if ($count > 0) {
            //创建分页对象
            $listRows = $this->pageSize;
            $firstRow = ($page-1)*$listRows;
            //分页查询数据
            $field='*';//这里要考虑如何去处理
            $voList = $this->tableD->field($field)->where($where)->order($order)->limit($firstRow . ',' . $listRows)->select();
            return array(
                'list'  => $voList,
                '_sort' => $_sort,
                '_order'=> $_order,
                'count' => ceil($count/$listRows),
                'total' => $count,
                'page'  => $page
            );
    	}
    }
}
