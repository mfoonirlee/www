<?php
$z[0]=Array("field","",493,516);
$z[0][4]['name']="seotitle";
$z[1]=Array("global","",517,543);
$z[1][4]['name']="cfg_webname";
$z[2]=Array("field","",584,613);
$z[2][4]['name']="keywords";
$z[3]=Array("field","",653,711);
$z[3][4]['name']="description";
$z[3][4]['function']="html2text(@me)";
$z[4]=Array("include","",7818,7853);
$z[4][4]['filename']="head.htm";
$z[5]=Array("type","[field:typelink /]",7909,7962);
$z[5][4]['typeid']="10";
$z[6]=Array("field","(@me=='10')? @me='on':@me='';",7970,8049);
$z[6][4]['name']="typeid";
$z[6][4]['runphp']="yes";
$z[7]=Array("channel"," <a href='[field:typeurl/]'>[field:typename/]</a>\r\n    \r\n    ",8059,8222);
$z[7][4]['type']="son";
$z[7][4]['currentstyle']="<a href='~typelink~' class='on'>~typename~</a>";
$z[8]=Array("list","\r\n					  var div = document.createElement(\"div\");\r\n\r\n                div.className = \"mode popup_in\";\r\n\r\n                div.innerHTML =  \"<p class='pic'><a href='[field:arcurl/]'><img src=[field:litpic/] style='height:\"+\"auto\"+\";'/></a></p><font><span class='clearfix news-title'><a href='[field:arcurl/]'>[field:title/]</a><font class='fr'>[field:pubdate function=\"MyDate('Y-m-d',@me)\"/]</font></span></font><font><a href='[field:arcurl/]' class='news-content'>[field:description function='cn_substr(@me,200)'/]</a></font>\";\r\n\r\n                div.style.top = 0;\r\n\r\n                div.style.left = 0;\r\n\r\n                document.getElementById(\"wrapp\").appendChild(div);\r\n				",8431,9146);
$z[8][4]['pagesize']="9";
$z[9]=Array("pagelist","",9402,9473);
$z[9][4]['listitem']="info,index,end,pre,next,pageno";
$z[9][4]['listsize']="5";
$z[10]=Array("include","",9494,9532);
$z[10][4]['filename']="footer2.htm";
$z[11]=Array("global","",9571,9592);
$z[11][4]['name']="tongji";

?>