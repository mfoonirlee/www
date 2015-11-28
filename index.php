<?php
include('wap/mobile_device_detect.php'); 
mobile_device_detect(false,true,true,'http://shop.dream-gardens.com.cn/mobile/',false);
require_once (dirname(__FILE__) . "/include/common.inc.php");
require_once DEDEINC."/arc.partview.class.php";
$GLOBALS['_arclistEnv'] = 'index';
$row = $dsql->GetOne("Select * From `dede_homepageset`");
$row['templet'] = MfTemplet($row['templet']);
$pv = new PartView();
$pv->SetTemplet($cfg_basedir . $cfg_templets_dir . "/" . $row['templet']);
$pv->Display();
?>