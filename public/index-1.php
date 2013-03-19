<?php //this block of php is by me
header( 'Location: http://corona.happycollision.com' ) ;

?><?
$ch = curl_init('http://www.a2hosting.com/ad/default-page.php?domain='.$_SERVER['HTTP_HOST']);
curl_exec($ch); curl_close($ch);
?>
