<?php

/**
 * @ 获取相关树形结构
 */
class OrgModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    public function shoplist($param = '',$levelData = array(), $areaData = array()) {
        $shopData = LibF::M('shop')->where($param)->order('parent_shop_id asc,shop_type ASC,shop_id ASC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = $data = array();
        
        $zyNum = $jmNum = 0;
        if (!empty($shopData)) {
            foreach ($shopData as $value) {

                $r['id'] = $value['shop_id'];
                $r['shop_code'] = $value['shop_code'];
                $r['parentid'] = $value['parent_shop_id'];
                $r['name'] = $value['shop_name'];
                $r['admin_name'] = $value['admin_name'];
                $r['mobile_phone'] = $value['mobile_phone'];
                $r['area_title'] = isset($areaData[$value['area_id']]) ? $areaData[$value['area_id']]['title'] : '';
                $r['level_title'] = isset($levelData[$value['level']]) ? $levelData[$value['level']] : '';
                $r['money'] = $value['money'];
                $r['shop_type'] = ($value['shop_type'] == 1) ? '自营' : '加盟';
                $r['url'] = '<a href="/shop/payment?' . $r['id'] . '" target="_blank">' . $r['money'] . '</a>';

                $addUrl = '/shop/add?shop_id=' . $r['id'];
                $editUrl = '/shipper/payment?shop_id=' . $r['id'];
                $r['list'] = '<a href="' . $addUrl . '" class="edit">修改</a>|';
                $r['list'] .= '<a href="javascript:;" shop_id="' . $r['id'] . '" shop_payment="' . $r['money'] . '" shop_name="' . $r['name'] . '" class="chongzhi">充值</a>|';
                $r['list'] .= '<a href="javascript:;" shop_id="' . $r['id'] . '" shop_payment="' . $r['money'] . '" shop_name="' . $r['name'] . '" class="shangjiao">上缴</a>|';
                $r['list'] .= '<a href="' . $editUrl . '" target="_blank">送气工上缴</a>';

                $iData[] = $r;
                
                if ($value['shop_type'] == 1) {
                    $zyNum += 1;
                } else if ($value['shop_type'] == 2) {
                    $jmNum += 1;
                }
            }
            $str = "<tr>
                        <td style='text-align:left;padding-left:20px'>\$spacer\$name</td>
                        <td>\$shop_type</td>
                        <td>\$admin_name</td>
                        <td>\$mobile_phone</td>
                        <td>\$area_title</td>
                        <td>\$url</td>
                        <td>\$level_title</td>
                        <td class='tableBtn'>\$list</td>
                    </tr>";
            
            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
            if (!empty($data)) {
                $data .= '<tr><td colspan="8">共' . count($shopData) . '个门店,其中自营门店' . $zyNum . '个，加盟门店' . $jmNum . '个</td></tr>';
            }
        }
        return $data;
    }

    public function orglist($params = '') {
        $orgData = LibF::M('organization')->order('listorder ASC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $data = array();
        if (!empty($orgData)) {
            foreach ($orgData as $value) {

                $r['id'] = $value['org_id'];
                $r['parentid'] = $value['org_parent_id'];
                $r['name'] = $value['org_name'];
                $r['listorder'] = $value['listorder'];

                $addUrl = '/org/add?org_id=' . $r['id'];
                $editUrl = '/org/edit?org_id=' . $r['id'];
                $delUrl = '/org/del?org_id=' . $r['id'];
                $r['list'] = '<a href="' . $addUrl . '">添加子部门</a>|<a href="' . $editUrl . '">修改</a>|<a href="' . $delUrl . '" onclick="if(confirm(\'确定删除?\')==false)return false;">删除</a>';

                $iData[] = $r;
            }

            $str = "<tr><td style='text-align:left;padding-left:20px'>\$spacer\$name</td>
                    <td>\$list</td></tr>";
            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
        }
        return $data;
    }

    public function orgSelect($params = '', $org_parent_id = 0) {
        $orgData = LibF::M('organization')->order('listorder ASC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $data = array();
        if (!empty($orgData)) {
            foreach ($orgData as $value) {

                $r['id'] = $value['org_id'];
                $r['parentid'] = $value['org_parent_id'];
                $r['name'] = $value['org_name'];
                $r['listorder'] = $value['listorder'];

                $r['selected'] = '';
                if ($org_parent_id == $r['id']) {
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

    /**
     * 岗位
     */
    public function quartersSelect($params = '', $quarters_parent_id = 0) {
        $quartersData = LibF::M('quarters')->order('listorder ASC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $data = array();
        if (!empty($quartersData)) {
            foreach ($quartersData as $value) {

                $r['id'] = $value['id'];
                $r['parentid'] = $value['quarters_parent_id'];
                $r['name'] = $value['title'];
                $r['listorder'] = $value['listorder'];

                $r['selected'] = '';
                if ($quarters_parent_id == $r['id']) {
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

    public function quartersList($params = ''){
        $quartersData = LibF::M('quarters')->where(array('status' => 1))->order('listorder ASC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $data = array();
        if (!empty($quartersData)) {
            foreach ($quartersData as $value) {

                $r['id'] = $value['id'];
                $r['parentid'] = $value['quarters_parent_id'];
                $r['name'] = $value['title'];
                $r['listorder'] = $value['listorder'];
                $r['list_edit'] = '';
                if ($value['is_show'] == 0) {
                    $r['list_edit'] = "<a href = '/quarters/edit?id=" . $value['id'] . "&pid=" . $value['quarters_parent_id'] . "'>修改</a>|<a href = '/quarters/del?id=" . $value['id'] . "' onclick = 'if(confirm('确定删除?')==false)return false;'>删除</a>";
                }
                $r['list_show'] = '';
                if ($value['status'] == 1) {
                    $r['list_show'] = '正常';
                } else {
                    $r['list_show'] = '禁用';
                }
                $iData[] = $r;
            }

            $str = "<tr>
                    <td style='text-align:left;padding-left:20px'>\$spacer\$name</td>
                    <td>\$list_show</td>
                    <td>\$list_edit</td>
                </tr>";
            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
        }
        return $data;
    }
    
    /**
     * 部门创建
     * 
     * @param $params
     */
    public function add($params, $id) {
        if (empty($params)) {
            return $this->logicReturn('0203', '请提交数据！');
        }

        $data['org_name'] = $params['org_name'];
        $data['org_parent_id'] = $params['org_parent_id'];
        $data['org_content'] = $params['org_content'];
        $data['listorder'] = $params['listorder'];

        if ($id) {
            $status = LibF::M('organization')->where('org_id=' . $id)->save($data);
        } else {
            $status = LibF::M('organization')->add($data);
        }
        if (!$status) {
            return $this->logicReturn('0206', '添加失败!');
        }
        return $this->logicReturn(200, 'ok', $status);
    }

    /**
     * 获取部门
     */
    public function getOrgList($org_id = 0, $org_parent_id = 0) {
        $where = array();
        if ($org_id)
            $where['org_id'] = $org_id;

        $where['org_parent_id'] = $org_parent_id;

        $data = LibF::M('organization')->where($where)->select();
        return $data;
    }

    /**
     * 获取职务
     */
    public function getRoleList($role_id = 0) {
        $where = array();
        if ($role_id)
            $where['id'] = $role_id;

        $data = LibF::M('auth_role')->where($where)->select();
        return $data;
    }

    /**
     * 获取部门对应职务
     */
    public function getOrgRole($org_id = 0, $role_id = 0) {
        $where = array();
        if ($org_id)
            $where['org_parent_id'] = $org_id;
        if ($role_id)
            $where['id'] = $role_id;

        $data = LibF::M('auth_role')->where($where)->order('listorder asc')->select();
        return $data;
    }

    /**
     * 获取部门对应岗位
     */
    public function getOrgQuarters($org_id = 0, $quarters_id = 0) {
        $where = array();
        if ($org_id)
            $where['org_parent_id'] = $org_id;
        if ($quarters_id)
            $where['id'] = $quarters_id;

        $data = LibF::M('quarters')->where($where)->order('listorder asc')->select();
        return $data;
    }

    /**
     * 获取职务对应用户
     */
    public function getRoleUser($role_id = 0) {
        $where = array('rq_auth_role_user.role_id' => $role_id);

        $roleUser = new Model('auth_role_user');
        $data = $roleUser->join('LEFT JOIN rq_admin_user ON rq_auth_role_user.uid = rq_admin_user.user_id')->field("rq_admin_user.username,rq_admin_user.photo")->where($where)->select();
        return $data;
    }

    /**
     * 获取岗位对应用户
     */
    public function getQuartersUser($quarters_id = 0) {
        $where = array('rq_quarters_user.quarters_id' => $quarters_id);
        $where['rq_admin_user.mobile_phone'] = array('neq','');

        $quartersUser = new Model('quarters_user');
        $data = $quartersUser->join('LEFT JOIN rq_admin_user ON rq_quarters_user.uid = rq_admin_user.user_id')->field("rq_admin_user.username,rq_admin_user.photo,rq_admin_user.real_name,rq_admin_user.mobile_phone")->where($where)->select();
        return $data;
    }

    /**
     * 获取组织架构
     */
    public function getOrgation($params = '') {
        $orgData = LibF::M('organization')->order('listorder ASC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $ks = 1;
        $kn = 0;

        $data = array();
        if (!empty($orgData)) {
            foreach ($orgData as $key => $value) {

                $r['keys'] = '';
                $r['show_kuang'] = '';
                if ($value['org_parent_id'] == 0) {
                    $r['keys'] = "class='zzjgDl" . $ks . "'";
                    $ks++;

                    $kn++;
                    $r['show_kuang'] = "<span class='fl'>" . $kn . "</span>";
                }

                $r['id'] = $value['org_id'];
                $r['parentid'] = $value['org_parent_id'];
                $r['name'] = $value['org_name'];
                $r['listorder'] = $value['listorder'];

                $roleData = $this->getOrgQuarters($r['id']);
                $r['list'] = '';
                if (!empty($roleData)) {
                    foreach ($roleData as $rVal) {
                        $roleUser = $this->getQuartersUser($rVal['id']);
                        $ulist = '';
                        if (!empty($roleUser)) {
                            foreach ($roleUser as $uVal) {
                                if (!empty($uVal['real_name'])) {
                                    $ulist .= "<li>
                                        <a href='javascript:;'>
                                            <div class='myheadface'>
                                                <img src='/statics/upload/photo/" . $uVal['photo'] . "'>
                                            </div>
                                            <p>" . $uVal['real_name'] . "</p>
                                        </a>
                                    </li>";
                                }
                            }
                            $r['list'] .= "<dd class='clearfix'>
                                <strong class='fl'><span>" . $rVal['title'] . "</span></strong>
                                <div class='fl memberList'>
                                    <ul class='clearfix'>
                                        " . $ulist . "
                                    </ul>
                                </div>
                            </dd>";
                        }
                    }
                }

                $iData[] = $r;
            }

            $str = "<dl \$keys><dt class='clearfix'>\$show_kuang
            <div class='fl'>
                <strong>\$spacer\$name</strong>
                <p></p>
            </div>\$list</dt></dl>";
            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
        }

        return $data;
    }

    /**
     * 获取组织架构
     */
    public function getOrgationPeople($params = '') {
        $orgData = LibF::M('organization')->order('listorder ASC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $iData = array();
        $ks = 2;
        $kn = 1;

        $data = array();
        if (!empty($orgData)) {
            foreach ($orgData as $key => $value) {

                $r['keys'] = '';
                if ($value['org_parent_id'] == 0) {
                    $r['keys'] = "class='zzjgDl" . $ks . "'";
                    $ks++;
                }
                $kn++;
                $r['kn'] = $kn;

                $r['id'] = $value['org_id'];
                $r['parentid'] = $value['org_parent_id'];
                $r['name'] = $value['org_name'];
                $r['listorder'] = $value['listorder'];

                $roleData = $this->getOrgQuarters($r['id']);
                $r['list'] = '';
                if (!empty($roleData)) {
                    foreach ($roleData as $rVal) {
                        $roleUser = $this->getQuartersUser($rVal['id']);
                        $ulist = '';
                        if (!empty($roleUser)) {
                            foreach ($roleUser as $uVal) {
                                $ulist .= "<li>
                                        <a href='javascript:;'>
                                            <div class='myheadface'>
                                                <img src='/statics/upload/photo/" . $uVal['photo'] . "'>
                                            </div>
                                            <p>" . $uVal['username'] . "</p>
                                        </a>
                                    </li>";
                            }
                        }

                        $r['list'] .= "<dd class='clearfix'>
                            <strong class='fl'><span>" . $rVal['title'] . "</span></strong>
                            <div class='fl memberList'>
                                <ul class='clearfix'>
                                    " . $ulist . "
                                </ul>
                            </div>
                        </dd>";
                    }
                }

                $iData[] = $r;
            }

            $str = "<dl \$keys><dt class='clearfix'><span class='fl'>\$kn</span>
            <div class='fl'>
                <strong>\$spacer\$name</strong>
                <p></p>
            </div>\$list</dt></dl>";

            $tree->init($iData);
            $data = $tree->get_tree(0, $str);
        }

        return $data;
    }

    /**
     * 获取岗位对应的人员
     */
    public function getQuarter($params = '') {
        $quartersData = LibF::M('quarters')->order('listorder DESC')->select();

        $tree = new TreeModel();
        $tree->icon = array('&nbsp;&nbsp;│ ', '&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;';

        $returnData = $data = array();
        if (!empty($quartersData)) {
            foreach ($quartersData as $value) {
                $r['id'] = $value['id'];
                $r['parentid'] = $value['quarters_parent_id'];
                $r['name'] = $value['title'];
                $r['listorder'] = $value['listorder'];

                $r['list'] = '';
                //获取当前岗位对一个的人员
                $adminUserData = $this->getQuartersUser($value['id']);
                if (!empty($adminUserData)) {
                    foreach ($adminUserData as $val) {
                        $r['list'] .= "<li>
                                            <div class='imgbox fl'>
                                                <img src='/statics/znewhome/images/head_1.png'>
                                            </div>
                                            <div class='fl name'>
                                                <h5>" . $val['real_name'] . "</h5>
                                                <p>" . $val['mobile_phone'] . "</p>
                                            </div>
                                        </li>";
                    }
                }
                $r['num'] = count($adminUserData);
                $data[] = $r;
            }
            $strlist = "<div class='td'>
                            <div class='rows clearfix'>
                                <span></span>
                                <i></i>
                                <div class='fl department'>
                                    <h4>\$spacer\$name<strong>(\$num 人)</strong></h4>
                                </div>
                                <ul class='peopleList fl'>\$list</ul>
                            </div>
                        </div>";
            $tree->init($data);
            $returnData = $tree->get_tree(0, $strlist);
        }
        return $returnData;
    }
    
}