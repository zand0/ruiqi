<?php
/**
 * 规则管理 6.1
 * @date 2016/03/24
 */
class AdminRuleAppModel extends Com\Model\Base {

    public function __construct() {
        $this->tableD = LibF::M('auth_rule_app');
    }

    public function add($param, $roles = array()) {
        $insert_id = $this->_add($param);
        if (!empty($insert_id)) {
            $data['uid'] = $insert_id;
            return $this->logicReturn('200', '操作成功');
        }
        return $this->logicReturn('1', '操作失败');
    }

    /**
     * 修改用户
     */
    public function edite($type = 'edite', $id, $data = array(), $roles = array()) {
        if ($type == 'edite') {
            $where['id'] = $id;
            $this->_edite($id, $data, $where);
            return $this->logicReturn('200', '操作成功');
        } else {
            $where['id'] = $id;
            return $this->tableD->where($where)->find();
        }
    }

    public function getList() {
        return $this->tableD->order("id asc,list_sort ASC")->where('status=1')->select();
    }

    public function getRuleList($authData) {

        if (empty($authData))
            return false;

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $ks = 2;
        $kn = 1;

        $data = array();
        if (!empty($authData)) {
            foreach ($authData as $key => $value) {

                $r['id'] = $value['id'];
                $r['parentid'] = $value['pid'];
                $r['name'] = $value['title'];
                $r['title'] = $value['name'];
                $r['listorder'] = $value['list_sort'];

                $text = '正常';
                if ($value['status'] != 1) {
                    $text = '禁用';
                }
                $r['text'] = $text;
                $r['list'] = "<a href='/authrule/appedit?id=" . $r['id'] . "'>修改</a>|<a href='/authrule/appdel?id=" . $r['id'] . "' onclick='if(confirm(\"确定删除?\")==false)return false;'>删除</a>";

                $iData[] = $r;
            }

            $str = "<tr>
                <td>\$id</td>
                <td style='text-align:left;padding-left:20px'>\$spacer\$name</td>
                <td>\$title</td>
                <td>\$listorder</td>
                <td>\$text</td>
                <td>\$list</td>
            </tr>";

            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
        }

        return $data;
    }

    public function getRuleSelect($authData, $pid = 0) {

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $data = array();
        if (!empty($authData)) {
            foreach ($authData as $value) {

                $r['id'] = $value['id'];
                $r['parentid'] = $value['pid'];
                $r['name'] = $value['title'];
                $r['title'] = $value['name'];
                $r['listorder'] = $value['list_sort'];

                $r['selected'] = '';
                if ($pid == $r['id']) {
                    $r['selected'] = "selected = 'selected'";
                }

                $iData[] = $r;
            }

            $str = "<option value='\$id' \$selected>\$spacer\$name</option>";
            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
        }
        return $data;
    }

    public function getRuleRole($authData, $rules = array(), $role_id = 0) {
        if (empty($authData))
            return false;

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();

        $data = array();
        if (!empty($authData)) {
            foreach ($authData as $key => $value) {

                $r['id'] = $value['id'];
                $r['parentid'] = $value['pid'];
                $r['name'] = $value['title'];
                $r['title'] = $value['name'];
                $r['listorder'] = $value['list_sort'];

                $r['text'] = '';
                if ($rules && in_array($value['id'], $rules)) {
                    $r['text'] = "checked";
                }

                $iData[] = $r;
            }
            $str = "<li class='dd-item' rid='\$id' pid='\$parentid'><input name='rules[]' type='checkbox' value='\$id' id='x\$id' \$text /><label for='x\$id'>\$spacer\$name(\$title)</label></li>";
            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
        }
        return $data;
    }
    
    public function getRuleObject() {

        $where['status'] = 1;
        $where['pid'] = 0;
        $data = $this->tableD->order("id asc,list_sort ASC")->where($where)->select();
        $returnData = array();
        foreach ($data as $value) {
            $returnData[$value['id']] = $value['list_sort'];
        }
        return $returnData;
    }

}