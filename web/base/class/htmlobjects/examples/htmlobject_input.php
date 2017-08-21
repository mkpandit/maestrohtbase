<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');
$html->debug();

$input           = $html->input();
$input->css      = 'htmlobject_input';
$input->id       = 'id';
$input->style    = 'color:blue;';
$input->title    = 'title';
$input->handler  = 'onclick="alert(\'onclick\')"';

$input->disabled = false;
$input->name     = 'test.html';
$input->type     = 'submit';
$input->value    = 'value';


$i       = $html->input();
$i->name = 'some_name';

echo $input->get_string();
echo $i->get_string();


$html->help($input);
$html->help($i);
?>
