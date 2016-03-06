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

$query = "SELECT `dede_feedback`.`id`,`aid`,`username`,`msg`, `dtime`, `title`
FROM `#@__feedback` INNER JOIN `yhctest`.`#@__archives` ON `dede_feedback`.`aid` = `dede_archives`.`id`
WHERE `ischeck` = 0 
ORDER BY `dtime` DESC ";
//$dsql->SetQuery("SELECT `dede_feedback`.`id`,`aid`,`username`,`msg`, `dtime`, `title` FROM `#@__feedback` INNER JOIN `yhctest`.`#@__archives` ON `dede_feedback`.`aid` = `dede_archives`.`id` WHERE `ischeck` = 0 ORDER BY `dtime` DESC ");
//$dsql->Execute();
//while($row = $dsql->GetArray())
//    if($row['type']=='number')

// $dsql->ExecuteNoneQuery("UPDATE `#@__sysconfig` SET `value`='{$hash}' WHERE varname='cfg_cookie_encode' ");

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
if(empty($s_tmplets)) $s_tmplets = 'templets/comment_list.htm';
//echo $s_tmplets;
$dlist->SetTemplate(DEDEADMIN.'/'.$s_tmplets);

//查询
$dlist->SetSource($query);

//显示
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();