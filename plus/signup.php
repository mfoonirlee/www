<?php

require(dirname(__FILE__)."/../include/common.inc.php");
session_start();
$aid = $_GET['aid'];
$activedate=strtotime($_GET['activedate']);
if(isset($number))
{
	$number=$_GET['number'];
}
else
{
	$number=1;
}
$address=$_GET['address'];
$mobile=$_GET['mobile'];
$name=$_GET['name'];
$uid = $_SESSION['_id'];
if(isset($name))
{
	$uname=$name;
}
else
{
	$uname = $_SESSION['_name'];
}
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
$sql1="SELECT datestart,dateend from dede_addonactivity where aid=".$_GET["aid"].";";
$row = $dsql->GetOne($sql1);
$startdate=strtotime(date("Y-m-d",$row['datestart']));
$enddate=strtotime(date("Y-m-d",$row['dateend']));
if ( isset($activedate) && ($activedate<$startdate||$activedate>$enddate))
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
//$data=$mobile+$uname+$time;
$data="该活动您已经报名，不可重复报名";
$fileType = mb_detect_encoding($data, array('UTF-8','GBK','LATIN1','BIG5'));
if( $fileType != 'UTF-8')
{
  $data = mb_convert_encoding($data ,'utf-8' , $fileType);
}
$result["msg"]=$data;
}
else
{
$time=strtotime(date('y-m-d h:i:s',time()));
$query = "INSERT INTO `dede_activitysignup`(`activityid`,`userid`,`user_name`,`alias`,`avatar`,`SignUpDate`,`user_rank`,
	`user_rank_name`,`mobile`,`address`,`number`,`activedate`) VALUES (".$aid.", ".$uid.",'".$uname."', '".$ualias."',
	'".$uavatar."',".$time.",$rank,'$rank_name','".$mobile."', '".$address."','$number','$activedate'); ";
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
$result["url"]="http://shop.dream-gardens.com.cn/user.php";
}
echo json_encode($result);
?>
