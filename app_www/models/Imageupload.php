<?php

class ImageuploadModel extends \Com\Model\Base {

    public function resize($ori) {
        
        $info = $this->getimageinfoarray($ori);
        if ($info) {
            //上传图片后切割的最大宽度和高度
            $width = 500;
            $height = 500;
            $scrimg = $ori;

            if ($info['type'] == 'jpg' || $info['type'] == 'jpeg') {
                $im = imagecreatefromjpeg($scrimg);
            }
            if ($info['type'] == 'gif') {
                $im = imagecreatefromgif($scrimg);
            }
            if ($info['type'] == 'png') {
                $im = imagecreatefrompng($scrimg);
            }
            if ($info['width'] <= $width && $info['height'] <= $height) {
                return;
            } else {
                if ($info['width'] > $info['height']) {
                    $height = intval($info['height'] / ($info['width'] / $width));
                } else {
                    $width = intval($info['width'] / ($info['height'] / $height));
                }
            }
            $newimg = imagecreatetruecolor($width, $height);
            imagecopyresampled($newimg, $im, 0, 0, 0, 0, $width, $height, $info['width'], $info['height']);
            imagejpeg($newimg, $scrimg);
            imagedestroy($im);
        }
        return;
    }

    public function getimageinfoarray($img) {
        $imageInfo = getimagesize($img);
        if ($imageInfo !== false) {
            $imageType = strtolower(substr(image_type_to_extension($imageInfo[2]), 1));
            $info = array(
                "width" => $imageInfo[0],
                "height" => $imageInfo[1],
                "type" => $imageType,
                "mime" => $imageInfo['mime'],
            );
            return $info;
        }else{
            return false;
        }
    }

}