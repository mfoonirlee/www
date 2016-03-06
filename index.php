<?php
include('wap/mobile_device_detect.php'); 
mobile_device_detect(false,true,true,'http://shop.dream-gardens.com.cn/mobile/',false);
require_once (dirname(__FILE__) . "/include/common.inc.php");

if ( isset($_GET["login"]) ) {
	session_start();
    $_SESSION['_islogin'] = true;
    $_SESSION['_id'] = $_GET["login"];
    $_SESSION['_name'] = $_GET["n"];
    $_SESSION['_img'] = $_GET["img"];
    $_SESSION['_alias'] = $_GET["alias"];    
    $_SESSION['_rankid'] = $_GET["rankid"];
    
    $rankname=$_GET["rankname"];
    $infoType = mb_detect_encoding($rankname, array('UTF-8','GBK','LATIN1','BIG5')) ;
    if( $infoType != 'GBK')
    {
      $rankname = mb_convert_encoding($rankname ,'GBK' , $infoType);
    }
    $_SESSION['_rankname'] = $rankname;

    $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid='".$_GET["login"]."'");
    if(is_array($row)){
        $inquery = "UPDATE `dede_member` SET `userid` = ".$_GET["login"].",`uname` = '".iconv("utf-8", "gb2312",$_GET["n"] )."',,`img` = '".$_GET["img"]."'; ";
        $dsql->ExecuteNoneQuery($inquery);
    }
    else {
    	$inquery = "INSERT INTO `dede_member`(`userid`,`uname`,`img`) VALUES (".$_GET["login"].", '".iconv("utf-8", "gb2312",$_GET["n"] )."', '".$_GET["img"]."'); ";
    //echo $inquery;
        $dsql->ExecuteNoneQuery($inquery);
    }
    exit;
}

if ( isset($_GET["logout"]) ) {
    session_start();
    $_SESSION['_islogin'] = false;
    exit;
}

require_once DEDEINC."/arc.partview.class.php";
$GLOBALS['_arclistEnv'] = 'index';
$row = $dsql->GetOne("Select * From `dede_homepageset`");
$row['templet'] = MfTemplet($row['templet']);
$pv = new PartView();
$pv->SetTemplet($cfg_basedir . $cfg_templets_dir . "/" . $row['templet']);
//echo $cfg_basedir . $cfg_templets_dir . "/" . $row['templet'];
$pv->Display();
?>