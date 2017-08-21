<?php
/**
 * Puppet Controller
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */


class node_class_select {
	

/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'node_class_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = 'node_class_msg';
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'node_class_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'node_class_identifier';
/**
* htvcenter rootdir
* @access public
* @var string
*/
var $rootdir;
/**
* path to templates
* @access public
* @var string
*/
var $tpldir;
/**
* translation
* @access public
* @var array
*/
var $lang = array();

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @access public
	 * @param htvcenter $htvcenter
	 * @param htmlobject_response $response
	 */
	//--------------------------------------------
	function __construct($htvcenter, $response, $controller) {
		$this->controller = $controller;
		$this->response   = $response;
		$this->htvcenter = $htvcenter;
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->file = $this->htvcenter->file();
		$this->user = $htvcenter->user();
		$this->tpldir   = $this->htvcenter->get('basedir').'/plugins/puppet/web/tpl';
		$this->class_path = $this->htvcenter->get('basedir').'/plugins/puppet/web/puppet/modules';
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @return htmlobject_template
	 */
	//--------------------------------------------
	function action() {
		$response = $this->select();
		$t = $response->html->template($this->tpldir.'/node-class.select.tpl.php');
		
		$t->add($this->response->html->thisfile, "thisfile");
		$t->add($content);
		$t->add($response->add_class, 'add_class');
		$t->add($response->node_list, 'table');
		$t->add($this->lang['label'], 'label');
		$t->add($this->htvcenter->get('baseurl'), 'baseurl');
		$t->group_elements(array('param_' => 'form'));
		return $t;
	}
	
	function select() {
		$response = $this->response;
		$result = $this->scan_dir($this->class_path);
		$b = array();
		
		$x          = $response->html->a();
		$x->href    = $response->get_url($this->actions_name, 'add' );
		$x->label   = "Add Class"; //$this->lang['action_edit'];
		$x->css     = 'add btn-labeled fa fa-plus add-node';
		$x->handler = 'onclick="wait();"';
		$tmp['add'] = $x->get_string();
		
		$tab .= "<table id='Tabelle' class='htmlobject_table table table-hover table-vcenter'>";
		if(empty($result)) {
			$tab .= "<tr><td>Currently no class found.</td></tr>";
		} else {
			$count = 1;
			foreach($result as $v) {
				$a          = $response->html->a();
				$a->href    = $response->get_url($this->actions_name, 'edit' ).'&node_name='.$v;
				$a->label   = $this->lang['action_edit'];
				$a->title   = $this->lang['action_edit'];
				$a->css     = 'edit';				
				$a->handler = 'onclick="wait();"';
				$tmp['action'] = $a->get_string();
			
				$b          = $response->html->a();
				$b->href    = $response->get_url($this->actions_name, 'remove' ).'&node_name='.$v;
				$b->label   = $this->lang['action_remove'];
				$b->title   = $this->lang['action_remove'];
				$b->css     = 'remove';
				$b->handler = 'onclick="wait();"';
				$tmp['del'] = $b->get_string();
			
				$tab .= "<tr><td>" . $count . ".</td><td>" . $v . " </td><td>" . $tmp['action'] . "</td><td>" . $tmp['del'] . "</td></tr>";
				$count++;
			}
		}
		$tab .= "</table>";
		$response->node_list = $tab;
		$response->add_class = $tmp['add'];
		return $response;
	}
	
	function scan_dir($path) {
		$node_list = array_diff(scandir($path), array('..', '.', '.svn'));
		$file_list = array();
		foreach($node_list as $file) {
			if(is_dir($path."/".$file)) {
				$file_list[] = $file;
			}
		}
		return $file_list;
	}
	
}
?>
