<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$textarea         = $html->textarea();
$textarea->css    = 'htmlobject_textarea';
$textarea->id     = 'id';
$textarea->style  = 'border:1px solid blue;';
$textarea->title  = 'title';
$textarea->value  = 'some text';

echo $textarea->get_string();
$html->help($textarea);
?>
