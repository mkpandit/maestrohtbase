<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$div         = $html->div();
$div->css    = 'htmlobject_div';
$div->id     = 'id';
$div->style  = 'border:1px solid blue;';
$div->title  = 'title';
$div->add('content');

echo $div->get_string();
$html->help($div);
?>
