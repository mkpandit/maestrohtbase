<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');
#$html->debug();

$template = $html->template('htmlobject_template.html');

$formbuilder = $html->formbuilder();
$formbuilder->css = 'formbuilder';
$formbuilder->display_errors = true;

$data['name']['label']                     = 'Name';
$data['name']['required']                  = true;
$data['name']['validate']['regex']         = '/^[a-z0-9~._-]+$/i';
$data['name']['validate']['errormsg']      = 'string must be a-z0-9~._-';
$data['name']['object']['type']            = 'input';
$data['name']['object']['attrib']['type']  = 'text';
$data['name']['object']['attrib']['name']  = 'name';

$data['submit']['object']['type']            = 'htmlobject_input';
$data['submit']['object']['attrib']['type']  = 'submit';
$data['submit']['object']['attrib']['name']  = 'submit';
$data['submit']['object']['attrib']['value'] = 'submit this form';

$formbuilder->add($data);
$formbuilder->set_error('name1', 'this errormessage is injected');

// Add formbuilder to template
$template->add($formbuilder);

$form         = $html->form();
$form->css    = 'htmlobject_form';
$form->id     = 'id1';
$form->style  = 'border:3px solid red;';
$form->title  = 'title';

$div         = $html->div();
$div->css    = 'htmlobject_div';
$div->id     = 'id2';
$div->style  = 'border:1px solid blue;';
$div->title  = 'title';
$div->add('this is div 1');
$form->add($div, 'div_div1');

$div         = $html->div();
$div->css    = 'htmlobject_div';
$div->id     = 'id3';
$div->style  = 'border:1px solid blue;';
$div->title  = 'title';
$div->add('this is div 2');
$form->add($div, 'div_div2');

// Add form to template
$template->add($form);

$single         = $html->div();
$single->css    = 'htmlobject_div';
$single->id     = 'id4';
$single->style  = 'border:1px solid green;';
$single->title  = 'title';
$single->add('this is div 3');

// Add a single object
$template->add($single, 'div3');

// Add plain text
$template->add('some text', 'text_text2');

// Add integer
$template->add(time(), 'time');

// Grouping
echo 'Before grouping <br>';
echo $template->get_string();
echo '<br>';

$html->help($template);

$template->group_elements(array('div_' => 'div', 'text_' => 'text'));

// change attrib of an element
$tmp = $template->get_elements('submit');
$tmp->disabled = true;
$template->add($tmp, 'submit');

echo 'After grouping <br>';
echo $template->get_string();

$html->help($template);
?>
