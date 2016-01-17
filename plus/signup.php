<?php

//require(dirname(__FILE__)."/../include/common.inc.php");
//session_start();
//$aid = $_GET['aid'];
//$uid = $_SESSION['user_id'];
//$uname = $_SESSION['user_name'];
//$ualias=$_SESSION['alias'];
//$uavatar = $_SESSION['avatar'];
//
//$query = "INSERT INTO `dede_activitysignup`(`activityid`,`userid`,`user_name`,`alias`,`avatar`) VALUES (".$aid.", '".$uid."', '".$//uname."', '".$ualias."', '".$uavatar."'); ";
//echo $query;
//$dsql->ExecuteNoneQuery($query);

require(dirname(__FILE__)."/../include/common.inc.php");
session_start();
$aid = $_GET['aid'];
$uid = $_SESSION['_id'];
$uname = $_SESSION['_name'];
$uimg = $_SESSION['_img'];

$query = "INSERT INTO `dede_activitysignup`(`activityid`,`userid`,`user_name`,`avatar`) VALUES (".$aid.", '".$uid."', '".$uname."', '".$uimg."'); ";
echo $query;
//$dsql->ExecuteNoneQuery($query);

?>