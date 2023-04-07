<?php

define("QQ", "");// TA的QQ号
define("MAIL", "");// 发件QQ邮箱
define("PWD", "");// 邮箱密码；QQ邮箱用的是授权码
define("NAME", "");// 发件人名，随便写，反正是发给自己

require "./PHPMailer/PHPMailer.php";
require "./PHPMailer/Exception.php";
require "./PHPMailer/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function download($file, $url) {
    $ch = curl_init();
    $fp=fopen($file, "w");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FILE, $fp); 
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    $error = curl_error($ch);
    $size = filesize($file);
    if ($size != $info["size_download"]) {
        log_i("图片下载失败：" . $error);
    } else {
        log_i("图片下载成功");
    }
    fclose($fp);
    curl_close($ch);
}

function log_i($str) {
    echo date("Y-m-d H:i:s") . " " . $str . "\n";
}

function getImageHash($file) {
    list($width, $height) = getimagesize($file);
    $img = imagecreatefromjpeg($file);
    $new_img = imagecreatetruecolor(8, 8);
    imagecopyresampled($new_img, $img, 0, 0, 0, 0, 8, 8, $width, $height);
    imagefilter($new_img, IMG_FILTER_GRAYSCALE);
    $colors = array();
    $sum = 0;

    for ($i = 0; $i < 8; $i++) {
        for ($j = 0; $j < 8; $j++) {
            $color = imagecolorat($new_img, $i, $j) & 0xff;
            $sum += $color;
            $colors[] = $color;
        }
    }

    $avg = $sum / 64;
    $hash = '';
    $curr = '';
    $count = 0;
    foreach($colors as $color) {
        if ($color > $avg) {
            $curr .= '1';
        } else {
            $curr .= '0';
        }
        $count++;
        if (!($count % 4)) {
            $hash .= dechex(bindec($curr));
            $curr = '';
        }
    }
    return $hash;
}

function sendMail($title, $text){
    $phpMaid = new PHPMailer();
    $phpMaid->isSMTP();
    $phpMaid->CharSet = "utf8";
    $phpMaid->Host = "smtp.qq.com";
    $phpMaid->SMTPAuth = true;
    $phpMaid->Username = MAIL;
    $phpMaid->Password = PWD;
    $phpMaid->SMTPSecure = "ssl";
    $phpMaid->Port = 465;
    $phpMaid->setFrom(MAIL, NAME);
    $phpMaid->addAddress(MAIL, NAME);
    $phpMaid->addReplyTo(MAIL, NAME);
    $phpMaid->Subject = $title;
    $phpMaid->Body = $text; 
    if (!$phpMaid->send()) {
        log_i("邮件发送失败，发送失败原因：" . $phpMaid->ErrorInfo);
    } else {
        log_i("邮件发送成功");
    }
}

$url = "https://q2.qlogo.cn/headimg_dl?dst_uin=" . QQ . "&spec=1";

if(!file_exists("./old.jpg")) {
    download("./old.jpg", $url);    
}

download("./new.jpg", $url);

if (getImageHash("./old.jpg") == getImageHash("./new.jpg")) {
    log_i("图片一致");
} else {
    log_i("图片不一致，替换，发送邮件");
    download("./old.jpg", $url);

    sendMail("小丑你好", "TA换头像了：" . $url);
}
