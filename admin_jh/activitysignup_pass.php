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

$query = "UPDATE `#@__activitysignup` SET `success` = 1 WHERE `id` = ".$_GET['id'];
//echo $query;
$dsql->ExecuteNoneQuery($query);

echo '{ "msg": "�ɹ�" }';