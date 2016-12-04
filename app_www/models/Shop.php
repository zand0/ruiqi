<?php

/**
 * Description of Shop
 *
 * @author zxg
 */
class ShopModel extends \Com\Model\Base {

    public function __construct() {
        $this->errorStatusPrefix = '801';
    }

    
    public function getlist($params) {
        $page = isset($params['page']) ? $params['page'] : '';
        $pageSize = isset($params['page_size']) ? $params['page_size'] : '';

        $shopModel = LibF::M('shop');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }

        $where['status'] = 1;
        if(isset($params['shop_id']) && !empty($params['shop_id']))
            $where['shop_id'] = $params['shop_id'];

        $offset = ($page - 1) * $pageSize;
        $count = $shopModel->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $shopModel->where($where)->limit($offset, $pageSize)->order('shop_id DESC')->select();
        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }
    
    
    
    public function filterList($params) {
        $page = $params['page'];
        $pageSize = $params['page_size']; //site.page_size
        
        $region_1 = $params['region_1'];
        $region_2 = $params['region_2'];
        $region_3 = $params['region_3'];
        $region_4 = $params['region_4'];
        
        $shopId = $params['shop_id'];

        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGtZeroInt($pageSize)) {
            $pageSize = LibF::C('site.page_size');
        }
        $shopM = LibF::M('shop');
        //$shopManageRegionM = LibF::M('shop_manage_region');
        
        $regionConf = LibF::LC('region');
        $regionFieldMap = $regionConf['manage_region_field_map'];
        $condition = !empty($shopId) ? array('shop_id' => array('neq', $shopId)) : '';
        if(!empty($params['level']))
            $condition['level'] = $params['level'];
        if(!empty($params['admin_name']))
            $condition['admin_name'] = array('like',$params['admin_name'].'%');

        // 筛选区域
        if ($region_4) {
            $condition[$regionFieldMap[4]] = $region_4;
        } elseif ($region_3) {
            $condition[$regionFieldMap[3]] = $region_3;
        } elseif ($region_2) {
            $condition[$regionFieldMap[2]] = $region_2;
        } elseif ($region_1) {
            $condition[$regionFieldMap[1]] = $region_1;
        }

       /* // 可管理店铺等级
        $manageShopLevelMap = $this->getShopManageLevelMap($adminShopLevel);
        if ($manageShopLevelMap) {
            $condition['level'] = array('in', $manageShopLevelMap);
        } else {
            return $this->succLogicReturn('ok', array('list' => array(), 'count' => 0, 'page' => null));
        }

        // 管理区域
        if ($adminShopLevel != 1) {
            $rows = $shopManageRegionM->field('region_id,region_level')->where(array('shop_id' => $shopId))->select();
            $manageRegions = null;
            if ($rows) {
                foreach ($rows AS $row) {
                    $manageRegions[$row['region_level']][] = $row['region_id'];
                }
            }
            if (!$manageRegions) {
                return $this->succLogicReturn('ok', array('list' => array(), 'count' => 0, 'page' => null));
            }
            $regionCondition['_logic'] = 'or';
            foreach ($manageRegions AS $key => $row) {
                $levelKey = $key + 1;
                if (!$regionFieldMap[$levelKey]) {
                    continue;
                }
                $fieldName = $regionFieldMap[$levelKey];
                $regionCondition[$fieldName] = array('in', $row);
            }
            if (!$regionCondition) {
                return $this->succLogicReturn('ok', array('list' => array(), 'count' => 0, 'page' => null));
            }
            $condition['_complex'] = $regionCondition;
        }*/
        $count = $shopM->where($condition)->count();
        $shopRows = $shopM->where($condition)->order('add_time desc')->select();
        $pageObj = new Page($count, $pageSize);
        return $this->succLogicReturn('ok', array('list' => $shopRows, 'count' => $count, 'page' => $pageObj));
    }

    /**
     * 可管理店铺等级
     * @param type $shopLevel
     * @return type
     */
    public function getShopManageLevelMap($shopLevel) {
        $shopConfig = LibF::LC('shop');
        $shopManageLevelMap = $shopConfig['shop_manage_level'][$shopLevel];
        $shopManageLevelMap = $shopManageLevelMap ? $shopManageLevelMap->toarray() : array();
        return $shopManageLevelMap;
    }

    /**
     * 店铺管理区域列表
     * @param type $params
     * @return type
     */
    public function getShopManageRegionList($params) {
        $page = $params['page'];
        $pageSize = $params['page_size'];
        $shopId = $params['shop_id'];
        $region_1 = $params['region_1'];
        $region_2 = $params['region_2'];
        $region_3 = $params['region_3'];
        $region_4 = $params['region_4'];

        $shopManageRegionM = LibF::M('shop_manage_region');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        $regionConf = LibF::LC('region');
        $regionFieldMap = $regionConf['manage_region_field_map'];
        $where = array('shop_id' => $shopId);
        if ($region_4) {
            $where[$regionFieldMap[4]] = $region_4;
        } elseif ($region_3) {
            $where[$regionFieldMap[3]] = $region_3;
        } elseif ($region_2) {
            $where[$regionFieldMap[2]] = $region_2;
        } elseif ($region_1) {
            $where[$regionFieldMap[1]] = $region_1;
        }
        $offset = ($page - 1) * $pageSize;
        $count = $shopManageRegionM->where($where)->count();

        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $shopManageRegionM->where($where)->limit($offset, $pageSize)->order('add_time desc')->select();
        $regionIdList = array();
        if (!$rows) {
            return $regionIdList;
        }
        foreach ($rows AS $row) {
            $regionPath = $row['region_path'];
            $row['region_path'] = explode(',', trim($regionPath, ','));
            $regionIdList = array_merge($regionIdList, $row['region_path']);
        }
        $regionMap = array();
        if ($regionIdList) {
            $regionM = LibF::M('region');
            $regionRows = $regionM->field('region_id,local_name')->where(array('region_id' => array('in', $regionIdList)))->select();

            foreach ($regionRows AS $row) {
                $regionMap[$row['region_id']] = $row['local_name'];
            }
        }

        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    /**
     * 店铺管理区域列表
     * @param type $params
     * @return type
     */
    public function getShopSaleRegionList($params) {
        $page = $params['page'];
        $pageSize = $params['page_size'];
        $shopId = $params['shop_id'];
        $region_1 = $params['region_1'];
        $region_2 = $params['region_2'];
        $region_3 = $params['region_3'];
        $region_4 = $params['region_4'];

        $shopManageRegionM = LibF::M('shop_sale_region');
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGetZeroInt($pageSize)) {
            $pageSize = LibF::C('site')->get('page_size');
        }
        $regionConf = LibF::LC('region');
        $regionFieldMap = $regionConf['manage_region_field_map'];
        $where = array('shop_id' => $shopId);
        if ($region_4) {
            $where[$regionFieldMap[4]] = $region_4;
        } elseif ($region_3) {
            $where[$regionFieldMap[3]] = $region_3;
        } elseif ($region_2) {
            $where[$regionFieldMap[2]] = $region_2;
        } elseif ($region_1) {
            $where[$regionFieldMap[1]] = $region_1;
        }
        $offset = ($page - 1) * $pageSize;
        $count = $shopManageRegionM->where($where)->count();
        $page = new Page($count, $pageSize);
        if ($offset > $count) {
            $data = array('count' => $count, 'list' => array(), 'filter' => $params, 'regionMap' => array());
            return $this->logicReturn(200, 'ok', $data);
        }
        $rows = $shopManageRegionM->where($where)->limit($offset, $pageSize)->order('add_time desc')->select();
        $regionIdList = array();
        if (!$rows) {
            return $regionIdList;
        }
        foreach ($rows AS $row) {
            $regionIdList[$row['region_1']] = $row['region_1'];
            $regionIdList[$row['region_2']] = $row['region_2'];
            $regionIdList[$row['region_3']] = $row['region_3'];
            $regionIdList[$row['region_4']] = $row['region_4'];
        }
        $regionMap = array();
        if ($regionIdList) {
            $regionM = LibF::M('region');
            $regionRows = $regionM->field('region_id,local_name')->where(array('region_id' => array('in', $regionIdList)))->select();

            foreach ($regionRows AS $row) {
                $regionMap[$row['region_id']] = $row['local_name'];
            }
        }

        $data = array('count' => $count, 'list' => $rows, 'filter' => $params, 'regionMap' => $regionMap, 'page' => $page);
        return $this->logicReturn(200, 'ok', $data);
    }

    public function addSaleRegion($param) {
        $shopId = $param['shop_id'];
        $shipPrice = $param['ship_price'];
        $tookType = 0;
        if (!$param['region_1'] || !$param['region_2'] || !$param['region_3'] || !$param['region_4']) {
            return $this->logicReturn('0601', '请选择地区！');
        }
        // 店铺是否存在
        if (!$shopId) {
            return $this->logicReturn('0603', '请选择店铺！');
        }
        $shopRow = LibF::M('shop')->find($shopId);
        if (!$shopRow) {
            return $this->logicReturn('0605', '请选择店铺！');
        }
        // 运费小数点后两位
        if ($shipPrice && !Validate::isFloat($shipPrice)) {
            return $this->logicReturn('0607', '运费金额错误！');
        }
        $shipPrice = round($shipPrice, 2);
        // 地区是否正确
        $regionM = LibF::M('region');
        $regionRow = $regionM->field('region_id,region_path')->where(array('region_id' => $param['region_4'], 'disabled' => 'false'))->find();
         
        $regionPath = ',' . $param['region_1'] . ',' . $param['region_2'] . ',' . $param['region_3'] . ',' . $param['region_4'] . ',';
        if ($regionPath != $regionRow['region_path']) {
            return $this->logicReturn('0609', '地区不存在！');
        }

        // 销售地区是否添加过
        $shopSaleRegionM = LibF::M('shop_sale_region');
        $saleCondition = array('region_1' => $param['region_1'], 'region_2' => $param['region_2'], 'region_3' => $param['region_3'], 'region_4' => $param['region_4']);
        $isExist = $shopSaleRegionM->field('ssr_id')->where($saleCondition)->find();
        if ($isExist) {
            return $this->logicReturn('0611', '此地区已添加！');
        }



        // 添加的销售区域是否在管理区域内
        $shopManageRegionM = LibF::M('shop_manage_region');
        $condition = array("region_1={$param['region_1']} OR region_2={$param['region_2']} OR region_3={$param['region_3']} OR region_4={$param['region_4']}");
        $condition['shop_id'] = $shopId;
        $inManage = $shopManageRegionM->where($condition)->find();
        if (!$inManage) {
            return $this->logicReturn('0613', '此地区不在管理区域下！');
        }

        $addData['region_1'] = $param['region_1'];
        $addData['region_2'] = $param['region_2'];
        $addData['region_3'] = $param['region_3'];
        $addData['region_4'] = $param['region_4'];
        $addData['shop_id'] = $shopId;
        $addData['ship_price'] = $shipPrice;
        $addData['took_type'] = $tookType;
        $addData['add_time'] = time();
        $addData['add_admin_id'] = 1;

        $addRs = $shopSaleRegionM->add($addData);
        if (!$addRs) {
            return $this->logicReturn('0615', '添加失败!');
        }
        return $this->logicReturn(200);
    }

    public function getShopById($id) {
        $shopM = LibF::M('shop');
        return $shopM->find($id);
    }

    /**
     * 添加商铺
     * @fc 02
     * @param type $data
     * @return type
     */
    public function save($param) {
        $shopM = LibF::M('shop');
        
        $regionM = LibF::M('region');
        $shopManageRegionM = LibF::M('shop_manage_region');
        $shopConf = LibF::LC('shop'); # 店铺配置文件
        $shopLevelMap = $shopConf['shop_level']->toarray();

        $data['shop_name'] = $param['shop_name']; # *
        $data['admin_name'] = $param['admin_name'];
        $data['mobile_phone'] = $param['mobile_phone'];
        $data['tel_phone'] = $param['tel_phone'];
        
        $data['region_1'] = $param['region_1'];
        $data['region_2'] = $param['region_2'];
        $data['region_3'] = $param['region_3'];
        $data['region_4'] = $param['region_4'];  # * 
        
        $data['add_admin_id'] = $param['add_admin_id'];
        $add_shop_id = $param['add_shop_id'];

        /*if (!$data['add_admin_id'] || !$add_shop_id) {
            return $this->logicReturn('0206', '您没有权限进行此操作!');
        }

        // 添加店铺的上级店铺是否存在
        $adminShopRow = $shopM->field('level')->find($add_shop_id);
        if (!$adminShopRow) {
            return $this->logicReturn('0206', '您没有权限进行此操作!');
        }*/

        // 店铺名称
        if (Validate::isEmpty($data['shop_name'])) {
            return $this->logicReturn('0201', '店铺名称不能为空！');
        }

        // 店长姓名
        if (Tools::strlen($data['admin_name']) > 20) {
            return $this->logicReturn('0206', '店长姓名不能大于20字符!');
        }
        // 手机
        if ($data['mobile_phone'] && !Validate::isMobilePhone($data['mobile_phone'])) {
            return $this->logicReturn('0203', '手机号码错误！');
        }

        // 座机
        if ($data['tel_phone'] && !Validate::isTelPhone($data['tel_phone'])) {
            return $this->logicReturn('0203', '座机号码错误');
        }

        // 所在地区必须完整填写
        /*if (!$data['region_4']) {
            return $this->logicReturn('0203', '请选择店铺所在地区！');
        }

        // 选择地区是否存在
        $regionRow = $regionM->field('region_path')->find($data['region_4']);
        $regionPath = ',' . $data['region_1'] . ',' . $data['region_2'] . ',' . $data['region_3'] . ',' . $data['region_4'] . ',';
        if (!$regionRow || $regionRow['region_path'] != $regionPath) {
            return $this->logicReturn('0206', '请选择店铺所在地区！');
        }
        if ($adminShopRow['level'] != 1) {
            // 地区是否在管理区域下 
            $condition = "  (region_1={$data['region_1']} AND region_level=0) or
                        (region_2={$data['region_2']} AND region_level=1) or
                        (region_3={$data['region_3']} AND region_level=2) or
                        (region_4={$data['region_4']} AND region_level=3) ";
            $inManageRegion = $shopManageRegionM->where($condition)->find();
            if (!$inManageRegion) {
                return $this->logicReturn('0206', '所在区域不在管理区域下!');
            }
        }


        // 当前管理权限
        $adminShopLevel = $adminShopRow['level'];
        $adminManageShopLevel = $this->getShopManageLevelMap($adminShopLevel); */

        // 编辑
        if ($param['shop_id'] > 0) {
            $shopRow = $shopM->find($param['shop_id']);
            if (!$shopRow) {
                return $this->logicReturn('0201', '店铺不存在！');
            }

           /* // 店铺是否在其管理区域内
            $condition = "  (region_1={$shopRow['region_1']} AND region_level=0) or
                            (region_2={$shopRow['region_2']} AND region_level=1) or
                            (region_3={$shopRow['region_3']} AND region_level=2) or
                            (region_4={$shopRow['region_4']} AND region_level=3) ";
            $inManageRegion = $shopManageRegionM->where($condition)->find();
            if (!$inManageRegion) {
                return $this->logicReturn('0201', '店铺不存在！');
            }

            // 是否有权限管理该等级店铺
            if (!in_array($shopRow['level'], $adminManageShopLevel)) {
                return $this->logicReturn('0201', '你没有权限编辑' . $shopLevelMap[$shopRow['level']] . '等级店铺!');
            }*/

            $shopM->where(array('shop_id' => $param['shop_id']))->save($data);
        } else { // 添加
            $data['level'] = $param['level'];
            // 店铺编号是否存在 
            $data['shop_code'] = $this->generateShopCode();
            $shopCodeExits = $shopM->where(array('shop_code' => $data['code']))->find();
            if ($shopCodeExits) {
                return $this->logicReturn('0203', '店铺编号已经存在！');
            }

            // 店铺等级不能为空
            if (!array_key_exists($data['level'], $shopLevelMap)) {
                return $this->logicReturn('0206', '请选择店铺等级!');
            }

            // 添加店铺的上级店铺是否有权限添加此等级店铺 
            /*if (in_array($adminShopLevel, $adminManageShopLevel)) {
                return $this->logicReturn('0206', '你没有权限添加' . $shopLevelMap[$data['level']] . '等级店铺!');
            }*/
            // 店铺状态
            $data['status'] = $shopConf['shop_default_status'];
            // 添加时间
            $data['add_time'] = time();

            try {
                $shopM->add($data);
            } catch (Exception $exc) {
                return $this->logicReturn('0221', 'ok');
            }
        }
        return $this->logicReturn(200, 'ok');
    }

    /**
     * 生成商铺编号
     * @return string
     */
    private function generateShopCode() {
        list($usec, $sec) = explode(" ", microtime());
        list($prefix, $usec) = explode(".", $usec);
        $num = intval($sec . $usec);
        $charArr = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        $char = '';
        do {
            $key = ($num - 1) % 62;
            $char = $charArr[$key] . $char;
            $num = floor(($num - $key) / 62);
        } while ($num > 0);
        return $char;
    }

    private function setStatus($shopId, $status, $adminUid) {
        $shopConf = LibF::LC('shop');
        $statusMap = $shopConf['shop_status'];
        if (!array_key_exists($status, $statusMap)) {
            return false;
        }
        $shopM = LibF::M('shop');
        $ures = $shopM->where(array('shop_id' => $shopId))->save(array('status' => $status));
        if (!$ures) {
            return false;
        }
    }

    public function stopBusiness($shopId, $adminUid) {
        return $this->setStatus($shopId, 0, $adminUid);
    }

    public function startBusiness($shopId) {
        return $this->setStatus($shopId, 1, $adminUid);
    }

    /**
     * @code 05
     * @param type $regionId
     * @return type
     */
    public function addManageRegion($param) {
        $shopId = $param['shop_id'];
        $adminShopLevel = $param['admin_shop_level'];
        $adminId = $param['admin_id'];
        $regionParams[] = $param['region_1'];
        $regionParams[] = $param['region_2'];
        $regionParams[] = $param['region_3'];
        $regionParams[] = $param['region_4'];

        $regionId = $regionPath = null;
        $regionPath = ',';
        foreach ($regionParams AS $regionParam) {
            if ($regionParam) {
                $regionId = $regionParam;
                $regionPath .= $regionParam . ',';
            }
        }

        // 店铺是否存在
        $shopM = LibF::M('shop');
        $shopRow = $shopM->find($shopId);
        if (!$shopRow) {
            return $this->logicReturn('0501', '门店不存在！');
        }

        // 地区是否存在
        $regionM = LibF::M('region');
        $regionRow = $regionM->where(array('region_id' => $regionId, 'disabled' => 'false'))->find();
        if (!$regionRow || $regionRow['region_path'] != $regionPath) {
            return $this->logicReturn('0501', '地区不存在！');
        }

        $shopConf = LibF::LC('shop'); # 店铺配置文件
        $shopLevelMap = $shopConf['shop_level']->toarray();
        $adminManageShopLevel = $this->getShopManageLevelMap($adminShopLevel);
        // 是否有权限管理该等级店铺
        if (!in_array($shopRow['level'], $adminManageShopLevel)) {
            return $this->logicReturn('0201', '你没有权限管理' . $shopLevelMap[$shopRow['level']] . '等级店铺!');
        }

        // 地区或者子地区已经被分配
        $manageRegionM = LibF::M('shop_manage_region');
        $regionConfig = LibF::LC('region');
        $mangeRegionFieldMap = $regionConfig['manage_region_field_map'];
        $regionLevel = $regionRow['region_grade'];
        if (!$mangeRegionFieldMap[$regionLevel]) {
            return $this->logicReturn('0503', '地区信息错误！');
        }
        $regionField = $mangeRegionFieldMap[$regionLevel + 1];
        $disableAdd = $manageRegionM->field('smr_id')->alias('mr')->join('LEFT JOIN __SHOP__ s on s.shop_id=mr.shop_id')->where(array('mr.' . $regionField => $regionId, 's.level' => $shopRow['level']))->find();
        //var_dump($disableAdd);exit;
        if ($disableAdd) {
            return $this->logicReturn('0505', '此地区或者子地区已经被分配！');
        }

        $regionPathArr = explode(',', trim($regionRow['region_path'], ','));
        if (($regionLevel + 1) != count($regionPathArr)) {
            return $this->logicReturn('0507', '地区信息错误！');
        }

        $addData = null;
        foreach ($regionPathArr AS $regionNk => $regionNode) {
            if (!$mangeRegionFieldMap[$regionNk + 1]) {
                return $this->logicReturn('0509', '地区信息错误！');
            }
            $addData[$mangeRegionFieldMap[$regionNk + 1]] = $regionNode;
        }
        $addData['region_path'] = $regionPath;
        $addData['shop_id'] = $shopId;
        $addData['region_id'] = $regionId;
        $addData['region_level'] = $regionLevel;
        $addData['add_time'] = time();
        $addData['add_admin_id'] = $adminId;
        $addRs = $manageRegionM->add($addData);
        if (!$addRs) {
            return $this->logicReturn('0511', '添加失败!');
        }
        return $this->logicReturn(200);
    }

    public function getRegionCity($shopId) {
        $manageRegionM = M('manage_region');
        $rows = $manageM->field('distinct(region_id)')->where(array('shop_id' => $shopId))->select();
        $list = array();
        foreach ($rows AS $row) {
            $list[] = $row['region_id'];
        }
        return $list;
    }

    public function getSubShop($shopId, $level) {
        $manageRegionM = M('manage_region');
        $rows = $manageM->field('region_id,region_level')->where(array('shop_id' => $shopId))->select();
        $regionFieldMap = array(1 => 'city_id', 2 => 'county_id', 3 => 'town_id', 4 => 'village_id');
        $where = null;
        foreach ($rows AS $row) {
            $where[$row['shop_level']] = $row['region_id'];
        }
        $where['level'] = array('level' => array('lt', $level));
        $shopRows = M('shop')->where($where)->select();
        return $shopRows;
    }

    public function checkExist($region_id, $level) {
        $manageRegionM = M('manage_region');
        $regionFieldMap = array(1 => 'city_id', 2 => 'county_id', 3 => 'town_id', 4 => 'village_id');
        $row = $manageM->field('region_id')->where(array($regionField => $regionId))->find();
        if ($row) {
            return true;
        }
        return false;
    }

    public function addSellRegion($param) {
        /* `shop_id` mediumint(6) unsigned NOT NULL COMMENT '自增ID号，管理员代号',
          `city_id` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '市',
          `county_id` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '县',
          `town_id` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '镇',
          `village_id` mediumint(6) unsigned NOT NULL DEFAULT '0' COMMENT '村',
          `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',,
          `add_admin_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后操作管理元ID',
          UNIQUE KEY `province_id` (`province_id`,`city_id`,`county_id`,`town_id`,`village_id`) */
    }

    /**
     * 获取门店的相关信息
     * 
     * @param $shop_id
     * @return array
     */
    public static function getShopArray($shop_id = 0,$param = array()) {

        $model = LibF::M('shop');
        if ($shop_id) {
            return $model->where('shop_id=' . $shop_id)->find();
        } else {
            $shopObject = array();
            
            $param['status'] = 1;
            $data = $model->where($param)->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $shopObject[$value['shop_id']] = $value;
                }
            }
            return $shopObject;
        }
    }

    /**
     * 根据 用户手机号查询属于哪个门店
     * 
     * @param $mobile
     * @return array
     */
    public function getShopInfo($mobile) {
        $model = LibF::M('shop');
        if ($mobile)
            $data = $model->where('mobile_phone = ' . $mobile)->find();
        else
            return FALSE;
    }

    /**
     * 获取指定商户送气工
     * 
     * @param $shop_id
     */
    public function getShopShipper($shop_id) {
        if (empty($shop_id))
            return FALSE;

        $data = LibF::M("shipper")->where('shop_id=' . $shop_id)->select();
        return $data;
    }

    public function getUnalloatSubSaleRegion($p_id, $shop_id) {
        $regionMap = array();
        $regionM = LibF::M('region');
        $shopManageRegionM = LibF::M('shop_manage_region');
        if (!Validate::isGetZeroInt($p_id)) {
            return $regionMap;
        }
        // 市
        if ($p_id == 0) {
            $manageRows = $shopManageRegionM->field('distinct(region_1) city_id')->select();
            if (!$manageRows) {
                return $regionMap;
            }
            $cityIds = null;
            foreach ($manageRows AS $manageRow) {
                $cityIds[] = $manageRow['city_id'];
            }
            $regionCondition = array('region_id' => array('in', $cityIds));
            return $regionM->field('region_id,local_name')->where($regionCondition)->select();
        }

        // 县 || 镇 || 村
        $regionRow = $regionM->find($p_id);
        if (!$regionRow) {
            return $regionMap;
        }
        $regionPathArr = explode(',', trim($regionRow['region_path'], ','));
        $regionLevel = $regionRow['region_grade'];
        $regionConfig = LibF::LC('region');
        $mangeRegionFieldMap = $regionConfig['manage_region_field_map'];
        $condition = array('shop_id' => $shop_id);
        foreach ($regionPathArr AS $k => $v) {
            $fieldName = $mangeRegionFieldMap[$k + 1];
            $condition[0] .= " $fieldName=$v or ";
        }
        $condition[0] = rtrim($condition[0], 'or ');
        $manageRegionRow = $shopManageRegionM->where($condition)->find();
        //var_dump($shopManageRegionM->_sql());exit;
        if (!$manageRegionRow) {
            return $regionMap;
        }
        $manageRegionLevel = $manageRegionRow['region_level'];

        $nextLevel = $regionLevel + 1;
        if ($regionLevel >= $manageRegionLevel) {
            $regionCondition = array('p_region_id' => $p_id);
            $regionMap = $regionM->field('region_id,local_name')->where($regionCondition)->select();
        } else {
            $nextLevelKey = $nextLevel + 1;
            $fieldName = $mangeRegionFieldMap[$nextLevelKey] ? $mangeRegionFieldMap[$nextLevelKey] : null;
            if ($fieldName && $manageRegionRow[$fieldName]) {
                $regionCondition['region_id'] = $manageRegionRow[$fieldName];
            } else {
                return $regionMap;
            }
            $regionMap = $regionM->field('region_id,local_name')->where($regionCondition)->select();
        }
        // 抛出一分配的
        if ($nextLevel == 3 && $regionMap) {
            $regionIdArr = null;
            foreach ($regionMap AS $k => $v) {
                $regionIdArr[$k] = $v['region_id'];
            }
            $shopSaleRegionM = LibF::M('shop_sale_region');
            $rows = $shopSaleRegionM->field('region_4')->where(array('region_4' => array('in', $regionIdArr)))->select();
            $regionIdArr = array_flip($regionIdArr);
            foreach ((array) $rows AS $row) {
                unset($regionMap[$regionIdArr[$row['region_4']]]);
            }
        }
        sort($regionMap);
        return $regionMap;
    }

    public function getSubSaleRegion($id) {
        $regionMap = array();
        $regionM = LibF::M('region');
        $shopSaleRegionM = LibF::M('shop_sale_region');
        if (!Validate::isGetZeroInt($id)) {
            return $regionMap;
        }
        if ($id == 0) {
            $manageRows = $shopSaleRegionM->field('distinct(region_1) city_id')->select();
        } else {
            $regionRow = $regionM->where(array('region_id' => $id, 'disabled' => 'false'))->find(); 
            if (!$regionRow) {
                return $regionMap;
            }
            $regionConfig = LibF::LC('region');
            $mangeRegionFieldMap = $regionConfig['manage_region_field_map'];
            $currentRegionField = $mangeRegionFieldMap[$regionRow['region_grade']+1];
            $newxRegionField = $mangeRegionFieldMap[$regionRow['region_grade']+2];
            if (!$currentRegionField || !$newxRegionField) {
                return $regionMap;
            }
            $manageRows = $shopSaleRegionM->field("distinct({$newxRegionField}) city_id")->where(array($currentRegionField=>$id))->select();
        }

        if (!$manageRows) {
            return $regionMap;
        }
        $cityIds = null;
        foreach ($manageRows AS $manageRow) {
            $cityIds[] = $manageRow['city_id'];
        }
        $regionCondition = array('region_id' => array('in', $cityIds), 'disabled' => 'false');
        return $regionM->field('region_id,local_name')->where($regionCondition)->select();
    }

    /**
     * 剩余未分配管理区域
     * @param type $p_id
     * @param type $shop_id
     * @return array
     */
    public function getSubManageRegion($p_id, $shop_id) {
        $regionMap = array();
        $regionM = LibF::M('region');
        $shopM = LibF::M('shop');
        $shopManageRegionM = LibF::M('shop_manage_region');
        if (!Validate::isGetZeroInt($p_id)) {
            return $regionMap;
        }

        if (!Validate::isGetZeroInt($shop_id)) {
            return $regionMap;
        }
        $shopRow = $shopM->find($shop_id);
        if (!$shopRow) {
            return $regionMap;
        }
        $childRegionIdArr = null;
        // --市
        if ($p_id == 0) {
            // 气站管理所有区域
            if ($shopRow['level'] == 1) {
                return $regionM->field('region_id,local_name')->where(array('p_region_id' => 0))->select();
            }
            // 门店管理已分配管理区域
            $manageRows = $shopManageRegionM->field('distinct(region_1) city_id')->where(array('shop_id' => $shop_id))->select();
            if (!$manageRows) {
                return $regionMap;
            }
            foreach ($manageRows AS $manageRow) {
                $childRegionIdArr[] = $manageRow['city_id'];
            }
            $regionCondition = array('region_id' => array('in', $childRegionIdArr));
            return $regionM->field('region_id,local_name')->where($regionCondition)->select();
        }

        // -- 下级 县 镇 村
        // 气站管理所有区域
        $parentRegionRow = $regionM->find($p_id);
        if (!$parentRegionRow) {
            return $regionMap;
        }
        if ($shopRow['level'] == 1) {
            return $regionM->field('region_id,local_name')->where(array('p_region_id' => $p_id))->select();
        }
        // 门店管理已分配管理区域
        $parentRegionPathArr = explode(',', trim($parentRegionRow['region_path'], ','));
        $parentRegionLevel = $parentRegionRow['region_grade'];
        $regionConfig = LibF::LC('region');
        $mangeRegionFieldMap = $regionConfig['manage_region_field_map'];
        $condition = array('shop_id' => $shop_id);
        foreach ($parentRegionPathArr AS $k => $v) {
            $fieldName = $mangeRegionFieldMap[$k + 1];
            $condition[0] .= " $fieldName=$v or ";
        }
        $condition[0] = rtrim($condition[0], 'or ');
        $childrenManageRegionRow = $shopManageRegionM->where($condition)->find();

        if (!$childrenManageRegionRow) {
            return $regionMap;
        }
        $childrenManageRegionLevel = $childrenManageRegionRow['region_level'];
        $nextLevel = $parentRegionLevel + 1;

        if ($parentRegionLevel >= $childrenManageRegionLevel) {
            $regionCondition = array('p_region_id' => $p_id);
            $regionMap = $regionM->field('region_id,local_name')->where($regionCondition)->select();
        } else {
            $nextLevelKey = $nextLevel + 1;
            $fieldName = $mangeRegionFieldMap[$nextLevelKey] ? $mangeRegionFieldMap[$nextLevelKey] : null;
            if ($fieldName && $childrenManageRegionRow[$fieldName]) {
                $regionCondition['region_id'] = $childrenManageRegionRow[$fieldName];
            } else {
                return $regionMap;
            }
            $regionMap = $regionM->field('region_id,local_name')->where($regionCondition)->select();
        }
        return $regionMap;
    }

    /**
     * 店铺管理区域列表
     * @param type $params
     * @return type
     */
    public function getUnallotSubManageRegion($p_id, $shop_id) {
        $regionMap = $this->getSubManageRegion($p_id, $shop_id);

        // 抛出一分配的
        if ($regionMap) {
            $regionIdArr = null;
            foreach ($regionMap AS $k => $v) {
                $regionIdArr[$k] = $v['region_id'];
            }
            $shopSaleRegionM = LibF::M('shop_manage_region');
            $rows = $shopSaleRegionM->field('region_id')->where(array('region_id' => array('in', $regionIdArr), 'shop_id' => array('neq', $shop_id)))->select();

            $regionIdArr = array_flip($regionIdArr);

            foreach ((array) $rows AS $row) {
                unset($regionMap[$regionIdArr[$row['region_id']]]);
            }
        }

        sort($regionMap);
        return $regionMap;
    }

    public function delSaleRegion($shopId, $ssrId) {
        $shopSaleRegionM = LibF::M('shop_sale_region');
        return $shopSaleRegionM->where(array('shop_id' => $shopId, 'ssr_id' => $ssrId))->delete();
    }

    public function delManageRegion($shopId, $smrId) {
        // 是否存在
        $shopManageRegionM = LibF::M('shop_manage_region');
        /* $shopSaleRegionM = LibF::M('shop_sale_region');
          $manageRegionRow = $shopManageRegionM->where(array('shop_id' => $shopId, 'smr_id' => $smrId))->find();
          if (!$manageRegionRow) {
          return $this->logicReturn('0701', '管理地区不存在');
          }

          $regionId = $manageRegionRow['region_id'];
          $regionLevel = $manageRegionRow['region_level'];

          // 村级直接删除
          if ($regionLevel == 3) {
          return $shopManageRegionM->delete($smrId);
          }


          // 市县镇是否还有销售区域
          $regionLevelKey = $regionLevel + 1;
          $regionConfig = LibF::LC('region');
          $mangeRegionFieldMap = $regionConfig['manage_region_field_map'];
          $fieldName = $mangeRegionFieldMap[$regionLevelKey];
          $hasSubManage = $shopManageRegionM->where(array($fieldName => $regionId, 'region_level' => array('gt', $regionLevel)))->find();
          if ($hasSubManage) {
          return $this->logicReturn('0703', '请先删除下级管理区域');
          }

          // 下级销售区域
          $hasSubSaleRegion = $shopSaleRegionM->where(array($fieldName => $regionId))->find();
          if ($hasSubSaleRegion) {
          return $this->logicReturn('0705', '请先删除下级销售区域');
          } */

        return $shopManageRegionM->where(array('smr_id' => $smrId))->delete();
    }

    /**
     * 门店的基础信息
     * 
     * 
     */
    public function shopArrayData($shop_id = 0) {
        $returnData = array();

        if (!empty($shop_id)) {
            $where = "shop_id = " . $shop_id;
            $returnData = LibF::M('shop')->where($where)->find();
        } else {
            $data = LibF::M('shop')->select();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $returnData[$value['shop_id']] = $value;
                }
            }
        }
        return $returnData;
    }

    /**
     * 根据条件获取门店信息
     * @param type $where
     * @return type
     */
    public function getshopbywhere($where = array(), $field = '*') {
        return LibF::M('shop')->field($field)->where($where)->select();
    }
    
    

}
