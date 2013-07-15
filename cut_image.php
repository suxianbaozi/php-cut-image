<?php
    class ImageCut {
            public $path = '';
            public $key = '';
            public function __construct() {
                
            }
            public function set_image($path) {
                $this->path = $path;
            }
            public function header_image(){
                header('Content-type:image/jpeg');
            }
            
            public function header_304(){
                //304缓存
                $this->key = $etag = md5_file($this->path);
                $last_mod = filemtime($this->path);
                header('ETag:'.$etag);
                header('Last-Modified:'.gmdate('D, d M Y H:i:s', $last_mod) . ' GMT');
                if ((isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $last_mod)
                    || (isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) < $last_mod)
                    || (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag)) {
                    header("HTTP/1.1 304 Not Modified");
                    return true;
                } else {
                    return false;
                }
            }
            public function get_data($width,$height){
                if($this->path) {
                    $path = $this->path;
                } else {
                    trigger_error('unset path');
                }
                if(!file_exists($path)) {
                    trigger_error('file not exist');
                }
                
                $img_type = exif_imagetype($path);
                $old_size = getimagesize($path);
                $old_width = $old_size[0];
                $old_height =  $old_size[1];
                
                if($width==0 && $height==0) {
                    $width = $old_width;
                    $height = $old_height;
                } else if($width==0) {
                    if($height>$old_height) {
                        $height = $old_height;
                    }
                    $width = $height/$old_height * $old_width;
                } else if($height==0) {
                    if($width>$old_width) {
                        $width = $old_width;
                    }
                    $height = $width/$old_width * $old_height;
                }
                
                $dst_wh = $width/$height;
                if($old_height>($old_width/$dst_wh)) {
                    $src_width = $old_width;
                    $src_height = $old_width/$dst_wh;
                    $src_x = 0;
                    $src_y = ($old_height-$src_height)/2;
                } else {
                    $src_height = $old_height;
                    $src_width = $old_height*$dst_wh;
                    $src_y = 0;
                    $src_x = ($old_width-$src_width)/2;
                }
                
                
                switch($img_type) {
                    case IMAGETYPE_JPEG:
                        $img = imagecreatefromjpeg($path);
                        break;
                    case IMAGETYPE_PNG:
                        $img = imagecreatefrompng($path);
                        break;
                    case IMAGETYPE_GIF:
                        $img = imagecreatefromgif($path);
                        break;
                }
                
                if($src_width<$width) {
                    $width = $src_width;
                }
                if($src_height<$height) {
                    $height = $src_height;
                }
                //重新画
                $newimage = imagecreatetruecolor($width,$height);
                imagecopyresampled($newimage,$img,0,0,$src_x,$src_y,$width,$height,$src_width,$src_height);
                //开始缓存图片        
                ob_start();
                imagejpeg($newimage,false,90);
                $data = ob_get_contents();
                ob_end_clean();
                imagedestroy($img); 
                imagedestroy($newimage);
                return $data;
            }
            public function show($width,$height){
                $this->header_image();
                $if_304 = $this->header_304();
                if($if_304) {
                    return;
                }
                echo $this->get_data($width, $height);
            }
        }
?>
