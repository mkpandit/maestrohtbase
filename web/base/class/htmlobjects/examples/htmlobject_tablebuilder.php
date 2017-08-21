<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>htmlobject_tablebuilder</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="content-style-type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript">
<link rel="stylesheet" type="text/css" href="htmlobject_tablebuilder.css">
</head>
<body>

<?php

require_once('../htmlobject.class.php');
$html = new htmlobject('../');
$html->debug();

$head['id']['hidden']   = true;
$head['id']['sortable'] = false;

$head['folder']['hidden']   = true;
$head['folder']['sortable'] = false;

$head['obj']['title'] = 'Source';
$head['obj']['map']   = 'folder';

$head['name']['title'] = 'Name';

// uncomment to provoke an error
#$head['carlos']['title'] = "Carlos";

$body = array();
for($i = 0; $i < 150; $i++) {
	$x = $i+1;
	if($x < 10)  { $x = '0'.$x; }
	if($x < 150) { $x = '0'.$x; }
	$body[$i]['id']     = $i;
	$body[$i]['folder'] = $x;
	$body[$i]['name']   = 'name '.$x;
	// Be aware, that objects can not be
	// sorted correctly with array_sort.
	// Make sure head is mapped. 
	$div = $html->div();
	$div->style = 'border: 1px solid blue';
	$div->add(rand(1,5).' div folder '.$x);
	$body[$i]['obj'] = $div;
}

// uncomment to provoke an error
#unset($body[4]['name']);
#unset($body[7]['folder']);

// add additional params as htmlobject_response object
// params can be changed before output
$params = $html->response();
$params->params = array('param1'=>'param1', 'param2'=>'param2');

$table = $html->tablebuilder('table', $params);
// Example how tablebuilder can be used for db queries.
// Make sure max is set before you init, because default
// value is 0. If you do not want tablebuilder to use
// default values for limit, offset or order on first
// glance, make sure these values are set too.
// After init, values will be overwriten by request.
// Autosort should be set to false.
# $table->limit    = 40;
# $table->offset   = 50;
# $table->order    = 'DESC';
# $table->max      = $db->count();
# $table->autosort = false;
# $table->init();
# $body = $db->get($table->offset, $table->limit, $table->order);	

$table->max                 = count($body);
$table->limit               = 40;
$table->offset              = 50;
$table->order               = 'DESC';
$table->form_action         = $html->thisfile; 
$table->form_method         = 'GET'; 
$table->css                 = 'htmlobject_table';
$table->border              = 1;
$table->id                  = 'Table';
$table->hide_empty          = false;
$table->head                = $head;
$table->body                = $body;
$table->sort                = 'name';
$table->sort_form           = true;
$table->sort_link           = true;
$table->autosort            = true;
$table->identifier          = 'id';
$table->identifier_name     = 'identifier';
$table->identifier_disabled = array('88','89');
$table->identifier_checked  = array('86','87');
$table->actions             = array('test','example');
$table->actions_name        = 'action';

$table->add_headrow('added headrow text');
$table->add_bottomrow('added bottomrow text');




if(
	$html->request()->get($table->identifier_name) !== '' &&
	$html->request()->get($table->actions_name) !== ''
) {
	echo '<h5>Selected Values</h5>';
	echo '<b>action: </b>'.$html->request()->get($table->actions_name);
	$html->help($html->request()->get($table->identifier_name));
}

echo $table->get_string();
$html->help($table);
?>
</body>
</html>
