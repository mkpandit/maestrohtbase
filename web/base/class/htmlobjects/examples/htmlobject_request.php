<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');
$request = $html->request();

$form         = $html->form();
$form->action = $html->thisfile;
$form->css    = 'htmlobject_form';
$form->id     = 'form';

$a        = $html->input();
$a->id    = 'id1';
$a->name  = 'input[id1]';
$a->value = 'hello';
$form->add($a);

$b        = $html->input();
$b->id    = 'id2';
$b->name  = 'input[id2]';
$b->value = 'world';
$form->add($b, 'id2');

$submit        = $html->input();
$submit->id    = 'submit';
$submit->type  = 'submit';
$submit->name  = 'submit';
$submit->value = 'submit will change hello to welcome and world to universe';
$form->add($submit);

if($request->get('submit') !== '') {
	// set the request filter to 
	// replace world by universe
	$old = $request->get('input');
	echo 'unfiltered request';
	$html->help($old); 
	$filter = array(
  				array ( 'pattern' => '~hello~', 'replace' => 'welcome'),
   				array ( 'pattern' => '~world~', 'replace' => 'universe'),
 			);
	$request->set_filter($filter);
	$new = $request->get('input');
	echo 'filtered request';
	$html->help($new);

	$a->value = $new['id1'];
	$b->value = $new['id2'];
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>PHP HTMLObjects / Manual / htmlobject_request</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="content-style-type" content="text/css">
<meta http-equiv="content-script-type" content="text/javascript">
</head>
<body>

<?php echo $form->get_string(); ?>

<?php $html->help($request); ?>


</body>
</html>







