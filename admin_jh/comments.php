<?php
/**
 * �����̨��ҳ
 *
 * @version        $Id: index.php 1 11:06 2010��7��13��Z tianya $
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

//��ʼ��
$dlist = new DataListCP();
$dlist->pageSize = 30;

//GET����
//$dlist->SetParameter('dopost', 'listArchives');
//$dlist->SetParameter('keyword', $keyword);
//if(!empty($mid)) $dlist->SetParameter('mid', $mid);
//$dlist->SetParameter('cid', $cid);
//$dlist->SetParameter('flag', $flag);
//$dlist->SetParameter('orderby', $orderby);
//$dlist->SetParameter('arcrank', $arcrank);
//$dlist->SetParameter('channelid', $channelid);
//$dlist->SetParameter('f', $f);

//ģ��
if(empty($s_tmplets)) $s_tmplets = 'templets/comment_list.htm';
//echo $s_tmplets;
$dlist->SetTemplate(DEDEADMIN.'/'.$s_tmplets);

//��ѯ
$dlist->SetSource($query);

//��ʾ
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();