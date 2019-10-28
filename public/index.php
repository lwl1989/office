<?php
/**
 * Created by inke.
 * User: liwenlong@inke.cn
 * Date: 2019/10/28
 * Time: 19:21
 */

class Aes
{
    public $key = 'liao_gua_gua';

    public $iv = 'I-only-love-you.';

    private $method = 'AES-128-CBC';
    //加密
    public function aesEn($data)
    {
        return base64_encode(openssl_encrypt($data, $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv));
    }

    //解密
    public function aesDe($data)
    {
        return openssl_decrypt(base64_decode($data), $this->method, $this->key, OPENSSL_RAW_DATA, $this->iv);
    }
}


function base64EncodeImage ($image_file) {
    $image_info = getimagesize($image_file);
    $image_data = getImageContent($image_file);
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}

function getImageContent($image_file) {
    return fread(fopen($image_file, 'r'), filesize($image_file));
}

if(isset($_COOKIE['love'])) {
    if(strpos($_SERVER['REQUEST_URI'], 'images' )===false) {
        echo '页面不存在，即将跳转到真爱页';
        echo '<script>
        setTimeout(function() {
                  location.href = "love.html"
        }, 2000);
        </script>';
    }
    $image = $_SERVER['DOCUMENT_ROOT'].'/..'.$_SERVER['REQUEST_URI'].'.jpeg';
    $love = $_COOKIE['love'];
    $aes = new Aes();
    $result = $aes->aesDe($love);
    if($result and strpos($result,'920922') !== false) {
        if(file_exists($image)) {

            $c_time = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])+3600*24;
            // 当大于当前时间时, 表示还在缓存中... 释放304
            if($c_time > time()){
                header('HTTP/1.1 304 Not Modified');
                exit();
            }
            header('Cache-Control: max-age=2592000');
            header("Expires: " . gmdate("D, d M Y H:i:s",time()+3600*24)." GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", filemtime($image)) . " GMT");
            header('Content-type: image/jpeg');
            echo getImageContent($image);
            exit();
        }
        header('HTTP/1.1 404 Not Found');
    }
}else{
    if(isset($_POST['birthday'])) {
        if($_POST['birthday'] == '920922') {
            $aes = new Aes();
            $value = $aes->aesEn(   '920922'.time());
            $result = setcookie('love', $value,  time()+60*60*24*30,'/','iwenjuan.com.cn',true,trues);
            header('location: /love.html');
            exit();
        }
    }
    echo '不是真爱哟，检测失败，即将跳转回去';
    echo '<script>
        setTimeout(function() {
                  location.href = "check.html"
        }, 2000);
        </script>';
}
