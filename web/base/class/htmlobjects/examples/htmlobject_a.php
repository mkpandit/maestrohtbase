<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$a         = $html->a();
$a->css    = 'htmlobject_a';
$a->id     = 'id';
$a->style  = 'color:blue;';
$a->title  = 'title';
$a->handler = 'onclick="alert(\'onclick\')"';
$a->href   = 'test.html';
$a->label  = 'label';
$a->target = '_blank';

echo $a->get_string();
$html->help($a);
?>
