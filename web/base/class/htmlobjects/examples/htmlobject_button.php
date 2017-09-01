<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$button           = $html->button();
$button->css      = 'htmlobject_button';
$button->id       = 'id';
$button->style    = 'color:blue;';
$button->title    = 'title';
$button->handler  = 'onclick="alert(\'onclick\')"';
$button->disabled = false;
$button->label    = 'label';
$button->name     = 'test';
$button->type     = 'submit';
$button->value    = 'value';

echo $button->get_string();
$html->help($button);
?>
