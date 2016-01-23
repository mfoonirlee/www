<?php

require(dirname(__FILE__)."/../include/common.inc.php");
ini_set('date.timezone', 'Asia/Shanghai');

session_start();
$aid = $_GET['aid'];
$info=$_POST['info'];
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
if($num<1)
{
$result["IsSuccess"]=0;
$result["msg"]="Comment Failed!";
}
else
{
$result["IsSuccess"]=1;
$result["msg"]="Comment Successfully!";
}
}
else
{
$result["IsSuccess"]=0;
$result["msg"]="Comment Failed!";
$result["url"]="http://112.124.110.58:8081/user.php";
}

echo json_encode($result);


?>