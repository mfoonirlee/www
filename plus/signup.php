<?php

require(dirname(__FILE__)."/../include/common.inc.php");
session_start();
$aid = $_GET['aid'];
$uid = $_SESSION['_id'];
$uname = $_SESSION['_name'];
$ualias=$_SESSION['_alias'];
$uavatar = $_SESSION['_img'];

if (isset($uid))
{
$query = "INSERT INTO `dede_activitysignup`(`activityid`,`userid`,`user_name`,`alias`,`avatar`) VALUES (".$aid.", ".$uid.", 
	'".$uname."', '".$ualias."', '".$uavatar."'); ";
$num= $dsql->ExecuteNoneQuery($query);
$result = array();
if($num<1)
{
$result["IsSuccess"]=0;
$result["msg"]="Sign Up Failed!";
}
else
{
$result["IsSuccess"]=1;
$result["msg"]="Sign Up Successfully!";
}
}
else
{
$result["IsSuccess"]=0;
$result["msg"]="Sign Up Failed!";
$result["url"]="http://112.124.110.58:8081/user.php";
}
echo json_encode($result);
?>
