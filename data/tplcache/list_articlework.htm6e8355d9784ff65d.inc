<?php
$z[0]=Array("field","",501,524);
$z[0][4]['name']="seotitle";
$z[1]=Array("global","",525,551);
$z[1][4]['name']="cfg_webname";
$z[2]=Array("field","",592,621);
$z[2][4]['name']="keywords";
$z[3]=Array("field","",661,719);
$z[3][4]['name']="description";
$z[3][4]['function']="html2text(@me)";
$z[4]=Array("include","",7885,7920);
$z[4][4]['filename']="head.htm";
$z[5]=Array("field","(@me=='3')? @me='cur':@me='';",8328,8407);
$z[5][4]['name']="typeid";
$z[5][4]['runphp']="yes";
$z[6]=Array("type","[field:typelink /]",8418,8470);
$z[6][4]['typeid']="3";
$z[7]=Array("channel","<li><a href='[field:typeurl/]'>[field:typename/]</a></li>\r\n    ",8490,8666);
$z[7][4]['type']="son";
$z[7][4]['currentstyle']="<li class='cur'><a href='~typelink~' >~typename~</a></li>";
$z[8]=Array("list","\r\n\r\n                    <li>\r\n\r\n                        <a href=\"[field:arcurl/]\"> </a>\r\n\r\n                        <div class=\"just floorMain\">\r\n\r\n                        	<a href=\"#\"><img _lazysrc=\"[field:litpic2 function='GetOneImgUrl(@me,1)'/]\" /></a>\r\n\r\n                        </div>\r\n\r\n                        <div class=\"against\"><img _lazysrc=\"[field:litpic/]\"></div>\r\n\r\n                    </li>\r\n                    \r\n   ",9340,9808);
$z[8][4]['pagesize']="30";
$z[9]=Array("include","",13620,13658);
$z[9][4]['filename']="footer2.htm";
$z[10]=Array("global","",13728,13749);
$z[10][4]['name']="tongji";

?>