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

$sql="SELECT COUNT(1) AS num FROM dede_addonshare WHERE aid= ".$_GET['aid'];
$row = $dsql->GetOne($sql);
$sql1="SELECT SUM(number) AS signupnum FROM dede_activitysignup WHERE Success=1 AND activityid=".$_GET['aid']." GROUP BY activityid";
$row1=$dsql->GetOne($sql1);
$sql2="SELECT activitynum FROM dede_addonshare WHERE aid= ".$_GET['aid'];
$row2=$dsql->GetOne($sql2);
echo '{ "msg": $_GET['num'] }';
echo $row2['activitynum'];
die;
if($row['num']>=1 && $row1['signupnum']+$_GET['num']>$row2['activitynum'])
{
echo '{ "msg": "失败,已超出分享活动的最大人数限制" }';
}
else
{
$query = "UPDATE `#@__activitysignup` SET `success` = 1 WHERE `id` = ".$_GET['id'];
//echo $query;
$dsql->ExecuteNoneQuery($query);

echo '{ "msg": "成功" }';
}

