<?php

require(dirname(__FILE__)."/../include/common.inc.php");
ini_set('date.timezone', 'Asia/Shanghai');

session_start();
$aid = $_GET['aid'];
$postdata = file_get_contents("php://input");
$info= substr($postdata,5);

$info = urldecode($info);

$infoType = mb_detect_encoding($info, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $infoType != 'GBK')
{
  $info = mb_convert_encoding($info ,'GBK' , $infoType);
}

$uid = $_SESSION['_id'];
$uname = $_SESSION['_name'];
$ualias=$_SESSION['_alias'];
$uavatar = $_SESSION['_img'];
$lve=$_SESSION['_rankid'];
$time=strtotime(date('y-m-d h:i:s',time()));

if (isset($uid))
{
$query = "INSERT INTO `dede_feedback`(`aid`,`mid`,`username`,`alias`,`avatar`,`dtime`,`msg`,`userlevel`) VALUES (".$aid.", ".$uid.", 
	'".$uname."', '".$ualias."', '".$uavatar."',".$time.",'".$info."',".$lve."); ";
 $num= $dsql->ExecuteNoneQuery($query);
$result = array();
if($num)
{
$result["IsSuccess"]=1;
$data="评论成功";
$fileType = mb_detect_encoding($data, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $fileType != 'UTF-8')
{
  $data = mb_convert_encoding($data ,'utf-8' , $fileType);
}
$result["msg"]=$data;
}
else
{
$result["IsSuccess"]=0;
$data="评论失败";
$fileType = mb_detect_encoding($data, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $fileType != 'UTF-8')
{
  $data = mb_convert_encoding($data ,'utf-8' , $fileType);
}
$result["msg"]=$data;
}
}
else
{
$result["IsSuccess"]=0;
$data="尚未登陆,登陆页面跳转中...";
$fileType = mb_detect_encoding($data, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $fileType != 'UTF-8')
{
  $data = mb_convert_encoding($data ,'utf-8' , $fileType);
}
$result["msg"]=$data;
$result["url"]="http://112.124.110.58:8081/user.php";
}

echo json_encode($result);
?>