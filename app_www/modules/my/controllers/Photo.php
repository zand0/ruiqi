<?php

/**
 * Description of Home
 *
 */
class PhotoController extends Com\Controller\My\Idauth {

    public function indexAction() {
        $user_id = $this->cuid;
        $this->_view->assign('user_id',$user_id);
        
        $kehuM = LibF::M('kehu');
        $userRow = $kehuM->find($user_id);
        $this->_view->assign('userRow',$userRow);
    }

    public function uploadAction() {
        $imageUploadModel = new ImageuploadModel();
        define('ROOT_PATH', realpath(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/webroot_www/statics/upload/photo/');
        
        if (!empty($_FILES)) {
            $uid = intval($_REQUEST['uid']);
            $ext = pathinfo($_FILES['Filedata']['name']);
            $ext = strtolower($ext['extension']);
            $tempFile = $_FILES['Filedata']['tmp_name'];
            $targetPath = ROOT_PATH;
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }
            $photo_name = $this->cuid.time();
            
            $new_file_name = $photo_name. '.' . $ext;
            $targetFile = $targetPath . $new_file_name;
            move_uploaded_file($tempFile, $targetFile);
            
            if (!file_exists($targetFile)) {
                $ret['result_code'] = 0;
                $ret['result_des'] = '上传失败';
            }else {
                $img = '/statics/upload/photo/'.$new_file_name;
                        
                $imageUploadModel->resize($targetFile);
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

    public function resizeAction() {
        define('ROOT_PATH', realpath(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/webroot_www');
        
        $imageUploadModel = new ImageuploadModel();
        if (!$image = $this->getRequest()->getPost("img")) {
            $ret['result_code'] = 101;
            $ret['result_des'] = "图片不存在";
        } else {
            $image = ROOT_PATH . $image;
            $info = $imageUploadModel->getimageinfoarray($image);
            
            if (!$info) {
                $ret['result_code'] = 102;
                $ret['result_des'] = "图片不存在";
            } else {
                $x = $this->getRequest()->getPost("x");
                $y = $this->getRequest()->getPost("y");
                $w = $this->getRequest()->getPost("w");
                $h = $this->getRequest()->getPost("h");
                $photo_name = $this->getRequest()->getPost('photo_name');
                $width = $srcWidth = $info['width'];
                $height = $srcHeight = $info['height'];
                $type = empty($type) ? $info['type'] : $type;
                $type = strtolower($type);
                unset($info);
                
                $filep = ROOT_PATH.'/statics/upload/photo/';
                $crop1 = new ImagejcropModel($filep, $image, $x, $y, $w, $h, 200, 200, 200,$type,$photo_name);
                $thumbname01 = $crop1->crop();
                $crop2 = new ImagejcropModel($filep, $image, $x, $y, $w, $h, 130, 130, 130,$type,$photo_name);
                $thumbname02 = $crop2->crop();
                $crop3 = new ImagejcropModel($filep, $image, $x, $y, $w, $h, 112, 112, 112,$type,$photo_name);
                $thumbname03 = $crop3->crop();
                $ret['result_code'] = 1;
                $ret['result_des'] = array(
                    "big" => str_replace(ROOT_PATH, "", $thumbname01['file']),
                    "middle" => str_replace(ROOT_PATH, "", $thumbname02['file']),
                    "small" => str_replace(ROOT_PATH, "", $thumbname03['file'])
                );
                
                $user_id = $this->cuid;
                $d['photo'] = $photo_name.'_200.'.$type;
                LibF::M('kehu')->where(array('kid'=>$user_id))->save($d);
            }
        }
        echo json_encode($ret);
        exit();
    }

}