<?php

require(dirname(__FILE__)."/../include/common.inc.php");
session_start();
$aid = $_GET['aid'];
$activedate=$_GET['activedate'];
$number=$_GET['number'];
$address=$_GET['address'];
$mobile=$_GET['mobile'];
$uid = $_SESSION['_id'];
$uname = $_SESSION['_name'];
$rank = $_SESSION['_rankid'];
$rank_name = $_SESSION['_rankname'];
$unameType = mb_detect_encoding($uname, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $unameType != 'GBK')
{
  $uname = mb_convert_encoding($uname ,'GBK' , $unameType);
}
$ualias=$_SESSION['_alias'];
$uavatar = $_SESSION['_img'];

if (isset($uid))
{

$sql1="SELECT datestart,dateend from dede_addonactivity where aid==".$_GET["aid"].";";
$row = $dsql->GetOne($sql1);
if ($activedate<$row['datestart']||$activedate>$row['dateend'])
{
	$result["IsSuccess"]=0;
$data="不在活动日期内，请重新选择活动日期";
$fileType = mb_detect_encoding($data, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $fileType != 'UTF-8')
{
  $data = mb_convert_encoding($data ,'utf-8' , $fileType);
}
$result["msg"]=$data;
}
else
{

$sql = "select Count(1)as signupnum from dede_activitysignup WHERE activityid=".$_GET["aid"]." and userid=".$uid.";";
$row = $dsql->GetOne($sql);

if($row['signupnum']>=1)
{
$result["IsSuccess"]=0;
$data="该活动您已经报名，不可重复报名";
$fileType = mb_detect_encoding($data, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $fileType != 'UTF-8')
{
  $data = mb_convert_encoding($data ,'utf-8' , $fileType);
}
$result["msg"]=$data;
}
else
{
$query = "INSERT INTO `dede_activitysignup`(`activityid`,`userid`,`user_name`,`alias`,`avatar`,`user_rank`,`user_rank_name`,`mobile`,`address`,`number`,`activedate`) VALUES (".$aid.", ".$uid.", 
	'".$uname."', '".$ualias."', '".$uavatar."',$rank,'$rank_name','".$mobile."', '".$address."','$number','$activedate'); ";
$num= $dsql->ExecuteNoneQuery($query);
$result = array();
if($num)
{
$result["IsSuccess"]=1;
$data="报名成功";
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
$data="报名失败";
$fileType = mb_detect_encoding($data, array('UTF-8','GBK','LATIN1','BIG5')) ;
if( $fileType != 'UTF-8')
{
  $data = mb_convert_encoding($data ,'utf-8' , $fileType);
}
$result["msg"]=$data;
}
}
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
