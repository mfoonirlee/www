<?php

require(dirname(__FILE__)."/../include/common.inc.php");
$tid = $_GET['tid'];

$result = array();
$resultlist= array();
if($tid==1)
{
	$sql = "SELECT  `id`, `title`,`description`,`datestart`, `dateend`,`activitytype` FROM `dede_archives` INNER JOIN `dede_addonactivity` ON `dede_archives`.`id` = `dede_addonactivity`.`aid` WHERE 1 = 1 AND `activitytype` in (500,1000,1500,2000) AND datestart IN (SELECT  MAX(datestart) FROM `dede_addonactivity` WHERE 1 = 1 AND `activitytype` in (500,1000,1500,2000) GROUP BY activitytype);";

    $dsql->SetQuery($sql);//将SQL查询语句格式化

    $dsql->Execute();//执行SQL操作
    while($row = $dsql->GetArray()){
	$result["id"] = iconv("gb2312","utf-8//IGNORE", $row['id']);
    $result["type"] = iconv("gb2312","utf-8//IGNORE", $row['activitytype']);
	$result["title"] = iconv("gb2312","utf-8//IGNORE", $row['title']);
    $starttime=date("Y/m/d h:i:s", $row['datestart']);
    $endtime=date("Y/m/d h:i:s", $row['dateend']);
    $result["date"] = $starttime . "-" .$endtime;
    $result["desc"] = iconv("gb2312","utf-8//IGNORE", $row['description']);
    array_push($resultlist,$result);
}
    echo json_encode($resultlist);
}
else
{
	$sql = "SELECT  `id`, `title`,`description`,`datestart`, `dateend`,`activitytype` FROM `dede_archives` INNER JOIN `dede_addonactivity` ON `dede_archives`.`id` = `dede_addonactivity`.`aid` WHERE 1 = 1 AND `activitytype` in (2500,3000,3500,4000) AND datestart IN (SELECT  MAX(datestart) FROM `dede_addonactivity` WHERE 1 = 1 AND `activitytype` in (2500,3000,3500,4000) GROUP BY activitytype);";
	
    $dsql->SetQuery($sql);//将SQL查询语句格式化

    $dsql->Execute();//执行SQL操作
    while($row = $dsql->GetArray()){
    $result["id"] = iconv("gb2312","utf-8//IGNORE", $row['id']);
    $result["type"] = iconv("gb2312","utf-8//IGNORE", $row['activitytype']);
    $result["title"] = iconv("gb2312","utf-8//IGNORE", $row['title']);
    $starttime=date("Y/m/d h:i:s", $row['datestart']);
    $endtime=date("Y/m/d h:i:s", $row['dateend']);
    $result["date"] = $starttime . "-" .$endtime;
    $result["desc"] = iconv("gb2312","utf-8//IGNORE", $row['description']);
    array_push($resultlist,$result);
}
    echo json_encode($resultlist);
}

?>
