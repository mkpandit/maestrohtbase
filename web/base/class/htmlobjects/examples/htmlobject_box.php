<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$input           = $html->input();
$input->id       = 'inputid';
$input->disabled = false;
$input->name     = 'test';
$input->type     = 'text';
$input->value    = 'some value';

$box           = $html->box();
$box->css      = 'htmlobject_box';
#$box->id       = 'id';
$box->style    = 'color:blue;';
$box->title    = 'title';

$box->label     = 'Label';
#$box->label_for = $input->id;
$box->css_left  = 'css_left';
$box->css_right = 'css_right';

$box->add($input);
$box->add('some more plain text');

echo $box->get_string();

$html->help($box);
?>
