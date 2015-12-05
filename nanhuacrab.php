<?php 
//echo $_SERVER[''];
echo 'PHP_SELF: '.$_SERVER['PHP_SELF'].'<br />';
echo 'SERVER_ADDR: '.$_SERVER['SERVER_ADDR'].'<br />';
echo 'SERVER_NAME: '.$_SERVER['SERVER_NAME'].'<br />';
echo 'DOCUMENT_ROOT: '.$_SERVER['DOCUMENT_ROOT'].'<br />';
echo 'HTTP_HOST: '.$_SERVER['HTTP_HOST'].'<br />';
echo 'REMOTE_ADDR: '.$_SERVER['HTTP_HOST'].'<br />';
//echo 'REMOTE_HOST: '.$_SERVER['REMOTE_HOST'].'<br />';

$info = apache_lookup_uri('index.php?var=value');
//print_r($info);
echo $info->uri.'<br />';
?>