<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>单独页面管理</title>

<link rel="stylesheet" href="css/reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="css/base.css">
<script language="javascript" src="js/jquery.min.js" type="text/javascript"></script>	
<script type="text/javascript" src="js/admin.jquery.configuration.js"></script>

<script language="javascript" src="js/global.js"></script>
<script language="javascript" src="../include/js/dedeajax2.js"></script>

</head>
<body>

<div id="main-content">
<script language="javascript">
$("#channel").addClass("current");$("#templets_one").addClass("current");</script>				

	<div class="content-box"><!-- Start Content Box -->
				
				<div class="content-box-header">
					
					<h3>单独页面管理</h3>
					
					<ul class="content-box-tabs">

					</ul>
					
					<div class="clear"></div>
					
				</div> <!-- End .content-box-header -->
				
				<div class="content-box-content">
					
<div class="tab-content  default-tab" id="tab1">				
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#D6D6D6" style="margin-bottom:6px">
  <tr>
   <td colspan="6" style="padding-left:10px;">
  <form action='templets_one.php' name='sss'>

  <ul>
  关键字：
  <input type='text' name='keyword' value="<?php if(!empty($keyword)) $keyword = ''; ?>" style='width:160px' />
  标识：
  <?php
     $likeid = (!isset($likeid) ? '' : $likeid);
     echo "<select name='likeid' id='likeid'>\r\n";
     $dsql->Execute("s","Select likeid From `#@__sgpage` group by likeid ");
     echo "<option value=''>不限</option>\r\n";
     while($arr = $dsql->GetArray('s'))
     {
        if($likeid==$arr['likeid']) echo "<option value='{$arr['likeid']}' selected>{$arr['likeid']}</option>\r\n";
        else echo "<option value='{$arr['likeid']}'>{$arr['likeid']}</option>\r\n";
     }
     echo "</select>\r\n";
  ?>

  <input class="button" name="imageField" type="submit" value="搜索" />
  </ul>
  </form>
	 </td>
</table>
<table width="98%" border="0" align="center" cellpadding="3" cellspacing="1" bgcolor="#cfcfcf">
  <tr>
   <td height="28" background="images/tbg.gif" colspan="7" style="padding-left:10px;">
    <div style="float:left">
    <b>◆单独页面管理</b>
    </div>
    <div style="float:right">
     [<a href="templets_one_add.php"><u>增加一个页面</u></a>]
     &nbsp;[<a href="#" onClick="document.form1.submit();"><u>更新选中页面</u></a>]
     &nbsp;[<a href="templets_one_edit.php?dopost=mkall"><u>更新所有页面</u></a>]
     &nbsp;&nbsp;&nbsp;&nbsp;
    </div>
	 </td>
  </tr>
  <tr align="center" bgcolor="#FBFCE2" height="24"> 
      <td width="6%">选择</td>
      <td width="6%">编号</td>
      <td width="30%">页面名称</td>
      <td width="11%">需编译</td>
      <td width="10%">标识</td>
      <td width="12%">修改时间</td>
      <td width="21%">管理</td>
   </tr>
   <form name='form1' action='templets_one_edit.php'>
   	<input type='hidden' name='dopost' value='mksel' />
   <?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{
?>
    <tr align="center" bgcolor="#FFFFFF" height="24" onMouseMove="javascript:this.bgColor='#FCFDEE';" 
    	onMouseOut="javascript:this.bgColor='#FFFFFF';"> 
    	  <td><input type='checkbox' name='ids[]' value='<?php echo $fields['aid']; ?>' class='np' ></td>
        <td><?php echo $fields['aid']; ?></td>
        <td><a href='templets_one_edit.php?aid=<?php echo $fields['aid']; ?>&dopost=edit'><?php echo $fields['title']; ?></a></td>
        <td><?php echo GetIsMake($fields['ismake']); ?></td>
        <td><?php echo $fields['likeid']; ?></td>
        <td><?php echo GetDateMk($fields['uptime']); ?></td>
        <td>
          	<a href='templets_one_edit.php?aid=<?php echo $fields['aid']; ?>&dopost=edit'>[更改]</a>
		        <a href="templets_one_edit.php?aid=<?php echo $fields['aid']; ?>&dopost=view" target='_blank'>[预览]</a>
		        &nbsp;|&nbsp;
		        <a href='templets_one_edit.php?aid=<?php echo $fields['aid']; ?>&dopost=delete'>[删除]</a>
		    </td>
     </tr>
		<?php
}
}?>
	   </form>
      <tr align="center" bgcolor="#F9FCEF" height="24"> 
       <td colspan="7">
      	<?php
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listsize'] = '6';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>

      </td>
     </tr>
   </table>
   </td>
</tr>
</table>
							<div class="clear"></div><!-- End .clear -->
							
						
						
					</div> <!-- End #tab1 -->

					
				</div> <!-- End .content-box-content -->
				
			</div> <!-- End .content-box -->
			
			<div class="clear"></div>
			
           <?php include(DEDEADMIN.'/templets/footer.htm'); ?>
			
		</div> <!-- End #main-content -->	
	</div>
</body>
</html>