<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');
$html->debug();

$select          = $html->select();
$select->css     = 'htmlobject_select';
$select->id      = 'id';
$select->style   = 'color:blue;';
$select->title   = 'title';
$select->name    = 'select[]';
$select->multiple = true;

$option = $html->option();
$option->value = 'optionvalue1';
$option->label = 'option label1';
$option->style = 'color:red;';
$select->add($option);

$option = $html->option();
$option->value = 'optionvalue2';
$option->label = 'option label2';
$option->selected = true;
$content = null;
$content[0] = $option;
$select->add($content);

$content = null;
$content['arg1'] = 'arrayvalue1';
$content['arg2'] = 'array label1';
$index = array('arg1','arg2');
$select->add($content, $index);

$content = null;
$content[0][0] = 'arrayvalue2';
$content[0][1] = 'array label2';
$index = array(0,1);
$select->add($content, $index);

$select->selected = array('arrayvalue2');

echo $select->get_string();
$html->help($select);
?>
