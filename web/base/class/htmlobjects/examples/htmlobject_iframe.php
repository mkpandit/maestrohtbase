<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$iframe         = $html->iframe();
$iframe->css    = 'htmlobject_iframe';
$iframe->id     = 'id';
$iframe->style  = 'padding: 5px;';
$iframe->title  = 'title';
$iframe->handler = 'onclick="alert(\'onclick\')"';

$iframe->align = "top";
$iframe->frameborder = 1;
$iframe->height = 250;
$iframe->longdesc = "longdesc";
$iframe->marginwidth = 10;
$iframe->marginheight = 10;
$iframe->name = "name";
$iframe->scrolling = "auto";
#$iframe->src = "#";
$iframe->width = 250;

$iframe->add('Your Browser does not support iframes');

echo $iframe->get_string();
$html->help($iframe);
?>
