<?php
require_once(dirname(__FILE__)."/../include/common.inc.php");
?>

<li style="display: <?php echo $_SESSION['_islogin'] == true ? 'none' : ''; ?>">
<a href="http://112.124.110.58:8081/user.php" class="" target='_black'>
	<div style="margin-top: 0px;">
	  <p><span>��¼</span><font>��¼</font></p>
	</div>
</a></li>
<li style="display: <?php echo $_SESSION['_islogin'] == true ? '' : 'none'; ?>">
    <a href="http://112.124.110.58:8081/user.php" class="" target='_black'>
        <div style="margin-top: 0px;">
          <p><span>��Ա����</span><font>��Ա����</font></p>
        </div>
    </a>
</li>
