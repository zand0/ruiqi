<?php

class ImagejcropModel extends \Com\Model\Base {

    var $filepath;  //裁切路径
    var $picname;   //需要裁切的图片路径
    var $x;         
    var $y;
    var $w;
    var $h;
    var $tw;
    var $th;
    var $name_num;  //图片大小
    var $type;  //图片类型
    var $image_name; //图片名称

    public function __construct($filepath, $picname, $x, $y, $w, $h, $tw, $th, $name_unm,$type,$image_name) {

        $this->filepath = $filepath;
        $this->picname = $picname;
        $this->x = $x;
        $this->y = $y;
        $this->w = $w;
        $this->h = $h;
        $this->tw = $tw;
        $this->th = $th;
        $this->name_unm = $name_unm;
        $this->type = $type;
        $this->image_name = $image_name;
    }

    public function crop() {

        $picname = $this->picname;
        $filepath = $this->filepath;
        $x = $this->x;
        $y = $this->y;
        $w = $this->w;
        $h = $this->h;
        $tw = $this->tw;
        $th = $this->th;
        $name_unm = $this->name_unm;
        $image_name = $this->image_name;

        //$ext = end(explode(".", $picname));
        $ext = $this->type;
        switch ($ext) {
            case "png":

                $image = @imagecreatefrompng($picname);
                break;
            case "jpeg":

                $image = @imagecreatefromjpeg($picname);
                break;
            case "jpg":

                $image = @imagecreatefromjpeg($picname);
                break;
            case "gif":

                $image = @imagecreatefromgif($picname);
                break;
        }
        
        $dst_r = ImageCreateTrueColor($tw, $th);
        $this->setTransparency($image, $dst_r, $ext);
        @imagecopyresampled($dst_r, $image, 0, 0, $x, $y, $tw, $th, $w, $h);
        @imagedestroy($image);

        //$filep= C("UPLOAD")."/".$this->nodeId.'/';
        /* $file=$filepath.time().".".$ext; */
        $file = $filepath . $image_name . '_' . $name_unm . "." . $ext;

        switch ($ext) {
            case "png":
                imagepng($dst_r, ($file != null ? $file : ''));
                break;
            case "jpeg":
                imagejpeg($dst_r, ($file ? $file : ''), 90);
                break;
            case "jpg":
                imagejpeg($dst_r, ($file ? $file : ''), 90);
                break;
            case "gif":
                imagegif($dst_r, ($file ? $file : ''));
                break;
        }

        if (file_exists($file)) {

            $returndata = array(
                "status" => '1',
                "file" => $file,
                "error" => ''
            );
        } else {
            $returndata = array(
                "status" => '0',
                "file" => '',
                "error" => '生成文件出错！'
            );
        }

        return $returndata;
        exit;
    }

    public function setTransparency($imgSrc, $imgDest, $ext) {

        if ($ext == "png" || $ext == "gif") {
            $trnprt_indx = @imagecolortransparent($imgSrc);
            // If we have a specific transparent color
            if ($trnprt_indx >= 0) {
                // Get the original image's transparent color's RGB values
                $trnprt_color = @imagecolorsforindex($imgSrc, $trnprt_indx);
                // Allocate the same color in the new image resource
                $trnprt_indx = @imagecolorallocate($imgDest, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
                // Completely fill the background of the new image with allocated color.
                @imagefill($imgDest, 0, 0, $trnprt_indx);
                // Set the background color for new image to transparent
                @imagecolortransparent($imgDest, $trnprt_indx);
            }
            // Always make a transparent background color for PNGs that don't have one allocated already
            elseif ($ext == "png") {
                // Turn off transparency blending (temporarily)
                @imagealphablending($imgDest, true);
                // Create a new transparent color for image
                $color = imagecolorallocatealpha($imgDest, 0, 0, 0, 127);
                // Completely fill the background of the new image with allocated color.
                @imagefill($imgDest, 0, 0, $color);
                // Restore transparency blending
                @imagesavealpha($imgDest, true);
            }
        }
    }

}