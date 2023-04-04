<?php

$qq = "";// ta的QQ号
$sendMail = "";// 发件QQ邮箱
$sendMailPwd = "";// 邮箱密码；QQ邮箱用的是授权码
$sendName = "";// 发件人名，随便写，反正是发给自己

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

function sendMail($title, $text){
    $phpMaid = new PHPMailer();
    $phpMaid->isSMTP();
    $phpMaid->CharSet = "utf8";
    $phpMaid->Host = "smtp.qq.com";
    $phpMaid->SMTPAuth = true;
    $phpMaid->Username = $sendMail;
    $phpMaid->Password = $sendMailPwd;
    $phpMaid->SMTPSecure = "ssl";
    $phpMaid->Port = 465;
    $phpMaid->setFrom($sendMail, $sendName);
    $phpMaid->addAddress($sendMail, $sendName);
    $phpMaid->addReplyTo($sendMail, $sendName);
    $phpMaid->Subject = $title;
    $phpMaid->Body = $text; 
    if (!$phpMaid->send()) {
        log_i("邮件发送失败，发送失败原因：" . $phpMaid->ErrorInfo);
    } else {
        log_i("邮件发送成功");
    }
}

$url = "https://q2.qlogo.cn/headimg_dl?dst_uin=" . $qq . "&spec=1";

if(!file_exists("./old.jpg")) {
    download("./old.jpg", $url);    
}

download("./new.jpg", $url);

if (md5_file("./old.jpg") == md5_file("./new.jpg")) {
    log_i("图片一致");
} else {
    log_i("图片不一致，替换，发送邮件");
    download("./old.jpg", $url);

    sendMail("重要邮件提醒", "ta换头像了：" . $url);
}