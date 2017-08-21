<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$img         = $html->img();
$img->css    = 'htmlobject_img';
$img->id     = 'id';
$img->style  = 'padding: 5px;';
$img->title  = 'title';
$img->handler = 'onclick="alert(\'onclick\')"';

$img->align = "top";
$img->alt = "alt";
$img->border = 1;
$img->height = 60;
$img->hspace = 20;
$img->ismap = true;
$img->longdesc = "longdesc";
$img->name = "name";
$img->src = "##";
$img->usemap = "usemap";
$img->vspace = 20;
$img->width = 60;

echo $img->get_string();
$html->help($img);
?>
