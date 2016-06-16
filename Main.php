<?php
set_include_path('/www/web/elearning/public_html/');
include 'Controllers/getData_OMS.php';

/**
 * Chinese Time - UTF+8
 */
date_default_timezone_set("Asia/Shanghai");

/**
 * Display all the errors on the interface to help troubleshooting
 */
error_reporting(-1);
ini_set('display_errors', 'On');

/**
 * server name  username  password  database
 */
//$controller = new getData_OMS("192.168.99.15","oms_monitoring","qazwsx123","erp_cn");
/**
 * function check data
 */
//$result=$controller->mulChecked_Data();
//print_r($result);
//echo $upFile;
$downloadFile=new UpDownload();
$downloadFile->checkOrder();


//这里判断是否为空 解决了undefined file的问题  前端页面FormData 格式发送fileContent.append("message","upload")解决了undefined message问题
//并且请求头不需要改变
//if(!empty($_FILES['file']['tmp_name'])){
//    if($_FILES["file"]["error"]>0){
//        echo "Error:".$_FILES["file"]["error"]."<br />";
//    }else{
//        echo "Upload:".$_FILES["file"]["name"]."<br />";
//        echo "Type:".$_FILES["file"]["type"]."<br />";
//        echo "Size:".($_FILES["file"]["size"]/1024)."KB<br />";
//        echo "Stored in:".$_FILES["file"]["tmp_name"]."<br />";
//        if(file_exists("image/".$_FILES["file"]["name"])){
//            echo $_FILES["file"]["name"]."ready exists. ";
//        }else{
//            $filePath="image/".$_FILES["file"]["name"];
//            move_uploaded_file($_FILES["file"]["tmp_name"],$filePath);
//            //chmod($filePath,755);
//            echo "Stored in:"."image/".$_FILES["file"]["name"];
//        }
//    }
//}
//
//echo $_POST['message'];