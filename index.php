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
    $base64_image = '';
    $image_info = getimagesize($image_file);
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
    return $base64_image;
}
if(isset($_COOKIE['love'])) {
    $image = $_GET['path'];
    $love = $_COOKIE['love'];

    $aes = new Aes();
    $result = $aes->aesDe($love);
    if($result and strpos($result,'920922') !== false) {
        if(file_exists($image)) {
            echo base64EncodeImage($image);
            exit();
        }
        header('HTTP/1.1 404 Not Found');
    }
}else{
    if(isset($_POST['birthday'])) {
        if($_POST['birthday'] == '920922') {
            $aes = new Aes();
            $value = $aes->aesDe('920922'.time());
            setcookie('love', $value, time()+3600);
        }
    }else{
        header('HTTP/1.1 404 Not Found');
    }
}
