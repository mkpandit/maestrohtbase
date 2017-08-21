<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');

$form         = $html->form();
$form->css    = 'htmlobject_form';
$form->id     = 'id1';
$form->style  = 'border:3px solid red;';
$form->title  = 'title';
$form->add('somestring', 'string');

$div         = $html->div();
$div->css    = 'htmlobject_div';
$div->id     = 'id2';
$div->style  = 'border:1px solid blue;';
$div->title  = 'title';
$div->add('this is div 1');
$form->add($div, 'div_1');

$div         = $html->div();
$div->css    = 'htmlobject_div';
$div->id     = 'id3';
$div->style  = 'border:1px solid blue;';
$div->title  = 'title';
$div->add('this is div 2');
$form->add($div, 'div_2');

echo $form->get_string();
echo $form->get_elements('div_1')->get_string();

$html->help($form);
?>
