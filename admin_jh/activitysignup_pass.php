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

/*echo $cfg_smtp_server;
echo $cfg_smtp_port;
echo $cfg_smtp_usermail;
echo $cfg_smtp_password;
echo $cfg_sendmail_bysmtp;
*/

//die;
//if($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server))
//{ 
//    $mailtype = 'TXT';
//    require_once(DEDEINC.'/mail.class.php');
//    $smtp = new smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password);
//    //$smtp->debug = false;
//    $smtp->sendmail("237375784@qq.com",$cfg_webname,$cfg_smtp_usermail, "title","mail body", $mailtype);
//}

$sql="SELECT COUNT(1) AS num FROM `#@__addonshare` WHERE aid= ".$_GET['aid'];
$row = $dsql->GetOne($sql);
if ( 0 < $row['num'] ) {
    $sql1="SELECT SUM(number) AS signupnum FROM `#@__activitysignup` WHERE Success=1 AND activityid=".$_GET['aid']." GROUP BY activityid";
    $row1=$dsql->GetOne($sql1);
    $sql2="SELECT activitynum FROM `#@__addonshare` WHERE aid= ".$_GET['aid'];
    $row2=$dsql->GetOne($sql2);
    
    //echo $row['num'];
    //echo $row1['signupnum']+$_GET['num'];
    //echo $row2['activitynum'];
    //die;
    
    if($row['num']>=1 && $row1['signupnum']+$_GET['num']>$row2['activitynum'])
    {
        echo '{ "msg": "失败,已超出分享活动的最大人数限制" }';
        die;
    }
}

$query = "UPDATE `#@__activitysignup` SET `success` = 1 WHERE `id` = ".$_GET['id'];
//echo $query;
$dsql->ExecuteNoneQuery($query);

echo '{ "msg": "成功" }';

