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

/*echo $cfg_smtp_server;
echo $cfg_smtp_port;
echo $cfg_smtp_usermail;
echo $cfg_smtp_password;
echo $cfg_sendmail_bysmtp;
*/
////���Դ��뿪ʼ
//$content=urlencode(iconv("gb2312","utf-8//IGNORE", "�𾴵Ŀͻ�����Ķ����������ţ�321�� ����2016/03/20 10:00:00����������2016/03/20 15:00:00����������ָ����ַ�����������뼰ʱ��ϵ���ǵĿͷ�����400-821-3860��"));    
//    $phone=15921824593;//15921824593//$row['mobile_phone']
//    $url="http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=cf_Dreamgardens&password=dream@sh456&mobile=$phone&content=$content";
//    file_get_contents($url); 
//    echo '{"msg": "�ɹ�" }';
//die;
////���Դ������

$sql="SELECT A.userid, A.user_name, mobile_phone,email FROM dede_activitysignup AS A LEFT JOIN ecshoptest.ecs_users AS B ON A.userid=B.user_id WHERE A.Success!=1 AND A.activityid= ".$_GET['aid'];

//echo '{ "msg": "$sql"}' ;
//die;

$dsql->SetQuery($sql);//��SQL��ѯ����ʽ��
$dsql->Execute();//ִ��SQL����
while($row = $dsql->GetArray()){
if(isset($row['email']))
{
    if($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server))
    { 
        $mailtype = 'TXT';
        require_once(DEDEINC.'/mail.class.php');
        $smtp = new smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password);
        //$smtp->debug = false;
        $smtp->sendmail($row['email'],$cfg_webname,$cfg_smtp_usermail, "title","mail body", $mailtype);
    }
}
if(isset($row['mobile_phone']))
{   
    $content=urlencode(iconv("gb2312","utf-8//IGNORE", "�𾴵Ŀͻ�����Ķ����������ţ�654321�� ����2016/03/20 10:00:00����������2016/03/20 15:00:00����������ָ����ַ�����������뼰ʱ��ϵ���ǵĿͷ�����400-821-3860��"));    
    $phone=$row['mobile_phone'];//15921824593//$row['mobile_phone']
    $url="http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=cf_Dreamgardens&password=dream@sh456&mobile=$phone&content=$content";
    file_get_contents($url); 
    //$html = file_get_contents($url);  
    //echo $html;  
}
}
echo '{ "msg": "�ɹ�" }';

