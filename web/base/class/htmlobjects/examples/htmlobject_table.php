<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');
$html->debug();

$table         = $html->table();
$table->css    = 'htmlobject_table';
$table->id     = 'id';
$table->style  = 'border:1px solid blue;';
$table->title  = 'title';

$table->border = 1;

$td         = $html->td();
$td->css    = 'htmlobject_td';
$td->id     = 'id';
$td->style  = 'border:1px solid blue;';
$td->title  = 'title';
$td->add('test');

$tr         = $html->tr();
$tr->css    = 'htmlobject_tr';
$tr->id     = 'id';
$tr->style  = 'border:1px solid red;';
$tr->title  = 'title';
$tr->add($td);

$table->add($tr);

$tr = $html->tr();
$tr->add('content1');
$ar[] = $tr;

$tr = $html->tr();
$tr->add('content2');
$ar[] = $tr;

$table->add($ar);

echo $table->get_string();
$html->help($table);
?>
