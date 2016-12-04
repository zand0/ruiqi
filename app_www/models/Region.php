<?php

/**
 * Description of Region
 *
 * @author zxg
 */
class RegionModel extends \Com\Model\Base {

    public function getSubRegionList($pRegionId, $field) {
        if (!$field) {
            $field = '*';
        }
        return LibF::M('region')->field($field)->where(array('parent_id' => $pRegionId))->select();
    }

    /**
     * 区域列表
     * @param type $filter
     */
    public function getMangeRegionList($filter) {
        $page = $filter['page'];
        $pageSize = $filter['page_size'];
        $region = isset($filter['region']) ? $filter['region'] : '';

        // 分页
        if (!Validate::isGtZeroInt($page)) {
            $page = 1;
        }
        if (!Validate::isGtZeroInt($pageSize)) {
            $page = 15;
        }
        $offset = ($page - 1) * $pageSize;

        //查询条件
        $condition['status'] = $where['rq_region.status'] = 1;
        if (isset($filter['region_id']) && $filter['region_id'] > 0) {
            $condition['region_id'] = $where['rq_region.region_id'] = $filter['region_id'];
        } else {
            if (isset($filter['type']) && !empty($filter['type']))
                $condition['region_type'] = $where['rq_region.region_type'] = $filter['type'];
            if (!empty($region))
                $condition['regionids'] = $where['rq_region.regionids'] = array('like', '%,' . implode(',', array_filter($region)) . ',%');
            if (!empty($filter['name']))
                $condition['region_name'] = $where['rq_region.region_name'] = array('like', '%' . $filter['name'] . '%');
        }

        $regionM = LibF::M('region');
        $count = $regionM->where($condition)->count();
        
        $list = array();
        $pageObj = '';
        if ($offset < $count) {
            //$list = $regionM->where($condition)->order('region_id DESC')->limit($offset, $pageSize)->select();
            
            $regionModel = new Model('region');
            $filed = " rq_region.*,rq_shipper_area.shipper_id,rq_shipper_area.area_id ";
            $leftJoin = " LEFT JOIN rq_shipper_area ON rq_region.region_id = rq_shipper_area.region_id ";
            $list = $regionModel->join($leftJoin)->field($filed)->where($where)->order('rq_region.region_id DESC')->limit($offset, $pageSize)->select();

            $pageCount = ceil($count / $pageSize);
            if ($pageCount > 1) {
                $pageObj = new Page($count, 20);
            }
        }
        return $this->logicReturn('200', '', array('list' => $list, 'count' => $count, 'pageObj' => $pageObj, 'filter' => $filter));
    }

    /**
     * 添加管理区域
     * 
     */
    public function add($param) {
        $pId = $param['pid'];
        $localName = $param['local_name'];
        $pPath = $param['local_path'];
        $regionM = LibF::M('Region');
        if ($pId !== 'new') {
            $regionRow = $regionM->where(array('region_id' => $pId, 'disabled' => 'false', 'region_path' => $pPath))->find();
            if (!$regionRow) {
                return $this->logicReturn('01', '地区不存在！');
            }
            if (strlen($localName) > 50) {
                return $this->logicReturn('03', '地区名字不能大于50字符');
            }
            if ($regionRow['region_grade'] > 2) {
                return $this->logicReturn('05', '此地区下部能添加下级区域！');
            }
        } else {
            $pId = 0;
            $pPath = ',';
        }
        try {
            $regionM->startTrans();
            $data['p_region_id'] = $pId;
            $data['local_name'] = $localName;
            $data['disabled'] = 'false';
            $data['package'] = 'mainland';
            $data['region_grade'] = $regionRow['region_grade'] + 1;
            $id = $regionM->add($data);
            $pPath = $pPath . $id . ',';
            $regionM->where(array('region_id' => $id))->save(array('region_path' => $pPath));
            $regionM->commit();
            return $this->logicReturn(200, '');
        } catch (Exception $exc) {
            $regionM->rollback();
            echo $exc->getMessage();
            exit;
            return $this->logicReturn('07', '添加失败！');
        }
    }

    /**
     * 删除区域
     * @param type $regionId
     * @return type
     */
    public function del($regionId) {
        if (!Validate::isGetZeroInt($regionId)) {
            return $this->logicReturn('01', '地区不存在');
        }
        $regionM = LibF::M('region');
        $regionRow = $regionM->where(array('region_id' => $regionId))->find();
        if (!$regionRow) {
            return $this->logicReturn('03', '地区不存在！');
        }

        // 有下地区不能删除
        if ($regionM->where(array('region_id' => $regionId))->save(array('status' => '-1'))) {
            return $this->logicReturn(200);
        }
        return $this->logicReturn('07', '删除失败！');
    }

    public function edit($param) {
        $id = $param['id'];
        $localName = $param['local_name'];
        if (!Validate::isGetZeroInt($id)) {
            return $this->logicReturn('01', '地区不存在');
        }
        if (strlen($localName) > 50) {
            return $this->logicReturn('03', '地区名字不能大于50字符');
        }
        try {
            $regionM = LibF::M('region');
            $regionM->where(array('region_id' => $id))->save(array('local_name'=>$localName));
            return $this->logicReturn('200');
        } catch (Exception $exc) {
            return $this->logicReturn('07', '编辑失败！');
        }
    } 
    
    /**
     * 获取 id 对应的地区
     * 
     * @param $id
     */
    public function getRegionObject() {

        $regionObject = array();

        //$condition = array('status' => 0);
        $list = LibF::M('region')->where($condition)->order('region_id ASC')->select();
        if (!empty($list)) {
            foreach ($list as $value) {
                $regionObject[$value['region_id']] = $value;
            }
        }
        return $regionObject;
    }

}
