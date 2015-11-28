<?php
if(!isset($GLOBALS['_vars'])) $GLOBALS['_vars'] = array(); 
$fields = array();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<title>
文档管理</title>

<link rel="stylesheet" href="css/reset.css" type="text/css" media="screen" />
<link rel="stylesheet" href="css/style.css" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="css/base.css">
<script language="javascript" src="js/jquery.min.js" type="text/javascript"></script>	
<script type="text/javascript" src="js/admin.jquery.configuration.js"></script>

<script language="javascript" src="js/global.js"></script>
<script language="javascript" src="../include/js/dedeajax2.js"></script>
<script language="javascript" src="js/ieemu.js"></script>
<script language="javascript" src="js/context_menu.js"></script>
<script language="javascript" src="js/dialog.js"></script>
<script language="javascript" src="js/main.js"></script>
<script language="javascript" src="js/list.js"></script>




<style>
.coolbg2 {
border: 1px solid #000000;
background-color: #F2F5E9;
height:18px
}
.coolbt2 {
  border-left: 2px solid #EFEFEF;
  border-top: 2px solid #EFEFEF;
  border-right: 2px solid #ACACAC;
  border-bottom: 2px solid #ACACAC;
  background-color: #F7FCDA
}
.nbline {border-bottom: 1px solid #d6d6d6; background-color:#FFFFFF; }
.bline2 {border-bottom: 1px solid #d6d6d6; background-color:#F9FCEF; }
</style>
</head>
<body onLoad="ContextMenu.intializeContextMenu()">
<div id="main-content">			

	<div class="content-box"><!-- Start Content Box -->
				
				<div class="content-box-header">
					
					<h3>文档管理</h3>
					
					<ul class="content-box-tabs">

					</ul>
					
					<div class="clear"></div>
					
				</div> <!-- End .content-box-header -->
				
				<div class="content-box-content">
					
<div class="tab-content  default-tab" id="tab1">				
<!--  快速转换位置按钮  -->
<table width="98%" border="0" cellpadding="0" cellspacing="1" align="center">
<tr>
 <td height="26">
  <table width="98%" border="0" cellspacing="0" cellpadding="0">
  <tr>
  <td align="center">

  <input class="button" onclick="location='catalog_do.php?channelid=<?php echo $GLOBALS['channelid']; ?>&cid=<?php echo $GLOBALS['cid']; ?>&dopost=addArchives';" name="imageField" type="button" value="添加文档" />
  
  <?php if($adminid==$mid){ ?>
  <input class="button" onclick="location='content_list.php?cid=<?php echo $GLOBALS['cid']; ?>&mid=0';" name="imageField" type="button" value="全部文档" />

	<?php
}
else{
?>
      <input class="button" onclick="location='content_list.php?cid=<?php echo $GLOBALS['cid']; ?>&mid=<?php echo $cuserLogin->getUserID(); ?>';" name="imageField" type="button" value="全部文档" />

	<?php } ?>
	
    <input class="button" onclick="location='catalog_main.php';" name="imageField" type="button" value="栏目管理" />
<input class="button" onclick="location='makehtml_list.php?cid=<?php echo $GLOBALS['cid']; ?>';" name="imageField" type="button" value="更新列表" />
<input class="button" onclick="location='makehtml_archives.php?cid=<?php echo $GLOBALS['cid']; ?>';" name="imageField" type="button" value="更新文档" />
<input class="button" onclick="location='recycling.php?cid=<?php echo $GLOBALS['cid']; ?>';" name="imageField" type="button" value="文章回收站" />
 </td>
 </tr>
</table>
</td>
</tr>
</table>
  
<!--  内容列表   -->

							
							<form name="form2">
<table width="98%" border="0" cellpadding="2" cellspacing="1" align="center" style="margin-top:8px">
<tr>
	<td height="28" colspan="10" style="padding-left:10px;">
	◆ <?php echo $GLOBALS['positionname']; ?>文档列表 &nbsp;(使用鼠标右键弹出菜单)
	</td>
</tr>
<tr align="center" height="25">
	<td width="6%">ID</td>
	<td width="4%">选择</td>
	<td width="28%">文章标题</td>
	<td width="10%">更新时间</td>
	<td width="10%">类目</td>
	<td width="8%">点击</td>
	<td width="6%">HTML</td>
	<td width="8%">权限</td>
	<td width="8%">发布人</td>
	<td width="10%">操作</td>
</tr>
<?php
$atts = array();
$atts['tagname'] = 'datalist';
$blockValue = $this->refObj->GetArcList($atts,$this->refObj,$fields); 
if(is_array($blockValue)){
foreach( $blockValue as $key=>$fields )
{
?>
<tr align='center' height="26" align="center" >
	<td nowrap>
		<?php echo $fields['id']; ?>
	</td>
	<td>
		<input name="arcID" type="checkbox" id="arcID" value="<?php echo $fields['id']; ?>" class="np" />
	</td>
	<td align='left'>
		<span id="arc<?php echo $fields['id']; ?>">
			<a href='archives_do.php?aid=<?php echo $fields['id']; ?>&dopost=editArchives'
				 oncontextmenu="ShowMenu(event,this,<?php echo $fields['id']; ?>,'<?php echo str_replace('\'', '', $fields['title']); ?>')">
				<u><?php echo $fields['title']; ?></u>
			</a>
		</span>
		<?php echo IsCommendArchives($fields['flag']); ?>
	</td>
	<td><?php echo GetDateMk($fields['senddate']); ?></td>
	<td><a href='content_list.php?cid=<?php echo $fields['typeid']; ?>'><?php echo GetTypename($fields['typeid']); ?></a></td>
	<td><?php echo $fields['click']; ?></td>
	<td><?php echo IsHtmlArchives($fields['ismake']); ?></td>
	<td><?php echo GetRankName($fields['arcrank']); ?></td>
	<td><?php echo GetMemberName($fields['mid']); ?></td>
	<td>
		<img src='images/trun.gif' title="编辑属性" alt="编辑属性" onClick="QuickEdit(<?php echo $fields['id']; ?>, event, this);" style='cursor:pointer' border='0' width='16' height='16' />
		<img src='images/gtk-edit.png' title="编辑" alt="编辑" onClick="editArc(<?php echo $fields['id']; ?>);" style='cursor:pointer' border='0' width='16' height='16' />
		<img src='images/part-list.gif' title="预览" alt="预览" onClick="viewArc(<?php echo $fields['id']; ?>);" style='cursor:pointer' border='0' width='16' height='16' />
	</td>
</tr>
<?php
}
}?>
<tr >
<td height="36" colspan="10">
	&nbsp;
    <input class="button" onclick="javascript:selAll()" name="imageField" type="button" value="全选" />
	<input class="button" onclick="javascript:noSelAll()" name="imageField" type="button" value="取消" />
    	<input class="button" onclick="javascript:updateArc()" name="imageField" type="button" value="更新" />
	<input class="button" onclick="javascript:checkArc()" name="imageField" type="button" value="审核" />
    <input class="button" onclick="javascript:adArc()" name="imageField" type="button" value="推荐" />
	 <input class="button" onclick="moveArc(event,this,<?php echo (empty($channelid) ? 0 : $channelid); ?>)" name="imageField" type="button" value="移动" />
      <input class="button" onclick="javascript:delArc()" name="imageField" type="button" value="删除" />
       <input class="button" onclick="<?php if($channelid==1) echo 'javascript:kwArc(0)'; ?>" name="imageField" type="button" value="自动关键字" />
        <input class="button" onclick="cAtts('attsAdd',event,this)" name="imageField" type="button" value="增加属性" />
         <input class="button" onclick="cAtts('attsDel',event,this)" name="imageField" type="button" value="删除属性" />
     
</td>
</tr>
</form>
<tr align="right" bgcolor="#F9FCEF">
	<td height="36" colspan="10" align="center">
		<?php
$atts = array();
$atts['tagname'] = 'pagelist';
$atts['listsize'] = '3';
 echo $this->refObj->GetPageList($atts,$this->refObj,$fields); 
?>

	</td>
</tr>
</table>

<!--  搜索表单  -->
<form name='form3' action='content_list.php' method='get'>
<input type='hidden' name='dopost' value='listArchives' />
<table width='98%'  border='0' cellpadding='1' cellspacing='1' bgcolor='#cfcfcf' align="center" style="margin-top:8px">
  <tr bgcolor='#EEF4EA'>
    <td align='center'>
      <table border='0' cellpadding='0' cellspacing='0' height="32">
        <tr>
          <td width='160'>
          <select name='cid' style='width:150px'>
          <option value='0'>选择栏目...</option>
          	<?php echo $GLOBALS['optionarr']; ?>
          </select>
        </td >
        <td nowrap>
          关键字：
          	<input type='text' name='keyword' value='<?php echo $GLOBALS['keyword']; ?>' style='width:120px' />
    		<select name='orderby' style='width:80px'>
            <option value='id'>排序</option>
            <option value='pubdate'>更新时间</option>
            <option value='sortrank'>置顶权值</option>
            <option value='click'>点击量</option>
            <option value='scores'>评论积分</option>
            <option value='lastpost'>最后评论</option>
      	</select>
    		<select name='flag' style='width:80px'>
            <option value=''>属性</option>
            <?php echo $GLOBALS['flagsArr']; ?>
      	</select>
        <input class="button" name="imageField" type="submit" value="搜索" />

       </td>
      </tr>
     </table>
   </td>
  </tr>
</table>
</form>
							
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