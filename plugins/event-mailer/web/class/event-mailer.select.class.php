<?php
/**
 * event_mailer Select
 *
    htvcenter Enterprise developed by htvcenter Enterprise GmbH.

    All source code and content (c) Copyright 2014, htvcenter Enterprise GmbH unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with htvcenter Enterprise GmbH.
    The latest version of this license can be found here: http://htvcenter-enterprise.com/license

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://htvcenter-enterprise.com

    Copyright 2014, htvcenter Enterprise GmbH <info@htvcenter-enterprise.com>
 */

class event_mailer_select
{
/**
* name of action buttons
* @access public
* @var string
*/
var $actions_name = 'event_mailer_action';
/**
* message param
* @access public
* @var string
*/
var $message_param = "event_mailer_msg";
/**
* id for tabs
* @access public
* @var string
*/
var $prefix_tab = 'event_mailer_tab';
/**
* identifier name
* @access public
* @var string
*/
var $identifier_name = 'event_mailer_identifier';
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
	function __construct($htvcenter, $response) {
		$this->htvcenter  = $htvcenter;
		$this->user     = $this->htvcenter->user();
		$this->rootdir  = $this->htvcenter->get('webdir');
		$this->response = $response;
		$this->file     = $this->htvcenter->file();
		$this->tpldir   = $this->rootdir.'/plugins/event-mailer/tpl';
		require_once($this->htvcenter->get('basedir').'/plugins/event-mailer/web/class/event-mailer.class.php');
		$this->mailer = new event_mailer();
	}

	//--------------------------------------------
	/**
	 * Action
	 *
	 * @access public
	 * @param string $action
	 * @return htmlobject_tabmenu
	 */
	//--------------------------------------------
	function action($action = null) {

		$response      = $this->select();

		$a = $response->html->a();
		$a->href = $response->html->thisfile.$response->get_string($this->actions_name, 'template', '?', true );
		$a->label = $this->lang['action_template'];
		$a->css = 'add';
		$a->handler = 'onclick="wait();"';

		$data['template'] = $a;
		$data['label'] = $this->lang['label'];
		$data['table'] = $response->table;
		$vars = array_merge(
			$data, 
			array(
				'thisfile' => $response->html->thisfile,
		));
		$t = $response->html->template($this->tpldir.'/event-mailer-select.tpl.php');
		$t->add($vars);
		$t->add($response->form);
		$t->group_elements(array('param_' => 'form'));
		return $t;

	}

	//--------------------------------------------
	/**
	 * Select
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function select() {
		$response = $this->get_response();

		$h['name']['title']    = $this->lang['name'];
		$h['email']['title']   = $this->lang['email'];
		$h['active']['title']  = $this->lang['active'];
		$h['error']['title']   = $this->lang['error'];
		$h['regular']['title'] = $this->lang['regular'];
		$h['warning']['title'] = $this->lang['warning'];
		$h['remove']['title']  = $this->lang['remove'];
		$h['func']['title']    = '&#160;';
		$h['func']['sortable'] = false;

		$result = $this->user->get_users();
		$b = array();
		foreach($result as $k => $v) {
			if($k !== 0) {
				foreach($v as $value) {
					$tmp = array();
					if($value['label'] === 'user_id' && $value['value'] !== '0') {
						$tmp['name'] = $this->user->get_instance_by_id($value['value'])->name;
						$params  = '?user_id='.$value['value'];
						$params .= $response->get_string($this->actions_name, 'edit', '&', true );
						$a = $response->html->a();
						$a->href = $response->html->thisfile.$params;
						$a->label = $this->lang['action_edit'];
						$a->title = $this->lang['action_edit'];
						$a->css   = 'edit';
						$a->handler = 'onclick="wait();"';
						$tmp['func'] =  $a->get_string();
						$result = $this->mailer->get_result_by_user($value['value']);
						if(isset($result)) {
							$tmp['email']   = $result['user_email'];
							$tmp['active']  = ($result['event_active'] !== '0')  ? 'X' : '';
							$tmp['error']   = ($result['event_error'] !== '0')   ? 'X' : '';
							$tmp['warning'] = ($result['event_warning'] !== '0') ? 'X' : '';
							$tmp['regular'] = ($result['event_regular'] !== '0') ? 'X' : '';
							$tmp['remove']  = ($result['event_remove'] !== '0')  ? 'X' : '';
						} else {
							$tmp['email']   = '';
							$tmp['active']  = '';
							$tmp['error']   = '';
							$tmp['warning'] = '';
							$tmp['regular'] = '';
							$tmp['remove']  = '';
						}
						$b[] = $tmp;
					}
				}
			}
		}

		$table                      = $response->html->tablebuilder( 'em', $response->params );
		$table->sort                = 'name';
		$table->css                 = 'htmlobject_table';
		$table->border              = 0;
		$table->id                  = 'Tabelle';
		$table->head                = $h;
		$table->body                = $b;
		$table->sort_params         = $response->get_string( $this->actions_name, 'select' );
		$table->sort_form           = true;
		$table->sort_link           = false;
		$table->autosort            = true;
		$table->max                 = count( $b );
		$response->table = $table;
		return $response;
	}

	//--------------------------------------------
	/**
	 * Get Response
	 *
	 * @access public
	 * @return htmlobject_response
	 */
	//--------------------------------------------
	function get_response() {
		$response = $this->response;
		$form = $response->get_form($this->actions_name, 'select');

		$submit = $form->get_elements('submit');
		$submit->handler = 'onclick="wait();"';
		$form->add($submit, 'submit');

		$submit = $form->get_elements('cancel');
		$submit->handler = 'onclick="cancel();"';
		$form->add($submit, 'cancel');

		$response->form = $form;
		return $response;
	}

}
?>
