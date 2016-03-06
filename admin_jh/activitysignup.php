<?php
/**
 * 管理后台首页
 *
 * @version        $Id: index.php 1 11:06 2010年7月13日Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC.'/typelink.class.php');
require_once(DEDEINC.'/datalistcp.class.php');
require_once(DEDEADMIN.'/inc/inc_list_functions.php');

//$activityid = $_GET['aid'];
//$activityid = 125;
//WHERE `activityid` = $activityid 

$query = "SELECT `dede_activitysignup`.`id`,`activityid`,`user_name`,`title`, `user_rank`, `user_rank_name`
FROM `#@__activitysignup` INNER JOIN `#@__archives` ON `dede_activitysignup`.`activityid` = `dede_archives`.`id`
ORDER BY `activityid`, `success` DESC, `user_rank` DESC";

//echo $query;

//初始化
$dlist = new DataListCP();
$dlist->pageSize = 30;

//GET参数
//$dlist->SetParameter('dopost', 'listArchives');
//$dlist->SetParameter('keyword', $keyword);
//if(!empty($mid)) $dlist->SetParameter('mid', $mid);
//$dlist->SetParameter('cid', $cid);
//$dlist->SetParameter('flag', $flag);
//$dlist->SetParameter('orderby', $orderby);
//$dlist->SetParameter('arcrank', $arcrank);
//$dlist->SetParameter('channelid', $channelid);
//$dlist->SetParameter('f', $f);

//模板
if(empty($s_tmplets)) $s_tmplets = 'templets/activitysignup_list.htm';
//echo $s_tmplets;
$dlist->SetTemplate(DEDEADMIN.'/'.$s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();


