<?php

/**
 * Description of Regionmanage
 *
 * @author zxg
 */
class RegionmanageController extends \Com\Controller\Common {

    public function indexAction() {
        //$region[1] = $this->getRequest()->getQuery('region_1');
        //$region[2] = $this->getRequest()->getQuery('region_2');
        $region[0] = 10; //河北
        $region[1] = 140; //沧州
        $region[2] = $this->getRequest()->getQuery('region_2'); //区县
        $region[3] = $this->getRequest()->getQuery('region_3'); //村镇

        $page = $this->getRequest()->getQuery('page', 1);
        $pageSize = 15;
        $name = $this->getRequest()->getQuery('name');

        $filter = array('region' => $region, 'name' => $name, 'type' => 4, 'page' => $page, 'page_size' => $pageSize);
        if ($region[3] > 0) {
            $filter['region_id'] = $region[3];
        }

        $this->_view->assign('page', $page);
        $this->_view->assign('pagesize', $pageSize);
        $this->_view->assign('region',$region);
        $this->_view->assign('name',$name);

        //得到所有区域
        $regionData = array();
        $regionModel = new RegionModel();
        $regionData = $regionModel->getRegionObject();
        $this->_view->assign('regionData', $regionData);
        
        $regionD = LibF::D('Region');
        $res = $regionD->getMangeRegionList($filter);
        if (!empty($res['ext']['list'])) {
            foreach ($res['ext']['list'] as &$value) {
                $regionList = substr($value['regionids'], 1, -1);
                $regionArr = !empty($regionList) ? explode(',', $regionList) : array();
                $value['sheng'] = isset($regionArr[0]) ? $regionData[$regionArr[0]]['region_name'] : '';
                $value['shi'] = isset($regionArr[1]) ? $regionData[$regionArr[1]]['region_name'] : '';
                $value['qu'] = isset($regionArr[2]) ? $regionData[$regionArr[2]]['region_name'] : '';
            }
        }

        $this->_view->assign($res['ext']);
    }

    public function addAction() {
        if (IS_POST) {
            $region[1] = 10;
            $region[2] = 140;
            $region[3] = $this->getRequest()->getPost('region_2');

            $pid = $this->getRequest()->getPost('pid');
            if ($pid) {
                if (!empty($region[3])) {
                    $param['parent_id'] = $region[3];
                    $param['region_type'] = 4;
                    $param['regionids'] = ',' . implode(',', array_filter($region)) . ',';
                }
                $param['region_name'] = $this->getRequest()->getPost('region_name');
                $status = LibF::M('region')->where(array('region_id' => $pid))->save($param);
            } else {
                $idata = array();
                $app = new App();
                $param['regionsn'] = 'qy' . $app->build_order_no();
                $param['parent_id'] = $region[3];
                $param['region_type'] = 4;
                $param['regionids'] = ',' . implode(',', array_filter($region)) . ',';
                $param['status'] = 1;

                $region_name = $this->getRequest()->getPost('region_name');
                if (!empty($region_name)) {
                    $rname = explode(PHP_EOL, $region_name);
                    if (!empty($rname)) {
                        foreach ($rname as $value) {
                            $param['region_name'] = preg_replace('/[\'\s| ]+/', '', $value);
                            $param['region_name'] = str_replace(array("\r\n", "\r", "\n"), "", $param['region_name']);
                            $idata[] = $param;
                        }
                    }
                }
                $status = LibF::M('region')->uinsertAll($idata);
            }
            $this->success('ok', '/regionmanage/index');
        }

        $pid = $this->getRequest()->getParam('id');
        $regionM = libF::M('region');
        if ($pid) {
            $regionRow = $regionM->where(array('region_id' => $pid))->find();
            if (!$regionRow) {
                $this->error('地区不存在');
            }
        }
        $this->_view->assign('pid', $pid);
        $this->_view->assign('regionData', $regionRow);
    }

    public function editAction() {
        $region_id = $this->getRequest()->getQuery('id');
        if ($region_id) {

            $regionModel = new RegionModel();
            $regionObject = $regionModel->getRegionObject();
            
            $regionData = LibF::M('region')->where(array('region_id' => $region_id, 'status' => 1))->find();
            $this->_view->assign('regionData', $regionData);
            $sheng = $shi = $xian = 0;
            $shi_name = $xian_name = '';
            if (!empty($regionData)) {
                $regionList = substr($regionData['regionids'], 1, -1);
                $regionArr = !empty($regionList) ? explode(',', $regionList) : array();
                $sheng = isset($regionArr[0]) ? $regionArr[0] : '';
                $shi = isset($regionArr[1]) ? $regionArr[1] : '';
                $shi_name = isset($regionArr[1]) ? $regionObject[$regionArr[1]]['region_name'] : '';
                $xian = isset($regionArr[2]) ? $regionArr[2] : '';
                $xian_name = isset($regionArr[2]) ? $regionObject[$regionArr[2]]['region_name'] : '';
            }

            $this->_view->assign('sheng', $sheng);
            $this->_view->assign('shi', $shi);
            $this->_view->assign('shi_name',$shi_name);
            $this->_view->assign('xian', $xian);
            $this->_view->assign('xian_name',$xian_name);

            $this->_view->assign('pid', $region_id);
            $this->_view->display('regionmanage/add.phtml');
        } else {
            $this->error('当前id不存在', '/regionmanage/index');
        }
    }

    public function delAction() {
        $region_id = $this->getRequest()->getQuery('id');
        if ($region_id) {
            $pid = $this->getRequest()->getQuery('pid');
            $list = '/regionmanage/index';
            if (!empty($pid)) {
                $list = "/regionmanage/list?id=" . $pid;
            }
            $regionModel = new RegionModel();
            $regionData = $regionModel->del($region_id);
            if ($regionData['status'] == 200) {
                $this->success('ok', $list);
            } else {
                $this->error('删除失败', '/regionmanage/index');
            }
        } else {
            $this->error('当前id不存在', '/regionmanage/index');
        }
    }
    
    public function listAction() {
        $region[0] = 10;
        $region[1] = 140;
        $region[2] = $this->getRequest()->getQuery('region_2'); //市
        $region[3] = $this->getRequest()->getQuery('region_3'); //县
        $region[4] = $this->getRequest()->getQuery('region_4'); //村镇
        $pid = $this->getRequest()->getQuery('id');
        $this->_view->assign('pid', $pid);
        if ($pid) {
            $getParam[] = "id=" . $pid;
            if(!empty($getParam))
                $this->_view->assign('getparamlist',  implode('&', $getParam));

            $regionData = libF::M('region')->where(array('region_id' => $pid))->find();
            if (!empty($regionData)) {
                $region[3] = $pid;

                $regionList = substr($regionData['regionids'], 1, -1);
                $regionArr = !empty($regionList) ? explode(',', $regionList) : array();
                $region[2] = isset($regionArr[2]) ? $regionArr[2] : '';
            }
        }

        $page = $this->getRequest()->getQuery('page', 1);
        $pageSize = 15;

        $name = $this->getRequest()->getQuery('name');
        $filter = array('region' => $region, 'name' => $name, 'type' => 5, 'page' => $page, 'page_size' => $pageSize);
        if ($region[4] > 0) {
            $filter['region_id'] = $region[4];
        }
        $this->_view->assign('name', $name);

        //得到所有区域
        $regionData = array();
        $regionModel = new RegionModel();
        $regionObject = $regionModel->getRegionObject();
        $this->_view->assign('regionData', $regionObject);

        $regionD = LibF::D('Region');
        $res = $regionD->getMangeRegionList($filter);
        if (!empty($res['ext']['list'])) {
            foreach ($res['ext']['list'] as &$value) {
                $regionList = substr($value['regionids'], 1, -1);
                $regionArr = !empty($regionList) ? explode(',', $regionList) : array();
                $value['sheng'] = isset($regionArr[0]) ? $regionObject[$regionArr[0]]['region_name'] : '';
                $value['shi'] = isset($regionArr[1]) ? $regionObject[$regionArr[1]]['region_name'] : '';
                $value['xian'] = isset($regionArr[2]) ? $regionObject[$regionArr[2]]['region_name'] : '';
                $value['qu'] = isset($regionArr[3]) ? $regionObject[$regionArr[3]]['region_name'] : '';
            }
        }
        $this->_view->assign($res['ext']);

        $this->_view->assign('page', $page);
        $this->_view->assign('pagesize', $pageSize);
        $this->_view->assign('region', $region);

        $CommonDataModel = new CommonDataModel();   //所属区域
        $areaData = $CommonDataModel->getQuarterData();
        $this->_view->assign('areaData', $areaData);

        $shipperModel = new ShipperModel(); //送气工
        $shipperData = $shipperModel->getShipperArray('');
        $this->_view->assign('shipperData', $shipperData);
    }

    public function addlistAction() {
        if (IS_POST) {
            $region[1] = 10;
            $region[2] = 140;
            $region[3] = $this->getRequest()->getPost('region_2');
            $region[4] = $this->getRequest()->getPost('region_3');
            
            $shipper_id = $this->getRequest()->getPost('shipper_id');  //负责送气工
            $area_id = $this->getRequest()->getPost('area_id'); //区域
            
            $page = $this->getRequest()->getPost('page',1);

            $pid = $this->getRequest()->getPost('pid');
            $tid = $this->getRequest()->getPost('tid');
            if ($tid) {
                if (!empty($region[4])) {
                    $param['parent_id'] = $region[4];
                    $param['region_type'] = 5;
                    $param['regionids'] = ',' . implode(',', array_filter($region)) . ',';
                }
                $param['region_name'] = $this->getRequest()->getPost('region_name');
                $param['region_name'] = preg_replace('/[\'\s| ]+/', '', $param['region_name']);
                $param['region_name'] = str_replace(array("\r\n", "\r", "\n"), "", $param['region_name']);
                $status = LibF::M('region')->where(array('region_id' => $tid))->save($param);
                
                //增加区域绑定
                if ($shipper_id && $area_id) {
                    //$where['shipper_id'] = $shipper_id;
                    //$where['area_id'] = $area_id;
                    $where['region_id'] = $tid;

                    $saVal['shipper_id'] = $shipper_id;
                    $saVal['area_id'] = $area_id;

                    $shipperArea = LibF::M('shipper_area')->where($where)->find();
                    if (empty($shipperArea)) {
                        $saVal['region_id'] = $tid;
                        $saVal['status'] = 1;
                        $saVal['ctime'] = time();
                        LibF::M('shipper_area')->add($saVal);
                    } else {
                        LibF::M('shipper_area')->where($where)->save($saVal);
                    }
                }
                
            } else {
                $idata = array();
                
                $app = new App();
                $param['regionsn'] = 'qy' . $app->build_order_no();
                $param['parent_id'] = $region[4];
                $param['region_type'] = 5;
                $param['regionids'] = ',' . implode(',', array_filter($region)) . ',';
                $param['status'] = 1;

                $region_name = $this->getRequest()->getPost('region_name');
                if (!empty($region_name)) {
                    $rname = explode(PHP_EOL, $region_name);
                    if (!empty($rname)) {
                        foreach ($rname as $value) {
                            $param['region_name'] = preg_replace('/[\'\s| ]+/', '', $value);
                            $param['region_name'] = str_replace(array("\r\n", "\r", "\n"), "", $param['region_name']);
                            $idata[] = $param;
                        }
                    }
                }
                $status = LibF::M('region')->uinsertAll($idata);
            }
            $this->success('ok', '/regionmanage/list?id='.$region[4]."&page=".$page);
        }

        $pid = $this->getRequest()->getQuery('id');
        $page = $this->getRequest()->getQuery('page',1);
        $regionM = libF::M('region');
        if ($pid) {
            $regionData = $regionM->where(array('region_id' => $pid))->find();
            
            $regionModel = new RegionModel();
            $regionObject = $regionModel->getRegionObject();
            
            $sheng = $shi = $xian = 0;
            $shi_name = $xian_name = '';
            if (!empty($regionData)) {
                $regionList = substr($regionData['regionids'], 1, -1);
                $regionArr = !empty($regionList) ? explode(',', $regionList) : array();
                $sheng = isset($regionArr[0]) ? $regionArr[0] : '';
                $shi = isset($regionArr[1]) ? $regionArr[1] : '';
                $shi_name = !empty($shi) ? $regionObject[$shi]['region_name'] : '';
                $xian = isset($regionArr[2]) ? $regionArr[2] : '';
                $xian_name = isset($regionArr[2]) ? $regionObject[$regionArr[2]]['region_name'] : '';
                $qu_name = isset($regionArr[$pid]) ? $regionObject[$pid]['region_name'] : '';
            }

            $this->_view->assign('sheng', $sheng);
            $this->_view->assign('shi', $shi);
            $this->_view->assign('shi_name',$shi_name);
            $this->_view->assign('xian', $xian);
            $this->_view->assign('xian_name',$xian_name);
            $this->_view->assign('qu',$pid);
            
            $CommonDataModel = new CommonDataModel();   //所属区域
            $areaData = $CommonDataModel->getQuarterData();
            $this->_view->assign('areaData', $areaData);

            $shipperModel = new ShipperModel(); //送气工
            $shipperData = $shipperModel->getShipperArray('');
            $this->_view->assign('shipperData', $shipperData);
            
            $this->_view->assign('page',$page);
            $this->_view->assign('pid', $pid);

            if (!$regionData) {
                $this->error('地区不存在');
            }
        }
        //$this->_view->assign('regionData', $regionData);
    }

    public function editlistAction() {
        $region_id = $this->getRequest()->getQuery('id');
        $pid = $this->getRequest()->getQuery('pid');
        $page = $this->getRequest()->getQuery('page');
        if ($region_id && $pid) {

            $regionModel = new RegionModel();
            $regionObject = $regionModel->getRegionObject();
            
            $regionData = new Model('region');
            $filed = " rq_region.*,rq_shipper_area.shipper_id,rq_shipper_area.area_id";
            $leftJoin = " LEFT JOIN rq_shipper_area ON rq_region.region_id = rq_shipper_area.region_id ";
            $regionData = $regionData->join($leftJoin)->field($filed)->where(array('rq_region.region_id' => $region_id, 'rq_region.status' => 1))->find();
            //$regionData = $regionData->where(array('region_id' => $region_id, 'status' => 1))->find();
            
            $this->_view->assign('regionData', $regionData);
            $sheng = $shi = $xian = 0;
            $shi_name = $xian_name = '';
            if (!empty($regionData)) {
               $regionList = substr($regionData['regionids'], 1, -1);
                $regionArr = !empty($regionList) ? explode(',', $regionList) : array();
                $sheng = isset($regionArr[0]) ? $regionArr[0] : '';
                $shi = isset($regionArr[1]) ? $regionArr[1] : '';
                $shi_name = !empty($shi) ? $regionObject[$shi]['region_name'] : '';
                $xian = isset($regionArr[2]) ? $regionArr[2] : '';
                $xian_name = isset($regionArr[2]) ? $regionObject[$regionArr[2]]['region_name'] : '';
                $cun_name = isset($regionArr[$region[3]]) ? $regionObject[$region[3]]['region_name'] : '';
            }

            $this->_view->assign('sheng', $sheng);
            $this->_view->assign('shi', $shi);
            $this->_view->assign('shi_name', $shi_name);
            $this->_view->assign('xian', $xian);
            $this->_view->assign('xian_name', $xian_name);
            $this->_view->assign('qu', $pid);
            $this->_view->assign('tid', $region_id);
            
            $CommonDataModel = new CommonDataModel();   //所属区域
            $areaData = $CommonDataModel->getQuarterData();
            $this->_view->assign('areaData', $areaData);

            $shipperModel = new ShipperModel(); //送气工
            $shipperData = $shipperModel->getShipperArray('');
            $this->_view->assign('shipperData', $shipperData);

            $this->_view->assign('page', $page);
            $this->_view->display('regionmanage/addlist.phtml');
        } else {
            $this->error('当前id不存在', '/regionmanage/list/id/'.$pid);
        }
    }
}
