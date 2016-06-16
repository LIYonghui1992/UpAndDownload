<?php
include 'DB_Manager/MySql_Manager.php';
/**
 * Chinese Time - UTF+8
 */
date_default_timezone_set("Asia/Shanghai");
header("content-type:text/html;charset=utf-8");
/**
 * Display all the errors on the interface to help troubleshooting
 */
error_reporting(-1);

ini_set('display_errors', 'On');


/**
 * Class getData_OMS  getData according to SQL sentence
 */
class getData_OMS{

    /**
     * @var objective database name
     */
    private $db_name;

    /**
     * @var the information of Db Server
     */
    private $server_name;
    private $username;
    private $password;
    /**
     * @var verify information
     */
    private $pass;
    private $params="decathlon";
    private $admin_certificate="yonghui.li@decathlon.com";
    private $admin_Ip="180.169.58.154";

    /**
     * @var the object of the session
     */
    private $manager;

    /**
     * getData_OMS constructor.  construct function
     * @param $server_name     server name
     * @param $username        user name
     * @param $password        password
     * @param $db_name         database name
     * @return getData_OMS     object
     */
    public function getData_OMS($server_name, $username, $password,$db_name)
    {
        $this->server_name = $server_name;
        $this->username = $username;
        $this->password = $password;
        $this->db_name = $db_name;
    }

    /**
     * Multiple line data
     */
    public function mulChecked_Data(){
        $this->manager = new MySql_Manager($this->server_name,$this->username,$this->password,$this->db_name);
        $this->pass=$this->get_Password($this->params,$this->admin_certificate);
        if (isset($_POST['message'])) {
            if(isset($_POST['password'])){
                $str=$_POST['password'];
                if($_SERVER['REMOTE_ADDR']=='180.169.58.154'){
                    if($str==$this->pass){
                        if($_POST['message']=='check_inorder'){
                            return $this->check_Inorder();
                        }else if($_POST['message']=='inventory_apply'){
                            return $this->check_Inventory_Apply();
                        }else if($_POST['message']=='syn_barcode') {
                            return $this->syn_Barcode();
                        }else if($_POST['message']=='check_order_buffer') {
                            return $this->check_Order_Buffer();
                        }else if($_POST['message']=='check_order_processed'){
                            return $this->check_Order_Processed();
                        }
                    }
                }else{
                    echo "Sorry, inssufficient permission!";
                }
            }else{
                echo "Sorry, inssufficient permission!";
            }
        }else{
            echo "Sorry, inssufficient permission!";
        }
        $this->manager->closeConnection();
    }

    /**
     * 1.Inorder generate status
     * Check inorder generate status （during yesterday to last weekend）function
     */
    private function check_Inorder(){
        $data = $this->manager->query_Mul("SELECT iso_bn,from_unixtime(create_time) FROM sdb_taoguaniostockorder_iso WHERE confirm='N' AND create_time BETWEEN
UNIX_TIMESTAMP(CURRENT_TIMESTAMP)-3600*24*7 AND UNIX_TIMESTAMP(CURRENT_TIMESTAMP)-3600*24*1");
        return json_encode($data);
    }

    /**
     * 6.After Sales confirmation status
     * Sales confirmation status（during yesterday to last weekend） function
     */
    private function check_Inventory_Apply(){
        $data= $this->manager->query_Mul("SELECT inventory_apply_bn,from_unixtime(inventory_date) FROM sdb_console_inventory_apply 
WHERE status='unconfirmed' AND inventory_date BETWEEN UNIX_TIMESTAMP(CURRENT_TIMESTAMP)-3600*24*7 AND UNIX_TIMESTAMP(CURRENT_TIMESTAMP)-3600*24*1");
        return json_encode($data);
    }
    /**
     * 8.Synchronization barcode
     * Synchronization barcode function
     */
    private function syn_Barcode(){
        $data = $this->manager->query_Mul("SELECT inner_product_id,sync_status FROM sdb_console_foreign_sku WHERE sync_status='1'");
        return json_encode($data);
    }

    /**
     * 11. Order buffer and orders to be processed
     * Check order buffer function
     */
    private function check_Order_Buffer(){
        $data=$this->manager->query("SELECT count(*) AS amount FROM sdb_ome_orders WHERE abnormal = 'false' AND ship_status = '0' AND is_fail = 'false' AND process_status IN ('unconfirmed','is_retrial') AND status = 'active' AND is_auto ='false' AND op_id IS NULL AND group_id IS NULL AND archive = '0'");
        return json_encode($data);
    }

    /**
     * 11. Order buffer and orders to be processed
     * Check orders to be processed
     */
    private function check_Order_Processed(){
        $data=$this->manager->query("SELECT count(*) AS amount FROM sdb_ome_orders WHERE group_id = 2 AND (op_id is null or op_id = 0) AND abnormal = 'false' AND is_fail = 'false' AND process_status IN('unconfirmed','confirmed','splitting','remain_cancel') AND archive = '0'");
        return json_encode($data);
    }
    /**
     * Get password for verification
     * @param $params
     * @param $ip
     * @return string
     */
    private function get_Password($params,$ip){
        return $str= strtoupper(md5(strtoupper(md5(self::encryption($params))).$ip));
    }

    /**
     * encryption
     * @param $params
     * @return null|string
     */
    private function encryption($params){
        if(!is_array($params))  return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach($params AS $key=>$val){
            if(is_null($val))   continue;
            if(is_bool($val))   $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? self::encryption($val) : $val);
        }
        return $sign;
    }

}



class UpDownload{
    function UpDownload(){

    }
    function checkOrder(){
        if(isset($_POST['message'])){
            if($_POST['message']=='getList'){
                $this->getList();
            }else if($_POST['message']=='upload'){
                $this->uploadFile();
            }
        }else if(isset($_GET['temp'])){
            $file_name=$_GET['temp'];
            //header 语句前不能有输出，不然会产生文件头无法修改错误Cannot modify header information
//            echo "file name".$file_name."<br/>";
            //用以解决中文不能显示出来的问题
            $file_name=iconv("utf-8","gb2312",$file_name);
            $file_dir=$_SERVER['DOCUMENT_ROOT']."/image/";
//            echo "file_dir:".$file_dir."<br/>";
            $this->getDownloadFile($file_dir,$file_name);
        }else{
            echo "没有收到请求 ";
        }
    }
    //先创建一个列表，并且提供一个连接
    //打印的时候不仅要打印出列表 还要打印出连接

    function getList(){
        $img_path=$_SERVER['DOCUMENT_ROOT']."/image/";
        $arr=scandir("image");
        echo "<table border='1' cellpadding='0' cellspacing='0' align='center'><tr bgcolor='#33ff33'><td>序号
        </td><td>文件名</td><td>文件类型</td></tr>";
        $i=0;

        foreach($arr as $temp){
            if($temp=="."||$temp==".."){
                continue;
            }
            $extend = explode ( ".",$temp );
            $va = count ( $extend )-1;
            echo "<tr>";
            echo "<td>".$i."</td><td><a href='Main.php?temp=".$temp."'>".$temp."</a></td>";
//            echo "<td><img src='".$img_path.$temp."'/>";
            echo "<td>".$extend [ $va ]."</td>";
            echo "</tr>";
            $i++;
        }
        echo "</table>";
    }


    // 下载文件功能
    /*
    $file_name------文件名
    $file_dir-------文件的绝对路径
    $_SERVER['DOCUMENT_ROOT']-----服务器跟目标
    down------自定义下载文件的文件夹
    获取文件在文件夹里面的位置
    必须是绝对路径
    Content-Type: application/force-download  强制浏览器下载
*/
    function getDownloadFile($file_dir,$file_name)
    {
        $file_path=$file_dir.$file_name;
        if (!file_exists($file_path)) {
            echo "<li>Sorry, no resource now!</li>";
            return;
        }
//        $file = fopen($file_path, "r");
        $file_size=filesize($file_path);
        if(is_file($file_path)){
            header("Content-Type: application/force-download");
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=".basename($file_path));
            readfile($file_path);
            exit;
        }else{
            echo "文件不存在！";
            exit;
        }

//
//        //下载文件需要用到的头
//        Header("Content-type:application/octet-stream");
//        Header("Accept-Ranges:bytes");
//        Header("Accept-Length:".$file_size);
//        Header("Content-Disposition:attachment;filename=" . $file_name);
//        $buffer=1024;
//        $file_count=0;
//        //向浏览器回传数据
//        while(!feof($file)&&$file_count<$file_size){
//            $file_con=fread($file,$buffer);
//            $file_count+=$buffer;
//            echo $file_con;
//        }
//        //下面这是一次全读取完再返回，消耗服务器内容，降低性能
////        echo fread($file, filesize($file_dir . $file_name));
//        fclose($file);
////        echo "<script>alert('下载完成');window.location.href='savedFolder.php';</script>";
//        exit();
    }
//上传文件功能
    function uploadFile()
    {
        //判断是否有文件上传，在数据入库时我们要非常严密的过滤和数据规则
        if (!empty($_FILES['file']['tmp_name'])) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Error:" . $_FILES["file"]["error"] . "<br />";
            } else {
                echo "Upload:" . $_FILES["file"]["name"] . "<br />";
                echo "Type:" . $_FILES["file"]["type"] . "<br />";
                echo "Size:" . ($_FILES["file"]["size"] / 1024) . "KB<br />";
                echo "Stored in:" . $_FILES["file"]["tmp_name"] . "<br />";
                if (file_exists("image/" . $_FILES["file"]["name"])) {
                    echo $_FILES["file"]["name"] . "ready exists. ";
                } else {
                    $filePath = "image/" . $_FILES["file"]["name"];
                    move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
                    //chmod($filePath,755);
                    echo "Stored in:" . "image/" . $_FILES["file"]["name"];
                }
            }
        }
    }

}