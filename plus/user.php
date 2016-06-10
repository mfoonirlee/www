<?php
require_once(dirname(__FILE__)."/../include/common.inc.php");
?>

<li style="display: <?php echo $_SESSION['_islogin'] == true ? 'none' : ''; ?>">
<a href="http://shop.dream-gardens.com.cn/user.php" class="register" target='_black'>
	<div style="margin-top: 0px;">
	  <p><span>登录</span>register</p>
	</div>
</a></li>
<li style="display: <?php echo $_SESSION['_islogin'] == true ? '' : 'none'; ?>">
    <a href="http://shop.dream-gardens.com.cn/user.php" class="userconter" target='_black'>
        <div style="margin-top: 0px;">
          <p><span>会员中心</span><font>会员中心</font></p>
        </div>
    </a>
</li>
<script type="text/javascript">
    $("a.register, a.userconter").hover(function() {	//On hover...
		$(this).find("p").stop().animate({
			marginTop: "-20" //Find the span tag and move it up 40 pixels
		}, 200);
	} , function() { //On hover out...
		$(this).find("p").stop().animate({
			marginTop: "0" //Move the span back to its original state (0px)
		}, 200);
	});
</script>