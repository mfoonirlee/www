<?php
/**
 * ECSHOP 首页文件
 * ============================================================================
 * 版权所有 (C) 2005-2007 康盛创想（北京）科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com
 * ----------------------------------------------------------------------------
 * 这是一个免费开源的软件；这意味着您可以在不用于商业目的的前提下对程序代码
 * 进行修改、使用和再发布。
 * ============================================================================
 * $Author: wj $
 * $Date: 2007-10-30 15:57:55 +0800 (星期二, 30 十月 2007) $
 * $Id: index.php 13300 2007-10-30 07:57:55Z wj $
*/

define('IN_ECS', true);

require('./includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
if(empty($_REQUEST['act'])) {
	$act='diy';
}
else {
	$act=$_REQUEST['act'];
}


/*------------------------------------------------------ */
//-- 判断是否存在缓存，如果存在则调用缓存，反之读取相应内容
/*------------------------------------------------------ */
/* 缓存编号 */
$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $act . '-' . $_CFG['lang']));
if($act=='diy') {
	if(!empty($_REQUEST['warecpuid'])) {
		$warecpuid=!empty($_REQUEST['warecpuid']) ? $_REQUEST['warecpuid'] : 0;
		$showwarecpu=!empty($_REQUEST['showwarecpu']) ? explode('|@ecshop@|',$_REQUEST['showwarecpu']) : '';
		$func_cpu="<script>onclickimage_diy('M_CPU',$warecpuid,'$showwarecpu[0]','$showwarecpu[1]');</script>";
		$smarty->assign('func_cpu', $func_cpu); 
	}
	
	if(!empty($_REQUEST['showwaremainboard'])) {
		$waremainboardid=!empty($_REQUEST['waremainboardid']) ? $_REQUEST['waremainboardid'] : 0;
		$showwaremainboard=!empty($_REQUEST['showwaremainboard']) ? explode('|@ecshop@|',$_REQUEST['showwaremainboard']) : '';
		$func_mainboard="<script>onclickimage_diy('M_MainBoard',$waremainboardid,'$showwaremainboard[0]','$showwaremainboard[1]');</script>";
		$smarty->assign('func_mainboard', $func_mainboard); 
	}
	
	if(!empty($_REQUEST['showwarememory'])) {
		$warememoryid=!empty($_REQUEST['warememoryid']) ? $_REQUEST['warememoryid'] : 0;
		$showwarememory=!empty($_REQUEST['showwarememory']) ? explode('|@ecshop@|',$_REQUEST['showwarememory']) : '';
		$func_memory="<script>onclickimage_diy('M_Memory',$warememoryid,'$showwarememory[0]','$showwarememory[1]');</script>";
		$smarty->assign('func_memory', $func_memory); 
	}
	
	if(!empty($_REQUEST['showwareshowcard'])) {
		$wareshowcardid=!empty($_REQUEST['wareshowcardid']) ? $_REQUEST['wareshowcardid'] : 0;
		$showwareshowcard=!empty($_REQUEST['showwareshowcard']) ? explode('|@ecshop@|',$_REQUEST['showwareshowcard']) : '';
		$func_showcard="<script>onclickimage_diy('M_ShowCard',$wareshowcardid,'$showwareshowcard[0]','$showwareshowcard[1]');</script>";
		$smarty->assign('func_showcard', $func_showcard); 
	}
		
	//if (!$smarty->is_cached('diy.dwt', $cache_id))
	//{
		assign_template();
		/* meta information */
   		$smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    	$smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
		$smarty->assign('page_title',      'DIY装机');    // 页面标题
		$smarty->assign('ur_here',         'DIY装机');  // 当前位置
		$smarty->assign('helps',           get_shop_help());       // 网店帮助
		/* links */
    $links = index_get_links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
	//}
	$smarty->display('diy.dwt');
}
elseif($act=='xiangdao') {
	if(empty($_REQUEST['step'])) {
		$step='cpu';
	}
	else {
		$step=$_REQUEST['step'];
	}

	$warecpuid=!empty($_REQUEST['warecpuid']) ? $_REQUEST['warecpuid'] : 0;
	$showwarecpu=!empty($_REQUEST['showwarecpu']) ? htmlspecialchars(stripslashes($_REQUEST['showwarecpu'])) : '';
	$wareboxcpubd_show=!empty($_REQUEST['wareboxcpubd']) ? stripslashes(htmlspecialchars_decode($_REQUEST['wareboxcpubd'])) : "<IMG height=138  src=\"themes/jindong/images/DIY_XD_23.gif\" width=192>";
	$wareboxcpubd=!empty($_REQUEST['wareboxcpubd']) ?  htmlspecialchars(stripslashes($_REQUEST['wareboxcpubd'])) : htmlspecialchars("<IMG height=138  src=\"themes/jindong/images/DIY_XD_23.gif\" width=192>");
	
	
	$waremainboardid=!empty($_REQUEST['waremainboardid']) ? $_REQUEST['waremainboardid'] : 0;
	$showwaremainboard=!empty($_REQUEST['showwaremainboard']) ? htmlspecialchars(stripslashes($_REQUEST['showwaremainboard'])) : '';
	$wareboxmainboardbd_show=!empty($_REQUEST['wareboxmainboardbd']) ? stripslashes(htmlspecialchars_decode($_REQUEST['wareboxmainboardbd'])) : "<IMG height=138   src=themes/jindong/images/DIY_XD_25.gif width=192>";
	$wareboxmainboardbd=!empty($_REQUEST['wareboxmainboardbd']) ?  htmlspecialchars(stripslashes($_REQUEST['wareboxmainboardbd'])) : htmlspecialchars("<IMG height=138  src=\"themes/jindong/images/DIY_XD_25.gif\" width=192>");
	
	
	$warememoryid=!empty($_REQUEST['warememoryid']) ? $_REQUEST['warememoryid'] : 0;
	$showwarememory=!empty($_REQUEST['showwarememory']) ? htmlspecialchars(stripslashes($_REQUEST['showwarememory'])) : '';
	$wareboxmemorybd_show=!empty($_REQUEST['wareboxmemorybd']) ? stripslashes(htmlspecialchars_decode($_REQUEST['wareboxmemorybd']) ): "<IMG height=138  src=themes/jindong/images/DIY_XD_27.gif width=192>";
	$wareboxmemorybd=!empty($_REQUEST['wareboxmemorybd']) ?  htmlspecialchars(stripslashes($_REQUEST['wareboxmemorybd'])) : htmlspecialchars("<IMG height=138  src=\"themes/jindong/images/DIY_XD_27.gif\" width=192>");
	
	$wareshowcardid=!empty($_REQUEST['wareshowcardid']) ? $_REQUEST['wareshowcardid'] : 0;
	$showwareshowcard=!empty($_REQUEST['showwareshowcard']) ? htmlspecialchars(stripslashes($_REQUEST['showwareshowcard'])) : '';
	$wareboxshowcardbd_show=!empty($_REQUEST['wareboxshowcardbd']) ? stripslashes(htmlspecialchars_decode($_REQUEST['wareboxshowcardbd'])) : "<IMG height=138  src=themes/jindong/images/DIY_XD_29.gif width=192>";
	$wareboxshowcardbd=!empty($_REQUEST['wareboxshowcardbd']) ?  htmlspecialchars(stripslashes($_REQUEST['wareboxshowcardbd'])) : htmlspecialchars("<IMG height=138  src=\"themes/jindong/images/DIY_XD_29.gif\" width=192>");
	
	$clearmemory=!empty($_REQUEST['clearmemory']) ? $_REQUEST['clearmemory'] : 0;
	
	$smarty->assign('warecpuid',$warecpuid);
	$smarty->assign('showwarecpu',$showwarecpu);
	$smarty->assign('wareboxcpubd_show',$wareboxcpubd_show);
	$smarty->assign('wareboxcpubd',$wareboxcpubd);
	
	$smarty->assign('waremainboardid',$waremainboardid);
	$smarty->assign('showwaremainboard',$showwaremainboard);
	$smarty->assign('wareboxmainboardbd_show',$wareboxmainboardbd_show);
	$smarty->assign('wareboxmainboardbd',$wareboxmainboardbd);
	
	$smarty->assign('warememoryid',$warememoryid);
	$smarty->assign('showwarememory',$showwarememory);
	$smarty->assign('wareboxmemorybd_show',$wareboxmemorybd_show);
	$smarty->assign('wareboxmemorybd',$wareboxmemorybd);
	
	$smarty->assign('wareshowcardid',$wareshowcardid);
	$smarty->assign('showwareshowcard',$showwareshowcard);
	$smarty->assign('wareboxshowcardbd_show',$wareboxshowcardbd_show);
	$smarty->assign('wareboxshowcardbd',$wareboxshowcardbd);
	
	$smarty->assign('clearmemory',$clearmemory);
	if($step=='cpu') {
		$step1='M_CPU';
	}
	elseif($step=='mainboard') {
		$step1='M_MainBoard';
	}
	elseif($step=='memory') {
		$step1='M_Memory';
	}
	elseif($step=='showcard') {
		$step1='M_ShowCard';
	}
	
	$cat_ids=$db->getOne("select diy_cat from " . $ecs->table('diy_cat') . " where diy_code='$step1'");
	if($cat_ids) {
		$wheresql="g.cat_id IN ($cat_ids) ";
	}
	
	$pinpai_list=get_brands_cat_diy($cat_ids);

	$topre="";
	$xiangdaoimg=array();
	if($step=='cpu') {
		//$wheresql=get_children(49);	
		$titleimg="<IMG height=41 src=\"themes/jindong/images/DIY_XD_39_1.gif\" width=120>";
		$xiangdaoimg['cpu']="<img height=29 src=\"themes/jindong/images/DIY_XD_11.gif\"  width=250 />";
		$xiangdaoimg['mainboard']="<img height=29 src=\"themes/jindong/images/DIY_XD_18.gif\"  width=250 />";
		$xiangdaoimg['memory']="<img height=29 src=\"themes/jindong/images/DIY_XD_19.gif\"  width=250 />";
		$xiangdaoimg['showcard']="<img height=29 src=\"themes/jindong/images/DIY_XD_20.gif\"  width=250 />";
		
		$action='diy.php?act=xiangdao&step=mainboard';
	}
	elseif($step=='mainboard') {
	
		//$wheresql=get_children(21);
		$titleimg="<IMG height=41 src=\"themes/jindong/images/DIY_XD_39_2.gif\" width=120>";
		$xiangdaoimg['cpu']="<img height=29 src=\"themes/jindong/images/DIY_XD_17.gif\"  width=250 />";
		$xiangdaoimg['mainboard']="<img height=29 src=\"themes/jindong/images/DIY_XD_12.gif\"  width=250 />";
		$xiangdaoimg['memory']="<img height=29 src=\"themes/jindong/images/DIY_XD_19.gif\"  width=250 />";
		$xiangdaoimg['showcard']="<img height=29 src=\"themes/jindong/images/DIY_XD_20.gif\"  width=250 />";
		$topre="<a href=\"javascript:topre('cpu');\"><img src=\"themes/jindong/images/DIY_XD_51.gif\" width=\"140\" height=\"37\" border=\"0\"/></a>";
		
		$action='diy.php?act=xiangdao&step=memory';
	}
	elseif($step=='memory') {
		//$wheresql=get_children(18);
		$titleimg="<IMG height=41 src=\"themes/jindong/images/DIY_XD_39_3.gif\" width=120>";
		$xiangdaoimg['cpu']="<img height=29 src=\"themes/jindong/images/DIY_XD_17.gif\"  width=250 />";
		$xiangdaoimg['mainboard']="<img height=29 src=\"themes/jindong/images/DIY_XD_18.gif\"  width=250 />";
		$xiangdaoimg['memory']="<img height=29 src=\"themes/jindong/images/DIY_XD_13.gif\"  width=250 />";
		$xiangdaoimg['showcard']="<img height=29 src=\"themes/jindong/images/DIY_XD_20.gif\"  width=250 />";
		$topre="<a href=\"javascript:topre('mainboard');\"><img src=\"themes/jindong/images/DIY_XD_51.gif\" width=\"140\" height=\"37\" border=\"0\"/></a>";
		
		$action='diy.php?act=xiangdao&step=showcard';
	}
	elseif($step=='showcard') {
		//$wheresql=get_children(21);
		$titleimg="<IMG height=41 src=\"themes/jindong/images/DIY_XD_39_4.gif\" width=120>";
		$xiangdaoimg['cpu']="<img height=29 src=\"themes/jindong/images/DIY_XD_17.gif\"  width=250 />";
		$xiangdaoimg['mainboard']="<img height=29 src=\"themes/jindong/images/DIY_XD_18.gif\"  width=250 />";
		$xiangdaoimg['memory']="<img height=29 src=\"themes/jindong/images/DIY_XD_19.gif\"  width=250 />";
		$xiangdaoimg['showcard']="<img height=29 src=\"themes/jindong/images/DIY_XD_14.gif\"  width=250 />";
		$topre="<a href=\"javascript:topre('memory');\"><img src=\"themes/jindong/images/DIY_XD_51.gif\" width=\"140\" height=\"37\" border=\"0\"/></a>";
		
		$action='diy.php?act=diy';
	}

	$func='_' . $step;
	$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
	$size = 99999;
	if(!$smarty->is_cached('diy_xiangdao.dwt', $cache_id)) {
		if($_REQUEST['brand_id']) {
			$brand_id=$_REQUEST['brand_id'];
			if($wheresql) {
				$wheresql .=" and brand_id=$brand_id";
			}
			else {
				$wheresql=" and brand_id=$brand_id";
			}
		}
		if($_REQUEST['keyword']) {
			$keyword=$_REQUEST['keyword'];
			if($wheresql) {
				$wheresql .=" and goods_name like '%$keyword%'";
			}
			else {
				$wheresql .=" and goods_name like '%$keyword%'";
			}
		}
		
		$goods=$db->getAll("select goods_id,goods_name,shop_price,goods_thumb,goods_number from " . $ecs->table('goods') . " as g where $wheresql AND g.is_on_sale = 1 AND g.is_alone_sale = 1 and g.is_delete=0");
		$count=count($goods);
		
		$goods=$db->getAll("select goods_id,goods_name,shop_price,goods_thumb,goods_number from " . $ecs->table('goods') . " as g where $wheresql AND g.is_on_sale = 1 AND g.is_alone_sale = 1 and g.is_delete=0 limit " . ($page-1) * $size . "," . $size);
		for($i=0;$i<count($goods);$i++) {
			$goods[$i]['goods_thumb']      = empty($goods[$i]['goods_thumb']) ? $GLOBALS['_CFG']['no_picture'] : $goods[$i]['goods_thumb'];
			$goods[$i]['shop_price']      = round($goods[$i]['shop_price']);
		}
		
		assign_template();
		/* meta information */
    	$smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    	$smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
		$smarty->assign('page_title',      '装机向导');    // 页面标题
		$smarty->assign('ur_here',         '装机向导');  // 当前位置<strong></strong>
		$smarty->assign('pinpai_list',$pinpai_list);
		$smarty->assign('act',$act);
		$smarty->assign('step',$step);
		$smarty->assign('func',$func);
		$smarty->assign('action',$action);
		$smarty->assign('topre',$topre);
		$smarty->assign('titleimg',$titleimg);
		$smarty->assign('xiangdaoimg',$xiangdaoimg);
		$smarty->assign('goods_list',$goods);
		$smarty->assign('helps',           get_shop_help());       // 网店帮助
		/* links */
    $links = index_get_links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
		assign_pager('diy',$act, $count, $size, '', '', $page, '', '', '', '', '', '',$step); // 分页
	}	
	
	$smarty->display('diy_xiangdao.dwt');
}
elseif($act=='add_to_cart1') {
	$good_ids=explode(',',$_REQUEST['strwareid']);
	$good_nums=explode(',',$_REQUEST['strwarenum']);
	$user_id=$_SESSION['user_id'];
	$session_id=SESS_ID;
	$goods_ids=array();
	$goods_nums=array();
	$i=0;
	foreach($good_ids as $key=>$value) {
		if(!empty($value)) {
			$goods_ids[$i]=$value;
			$i++;
		}
	}
	$i=0;
	foreach($good_nums as $key=>$value) {
		if($value) {
			$goods_nums[$i]=$value;
			$i++;
		}
	}

	if(!count($goods_ids)) {
		show_message('请选择商品', '', '', 'error');
	}
	if(!count($goods_nums)) {
		show_message('请选择商品数量', '', '', 'error');
	}
	
	
	for($i=0;$i<count($goods_ids);$i++) {
		$sql="";
		$row_good=$db->getRow("select goods_id,goods_name,goods_sn,market_price,shop_price,is_real,extension_code,goods_number from " . $ecs->table('goods') . " where goods_id=" . $goods_ids[$i]);
		if(!is_array($row_good)) {
			echo"<script>alert('您订购的商品：" . $row_good['goods_name'] . "\\r\\n无货或不存在，请重新选择');</script>";
			echo"<script>history.go(-1);</script>";
		}
		
		if($goods_nums[$i] > $row_good['goods_number']) {
			echo"<script>alert('您订购的商品：" . $row_good['goods_name'] . "\\r\\n无货或不存在，请重新选择');</script>";
			echo"<script>history.go(-1);</script>";
		}
		//$goods_attr_id=$db->getRow("select goods_attr_id from " . $ecs->table('goods_attr') . " where goods_id=$value");
		$goods_id=$goods_ids[$i];
		$goods_sn=$row_good['goods_sn'];
		$goods_name=$row_good['goods_name'];
		$market_price=$row_good['market_price'];
		$goods_price=$row_good['shop_price'];
		$goods_number=$goods_nums[$i];
		$goods_attr='';
		$is_real=$row_good['is_real'];
		$extension_code= $row_good['extension_code'];
		$rec_type=CART_GENERAL_GOODS;
		$is_gift=0;
		$goods_attr_id=0;
		$row_yes=false;
		$row_yes=$db->getRow("select * from " . $ecs->table('cart') . " where user_id=$user_id and session_id='$session_id' and goods_id=$goods_id");
		if($row_yes==false) {
			$sql="insert into " . $ecs->table('cart') . "(user_id,session_id,goods_id,goods_sn,goods_name,market_price,goods_price,goods_number,goods_attr,is_real,extension_code,rec_type,is_gift,goods_attr_id) values($user_id,'$session_id',$goods_id,'$goods_sn','$goods_name',$market_price,$goods_price,$goods_number,'$goods_attr',$is_real,'$extension_code',$rec_type,$is_gift,$goods_attr_id)";
			$db->query($sql);
		}
		else {
			$sql="update " . $ecs->table('cart') . " set goods_number=goods_number + $goods_number where user_id=$user_id and session_id='$session_id' and goods_id=$goods_id";
			$db->query($sql);
		}	
	}
	header("Location:flow.php");
}
elseif($act=='showdata') {
	if(empty($_REQUEST['step'])) {
		$step='M_CPU';
	}
	else {
		$step=$_REQUEST['step'];
	}
	
	$cat_ids=$db->getOne("select diy_cat from " . $ecs->table('diy_cat') . " where diy_code='$step'");
	if($cat_ids) {
		$wheresql="g.cat_id IN ($cat_ids) ";
	}
	$pinpai_list=get_brands_cat_diy($cat_ids);

	$cpu=!empty($_REQUEST['cpu']) ? $_REQUEST['cpu'] : '';
	$mainboard= !empty($_REQUEST['mainboard']) ? $_REQUEST['mainboard'] : '';
	if($step=='M_CPU') {
		if($_REQUEST['mainboard']) {
			$type=$db->getOne("select cat_id from " . $ecs->table('goods_cat') . " where goods_id=" . $_REQUEST['mainboard']);
			if($type > 0) {
				$wheresql .= " and c.cat_id=$type";
				$othersql=" left join " . $ecs->table('goods_cat') . " as c on g.goods_id=c.goods_id";
			}
		}
		$typename='CPU';
		$func='onclickimage';
		
	}
	elseif($step=='M_MainBoard') {
		if($_REQUEST['cpu']) {
			$type=$db->getOne("select cat_id from " . $ecs->table('goods_cat') . " where goods_id=" . $_REQUEST['cpu']);
			if($type > 0) {
				$wheresql .= " and c.cat_id=$type";
				$othersql=" left join " . $ecs->table('goods_cat') . " as c on g.goods_id=c.goods_id";
			}
		}
		$typename='主板';
		$func='onclickimage1';
	}
	elseif($step=='M_Memory') {
		if($_REQUEST['mainboard']) {
			$type=$db->getOne("select cat_id from " . $ecs->table('goods_cat') . " where goods_id=" . $_REQUEST['mainboard']);
			if($type > 0) {
				$wheresql .= " and c.cat_id=$type";
				$othersql=" left join " . $ecs->table('goods_cat') . " as c on g.goods_id=c.goods_id";
			}
		}
		$typename='内存';
		$func='onclickimage';
	}
	elseif($step=='M_ShowCard') {
		if($_REQUEST['mainboard']) {
			$type=$db->getOne("select cat_id from " . $ecs->table('goods_cat') . " where goods_id=" . $_REQUEST['mainboard']);
			if($type > 0) {
				$wheresql .= " and c.cat_id=$type";
				$othersql=" left join " . $ecs->table('goods_cat') . " as c on g.goods_id=c.goods_id";
			}
		}
		$typename='显卡';
		$func='onclickimage';
	}
	elseif($step=='M_HD') {
		if($_REQUEST['mainboard']) {
			$type=$db->getOne("select cat_id from " . $ecs->table('goods_cat') . " where goods_id=" . $_REQUEST['mainboard']);
			if($type > 0) {
				$wheresql .= " and c.cat_id=$type";
				$othersql=" left join " . $ecs->table('goods_cat') . " as c on g.goods_id=c.goods_id";
			}
		}
		$typename='硬盘';
		$func='onclickimage';
	}
	elseif($step=='sound') {
		$typename='声卡';
		$func='onclickimage';
	}
	elseif($step=='cddriver') {
		$typename='光驱';
		$func='onclickimage';
	}
	elseif($step=='network') {
		$typename='网卡';
		$func='onclickimage';
	}
	elseif($step=='display') {
		$typename='显示器';
		$func='onclickimage';
	}
	elseif($step=='M_Box') {
		if($_REQUEST['mainboard']) {
			$type=$db->getOne("select cat_id from " . $ecs->table('goods_cat') . " where goods_id=" . $_REQUEST['mainboard']);
			if($type > 0) {
				$wheresql .= " and c.cat_id=$type";
				$othersql=" left join " . $ecs->table('goods_cat') . " as c on g.goods_id=c.goods_id";
			}
		}
		$typename='机箱';
		$func='onclickimage';
	}
	elseif($step=='powersupply') {
		$typename='电源';
		$func='onclickimage';
	}
	elseif($step=='mouse') {
		$typename='鼠标';
		$func='onclickimage';
	}
	elseif($step=='keyboard') {
		$typename='键盘';
		$func='onclickimage';
	}
	elseif($step=='keymouse') {
		$typename='键鼠套装';
		$func='onclickimage';
	}
	elseif($step=='soundbox') {
		$typename='音箱';
		$func='onclickimage';
	}
	elseif($step=='M_N1') {
		if($_REQUEST['mainboard']) {
			$type=$db->getOne("select cat_id from " . $ecs->table('goods_cat') . " where goods_id=" . $_REQUEST['mainboard']);
			if($type > 0) {
				$wheresql .= " and c.cat_id=$type";
				$othersql=" left join " . $ecs->table('goods_cat') . " as c on g.goods_id=c.goods_id";
			}
		}
		$typename='散热设备';
		$func='onclickimage';
	}
	
	if($_REQUEST['brand_id']) {
		$brand_id=$_REQUEST['brand_id'];
		if($wheresql) {
			$wheresql .=" and brand_id=$brand_id";
		}
		else {
			$wheresql=" and brand_id=$brand_id";
		}
	}
	if($_REQUEST['keyword']) {
		$keyword=$_REQUEST['keyword'];
		if($wheresql) {
			$wheresql .=" and goods_name like '%$keyword%'";
		}
		else {
			$wheresql .=" and goods_name like '%$keyword%'";
		}
	}
	$page = isset($_REQUEST['page'])   && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
	$size = 99999;
	
	$goods=$db->getAll("select g.goods_id,g.goods_name,g.shop_price,g.goods_thumb from " . $ecs->table('goods') . " as g" . $othersql . " where $wheresql AND g.is_on_sale = 1 AND g.is_alone_sale = 1 and g.is_delete=0");
	$count=count($goods);
	//if(!$smarty->is_cached('diy_goods.dwt', $cache_id)) {
		$goods=$db->getAll("select g.goods_id,g.goods_name,g.shop_price,g.goods_thumb from " . $ecs->table('goods') . " as g" . $othersql . " where $wheresql AND g.is_on_sale = 1 AND g.is_alone_sale = 1 and g.is_delete=0 limit " . ($page-1) * $size . "," . $size);
		for($i=0;$i<count($goods);$i++) {
			$goods[$i]['goods_thumb']      = empty($goods[$i]['goods_thumb']) ? $GLOBALS['_CFG']['no_picture'] : $goods[$i]['goods_thumb'];
			$goods[$i]['shop_price']      = round($goods[$i]['shop_price']);
		}
		
		/* meta information */
    	$smarty->assign('keywords',        htmlspecialchars($_CFG['shop_keywords']));
    	$smarty->assign('description',     htmlspecialchars($_CFG['shop_desc']));
		$smarty->assign('pinpai_list',$pinpai_list);
		$smarty->assign('act',$act);
		$smarty->assign('mainboard',$mainboard);
		$smarty->assign('cpu',$cpu);
		$smarty->assign('step',$step);
		$smarty->assign('typename',$typename);
		$smarty->assign('func',$func);
		$smarty->assign('goods_list',$goods);
		$smarty->assign('helps',           get_shop_help());       // 网店帮助
		/* links */
    $links = index_get_links();
    $smarty->assign('img_links',       $links['img']);
    $smarty->assign('txt_links',       $links['txt']);
	assign_pager('diy',$act, $count, $size, '', '', $page, '', '', '', '', '', '',$step,$cpu,$mainboard,$brand_id,$keyword); // 分页
	//}	
	
	$smarty->display('diy_goods.dwt');
}


/**
 * 获得所有的友情链接
 *
 * @access  private
 * @return  array
 */
function index_get_links()
{
    $sql = 'SELECT link_logo, link_name, link_url FROM ' . $GLOBALS['ecs']->table('friend_link') . ' ORDER BY show_order';
    $res = $GLOBALS['db']->getAll($sql);

    $links['img'] = $links['txt'] = array();

    foreach ($res AS $row)
    {
        if (!empty($row['link_logo']))
        {
            $links['img'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url'],
                                    'logo' => $row['link_logo']);
        }
        else
        {
            $links['txt'][] = array('name' => $row['link_name'],
                                    'url'  => $row['link_url']);
        }
    }

    return $links;
}
/*------------------------------------------------------ */
//-- PRIVATE FUNCTIONS
/*------------------------------------------------------ */
?>