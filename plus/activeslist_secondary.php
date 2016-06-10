<?php

require(dirname(__FILE__)."/../include/common.inc.php");
//$tid = $_GET['tid'];

$result = array();
$resultlist= array();
$resultlist["list"]= array();
//$resultlist["url"]="http://shop.dream-gardens.com.cn/mobile/";
//if($tid==1)
//{
	$sql = "SELECT `id`, `title`, `description`, `litpic`, `isnew`,`datestart`, `dateend`, `activitytype` ,`isactive` FROM `dede_archives` INNER JOIN `dede_addonactivity` ON `dede_archives`.`id` = `dede_addonactivity`.`aid` WHERE 1 = 1 AND `dede_archives`.`typeid` = 26 ORDER BY datestart desc LIMIT 0,9;";

    $dsql->SetQuery($sql);//将SQL查询语句格式化

    $dsql->Execute();//执行SQL操作
        
    while($row = $dsql->GetArray()){
	$result["id"] = iconv("gb2312","utf-8//IGNORE", $row['id']);
    $result["type"] = iconv("gb2312","utf-8//IGNORE", $row['activitytype']);
	$result["title"] = iconv("gb2312","utf-8//IGNORE", $row['title']);
    $result["isnew"] = iconv("gb2312","utf-8//IGNORE", $row["isnew"])=="是" ? 1 : 0;
	$result["issoldout"] = iconv("gb2312","utf-8//IGNORE", $row["isactive"])=="是" ? 1 : 0;
    $result["img"] = iconv("gb2312","utf-8", $row["litpic"]);
    $dec= iconv("gb2312","utf-8//IGNORE",$row["description"]);
    if(strlen($dec)>25)
	{
	 $result["desc"] = mb_substr( $dec, 0, 25, "utf-8" ). " " ."…"; 
	}
	else
	{
	 $result["desc"] = $dec;
	}
    //$starttime=date("Y/m/d h:i:s", $row['datestart']);
    //$endtime=date("Y/m/d h:i:s", $row['dateend']);
    //$result["date"] = $starttime . "-" .$endtime;
    array_push($resultlist["list"],$result);
}
    echo json_encode($resultlist);
//}
//else
//{
//	$sql = "SELECT  `id`, `title`,`description`,`datestart`, `dateend`,`activitytype`, `litpic` FROM `dede_archives` INNER JOIN `dede_addonactivity` ON `dede_archives`.`id` = `dede_addonactivity`.`aid` WHERE 1 = 1 AND `activitytype` in (2500,3000,3500,4000) AND datestart IN (SELECT  MAX(datestart) FROM `dede_addonactivity` WHERE 1 = 1 AND `activitytype` in (2500,3000,3500,4000) GROUP BY activitytype);";
//	
//    $dsql->SetQuery($sql);//将SQL查询语句格式化
//
//    $dsql->Execute();//执行SQL操作
//    while($row = $dsql->GetArray()){
//    $result["id"] = iconv("gb2312","utf-8//IGNORE", $row['id']);
//    $result["type"] = iconv("gb2312","utf-8//IGNORE", $row['activitytype']);
//    $result["title"] = iconv("gb2312","utf-8//IGNORE", $row['title']);
//    $starttime=date("Y/m/d h:i:s", $row['datestart']);
//    $endtime=date("Y/m/d h:i:s", $row['dateend']);
//    $result["date"] = $starttime . "-" .$endtime;
//    $result["desc"] = iconv("gb2312","utf-8//IGNORE", $row['description']);
//    $result["img"] = iconv("gb2312","utf-8", $row["litpic"]);
//    array_push($resultlist["list"],$result);
//}
//    echo json_encode($resultlist);
//}

?>
