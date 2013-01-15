<?php 
require_once 'kernel/common/template.php';

$tpl    = templateInit();
$module = $Params['Module'];

$Result[ 'content' ] = $tpl->fetch( 'design:calendar2/test_calendar1.tpl' );

?>