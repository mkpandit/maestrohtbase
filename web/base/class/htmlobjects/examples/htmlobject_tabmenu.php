<?php
require_once('../htmlobject.class.php');
$html = new htmlobject('../');
$html->debug();

$tab = $html->tabmenu('tab');
$tab->message_replace = array();

$div         = $html->div();
$div->css    = 'htmlobject_div';
$div->id     = 'id';
$div->style  = 'border:1px solid blue;';
$div->title  = 'title';
$div->add('Tab 1 Content');

$content               = array();
$content['tab1']['label']   = 'Tab 1';
$content['tab1']['value']   = $div;
$content['tab1']['target']  = $html->thisfile;
$content['tab1']['request'] = array('param1'=>'value1','param2'=>'value2');
$content['tab1']['onclick'] = false;
$content['tab1']['active']  = true;

$content['tab2']['label']   = 'Tab 2';
$content['tab2']['value']   = 'Tab 2 Content';
$content['tab2']['target']  = $html->thisfile;
$content['tab2']['onclick'] = true;
$content['tab2']['active']  = true;

$content['xx']['label']   = 'Tab 3';
$content['xx']['value']   = 'Tab 3 Content';
$content['xx']['target']  = $html->thisfile;
$content['xx']['request'] = 'param1=xx&param2=tt';
$content['xx']['onclick'] = false;
$content['xx']['active']  = true;

$content[2]['label']   = 'Tab 4';
$content[2]['target']  = $html->thisfile;
$content[2]['onclick'] = false;
$content[2]['active']  = false;
## Set value only if needed
if($tab->get_current() === '2') {
	$content[2]['value']   = 'Tab 4 Content';
}

$tab->add($content);
$tab->css = 'htmlobject_tabs';
$tab->auto_tab = true;

## Overwrite values
$c['tab1']['label']  = 'New Tab 1';
$c['tab1']['target'] = $html->thisfile;
$c['tab2']['value']  = 'New Tab 2 Content';
$tab->add($c);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
	"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>PHP HTMLObjects / Tabmenu Example</title>
<style type="text/css">

.htmlobject_tabs {
	position:relative;
	float: left;
	width: 100%;
	line-height: normal;
    padding: 6px 0 0 0px;
	border-bottom:1px solid #91a7b4;
	margin:0 0 0 0;
    background-color:transparent;
}
.htmlobject_tabs ul {
	position:relative;
	margin: 0 0 0 5px;
	padding: 0 0 0;
	list-style: none;
}
.htmlobject_tabs li {
	position:relative;
	top:1px;
	float: left;
    border-width:1px;
    border-style:solid;
    border-color:#91a7b4;
	margin: 0;
	margin-left: 0;
	margin-right: 2px;
	padding: 0 0 0 0px;
	background-color:#eeeeee;

}
.htmlobject_tabs li.current {
    border-bottom: 1px solid white !important;
    background-color:white;
	margin-right: 2px;
}
.htmlobject_tabs span {
	float: left;
	display: block;
	padding: 0px 0px 0 0;
}
.htmlobject_tabs a {
	display:block;
	padding: 5px 10px;
}
.htmlobject_tabs a, .htmlobject_tabs a:link, .htmlobject_tabs a:visited, .htmlobject_tabs a:hover {
	color: black;
	font-size: 12px;
	text-decoration: none;
	font-family: Arial !important;
}
.htmlobject_tabs .custom_tab {
	float:right;
	line-height: 20px;
	margin: 0 30px 0 0;
}
.htmlobject_tabs .custom_tab a {
	display: inline;
	padding: 0;
	margin: 0;
}
.htmlobject_tabs_box {
	height: 300px;
	border: 1px solid #919B9C;
	border-top: 0px;
	padding: 20px;
	clear: both;
	background-color: white;
	overflow: visible;
}
</style>
</head>
<body>
<?php
echo $tab->get_string();
$html->help($tab);
?>
</body>
</html>
