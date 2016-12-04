<?php

/**
 * 公文审批
 */
class ApprovalController extends \Com\Controller\Common {

    protected $shop_id;
    protected $shop_level;
    protected $user_info;

    public function init() {
        parent::init();

        $session = Tools::session();
        $adminInfo = $session['userinfo'];
        if (!empty($adminInfo)) {
            $this->shop_id = $adminInfo['shop_id'];
            $this->shop_level = $adminInfo['shop_level'];
            $userData['user_id'] = $adminInfo['user_id'];
            $userData['username'] = $adminInfo['username'];
            $userData['photo'] = $adminInfo['photo'];
            $userData['mobile_phone'] = $adminInfo['mobile_phone'];
            $this->user_info = $userData;
        }
    }

    public function indexAction() {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        if (!empty($param)) {
            if (!empty($param['approvalsn']))
                $where['approvalsn'] = $param['approvalsn'];
            if (!empty($param['username']))
                $where['username'] = $param['username'];
            if (!empty($param['approval_status']))
                $where['approval_status'] = $param['approval_status'];
            if (!empty($param['approval_type']))
                $where['approval_type'] = $param['approval_type'];

            if (!empty($param['time_start']) && !empty($param['end_time']))
                $param['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['end_time'])), 'AND');

            unset($param['submit']);
        }
        
        $where['approval_user'] = $this->user_info['user_id'];

        $typeObject = array(0 => '普通', 1 => '紧急', 2 => '重要');
        $this->_view->assign('typeObject', $typeObject);
        $statusObject = array(0 => '未审批', 1 => '已审批', 2 => '拒绝');
        $this->_view->assign('statusObject', $statusObject);

        $approvalModel = new FillingModel();
        $data = $approvalModel->getDataList($param, 'approval', $where, 'time_created desc');

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);
    }
    
    public function myAction() {
        $param = $this->getRequest()->getPost();
        $this->_view->assign('param', $param);

        if (!empty($param)) {
            if (!empty($param['approvalsn']))
                $where['approvalsn'] = $param['approvalsn'];
            if (!empty($param['username']))
                $where['username'] = $param['username'];
            if (!empty($param['approval_status']))
                $where['approval_status'] = $param['approval_status'];
            if (!empty($param['approval_type']))
                $where['approval_type'] = $param['approval_type'];

            if (!empty($param['time_start']) && !empty($param['end_time']))
                $param['time_created'] = array(array('egt', strtotime($param['time_start'])), array('elt', strtotime($param['end_time'])), 'AND');

            unset($param['submit']);
        }

        $where['user'] = $this->user_info['user_id'];

        $typeObject = array(0 => '普通', 1 => '紧急', 2 => '重要');
        $this->_view->assign('typeObject', $typeObject);
        $statusObject = array(0 => '未审批', 1 => '已审批', 2 => '拒绝');
        $this->_view->assign('statusObject', $statusObject);

        $approvalModel = new FillingModel();
        $data = $approvalModel->getDataList($param, 'approval', $where, 'time_created desc');

        $this->_view->assign($data);
        $this->_view->assign('page', $param['page']);
    }

    public function detialAction() {
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('approval')->where(array('id' => $id))->find();
            if (!empty($data)) {
                if ($data['approval_genre'] == 1) {
                    $data['url'] = '/gastj/planinfo?sno=' . $data['approval_documents'];
                } else if ($data['approval_genre'] == 2) {
                    $data['url'] = '/instock/purchaseinfo?id=' . $data['approval_documents'];
                } else if ($data['approval_genre'] == 3) {
                    $data['url'] = '/instock/info?id=' . $data['approval_documents'];
                }
            }
            $this->_view->assign($data);
            $this->_view->assign('id', $id);
            $this->_view->assign('user_id',$this->user_info['user_id']);
        }
    }

    public function addAction() {
        $approvalModel = new ApprovalModel();
        if (IS_POST) {
            $app = new App();
            $data['approvalsn'] = 'sh' . $app->build_order_no();
            $data['title'] = $this->getRequest()->getPost('title');
            $data['approval_object'] = $this->getRequest()->getPost('approval_object');
            $data['approval_user'] = $this->getRequest()->getPost('user_id');
            $data['comment'] = $this->getRequest()->getPost('comment');
            $data['approval_type'] = $this->getRequest()->getPost('approval_type');
            $data['approval_annex'] = $this->getRequest()->getPost('file_name');
            $data['user'] = $this->user_info['user_id'];
            $data['username'] = $this->user_info['username'];
            $data['time_created'] = time();

            $id = $this->getRequest()->getPost('id');
            $status = $approvalModel->add($data, $id);

            $this->success('创建成功', '/approval/index');
        }
    }

    public function editeAction() {

        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $data = LibF::M('approval')->where(array('id' => $id))->find();
            $this->_view->assign($data);
        }
        $this->_view->assign('id', $id);
        $this->_view->display('approval/add.phtml');
    }

    public function delAction() {
        $approvalModel = new ApprovalModel();
        $id = $this->getRequest()->getQuery('id');
        if ($id) {
            $status = $approvalModel->delData($id);
        }
        $this->success('ok', '/approval/index');
    }

    public function uploadAction() {
        define('ROOT_PATH', realpath(dirname(dirname(dirname(__FILE__)))) . '/webroot_www/statics/upload/file/');

        if (!empty($_FILES)) {
            $ext = pathinfo($_FILES['Filedata']['name']);
            $ext = strtolower($ext['extension']);
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = ROOT_PATH;
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
            $photo_name = time();

            $new_file_name = $photo_name . '.' . $ext;
            $targetFile = $targetPath . $new_file_name;
            move_uploaded_file($tempFile, $targetFile);

            if (!file_exists($targetFile)) {
                $ret['result_code'] = 0;
                $ret['result_des'] = '上传失败';
            } else {
                $img = '/statics/upload/file/' . $new_file_name;

                $ret['result_code'] = 1;
                $ret['result_des'] = $img;
                $ret['result_photo_name'] = $photo_name;
            }
        } else {
            $ret['result_code'] = 100;
            $ret['result_des'] = '没有选择文件';
        }
        exit(json_encode($ret));
        exit;
    }

}
