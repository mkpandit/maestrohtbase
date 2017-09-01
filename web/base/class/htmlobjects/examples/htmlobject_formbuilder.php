<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');
$html->debug();

$formbuilder = $html->formbuilder();
$formbuilder->box_css = 'htmlobject_box';
$formbuilder->display_errors = true;

## Add plain text
$formbuilder->add('This is plaintext', 'plaintext');
## Add an object
$div = $html->div();
$div->style = 'border: 1px solid blue';
$div->add('div text');
$formbuilder->add($div, 'divobject');
## Add Input - Required
$data['name']['label']                        = 'Name';
$data['name']['required']                     = true;
$data['name']['validate']['regex']            = '/^[a-z0-9~._-]+$/i';
$data['name']['validate']['errormsg']         = 'string must be a-z0-9~._-';
$data['name']['object']['type']               = 'input';
$data['name']['object']['attrib']['type']     = 'text';
$data['name']['object']['attrib']['name']     = 'names[test]';
## Add Input - Static
$data['name1']['label']                     = 'Static Value';
$data['name1']['static']                    = true;
$data['name1']['css']                       = 'helo';
$data['name1']['object']['type']            = 'htmlobject_input';
$data['name1']['object']['attrib']['type']  = 'text';
$data['name1']['object']['attrib']['name']  = 'static';
$data['name1']['object']['attrib']['value'] = 'static value';
## Add Checkbox
$data['cbox']['label']                     = 'cbox';
$data['cbox']['required']                  = true;
$data['cbox']['object']['type']            = 'htmlobject_input';
$data['cbox']['object']['attrib']['type']  = 'checkbox';
$data['cbox']['object']['attrib']['name']  = 'cbox444';
$data['cbox']['object']['attrib']['value'] = 'cbox value';
## Add Textarea
$data['tarea']['label']                         = 'textarea';
$data['tarea']['required']                      = true;
$data['tarea']['object']['type']                = 'htmlobject_textarea';
$data['tarea']['object']['attrib']['name']      = 'tarea';
$data['tarea']['object']['attrib']['maxlength'] = 10;
$data['tarea']['object']['attrib']['minlength'] = 5;
## Add Select
$data['select']['label']                        = 'select';
$data['select']['required']                     = true;
$data['select']['object']['type']               = 'htmlobject_select';
$data['select']['object']['attrib']['name']     = 'select444[]';
$data['select']['object']['attrib']['multiple'] = true;
$data['select']['object']['attrib']['index']    = array(0,1);
$data['select']['object']['attrib']['options']  = array(array("value1", "label1"), array("value2", "label2"));
## Add Input from Object
$input = $html->input();
$input->name = 'names[demo]';
$data['obj']['label']  = 'object';
$data['obj']['static'] = true;
$data['obj']['object'] = $input;
## Add Submit
$data['submit']['object']['type']            = 'htmlobject_input';
$data['submit']['object']['attrib']['type']  = 'submit';
$data['submit']['object']['attrib']['name']  = 'submit';
$data['submit']['object']['attrib']['value'] = 'submit this form';
$formbuilder->add($data);

## Add single object
$input = $html->input();
$input->name = 'single';
$input->value = 'single object';
$formbuilder->add($input, 'single');

## Overwrite values
$input = $html->input();
$input->name = 'fred';
$input->value = 'test';
$x['obj']['required'] = true;
$x['obj']['static']   = false;
$x['obj']['object']   = $input;
$x['single']['label'] = 'injected label';
$formbuilder->add($x);


$formbuilder->set_label('obj', 'Object');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>PHP HTMLObjects / Formbuilder Example</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<style type="text/css">
.<?php echo $formbuilder->box_css;?>.error {
	color:red;
}
.<?php echo $formbuilder->box_css;?> .errormsg {
	background:yellow;
	border: 1px solid;
}
</style>
</head>
<body>
<?php
	$error = $formbuilder->get_errors();
	if(isset($error)) {
		echo 'ERRORS';
		$html->help($formbuilder->get_errors());
	}
	## Inject an Error
	$formbuilder->set_error('name1', 'this errormessage is injected');
?>

<?php 
echo $formbuilder->get_string();
echo 'Name Values<br>'; 
$html->help($formbuilder->get_names());
echo 'Request Values (raw)<br>'; 
$html->help($formbuilder->get_request(null, true));
echo 'Request Values<br>'; 
$html->help($formbuilder->get_request(null));
echo 'Static Values<br>'; 
$html->help($formbuilder->get_static());
echo 'Object<br>'; 
$html->help($formbuilder);
?>
</body>
</html>
