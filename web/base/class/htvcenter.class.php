<?php
/**
 * htvcenter Class
 *
 * htvcenter Content
 *
    htvcenter Enterprise developed by HTBase Corp.

    All source code and content (c) Copyright 2015, HTBase Corp unless specifically noted otherwise.

    This source code is released under the htvcenter Enterprise Server and Client License, unless otherwise agreed with HTBase Corp.

    By using this software, you acknowledge having read this license and agree to be bound thereby.

                http://www.htbase.com

    Copyright 2015, HTBase Corp <contact@htbase.com>
 */

class htvcenter
{
/**
* Absolute path to istall dir
* @access protected
* @var string
*/
protected $basedir;
/**
* Absolute path to class dir
* @access protected
* @var string
*/
protected $classdir;
/**
* Absolute path to web dir
* @access protected
* @var string
*/
protected $webdir;
/**
* Absolute uri
* @access protected
* @var string
*/
protected $baseurl;
/**
* htvcenter config
* @access protected
* @var string
*/
protected $config;
/**
* DB object
* @access private
* @var object
*/
private $db;
/**
* file object
* @access private
* @var object
*/
private $file;
/**
* admin user object
* @access private
* @var object
*/
private $admin;
/**
* current user object
* @access private
* @var object
*/
private $user;
/**
* name of db tables
* @access private
* @var array
*/
private $table;

	//--------------------------------------------
	/**
	 * Constructor
	 *
	 * @param object $file
	 * @param object $user
	 * @param object $response
	 * @access public
	 */
	//--------------------------------------------
	function __construct($file, $user, $response) {
		if ((file_exists("/etc/init.d/htvcenter")) && (is_link("/etc/init.d/htvcenter"))) {
			$this->basedir = dirname(dirname(dirname(readlink("/etc/init.d/htvcenter"))));
		} else {
			$this->basedir = "/usr/share/htvcenter";
		}
		$this->response = $response;
		$this->classdir = $this->basedir.'/web/base/class';
		$this->webdir   = $_SERVER["DOCUMENT_ROOT"].'/htvcenter/base';
		$this->baseurl  = '/htvcenter/base';
		$this->config   = $this->parse_conf($this->basedir.'/etc/htvcenter-server.conf', 'htvcenter_');
		
		$this->table['appliance']      = 'appliance_info';
		$this->table['deployment']     = 'deployment_info';
		$this->table['event']          = 'event_info';
		$this->table['image']          = 'image_info';
		$this->table['kernel']         = 'kernel_info';
		$this->table['resource']       = 'resource_info';
		$this->table['storage']        = 'storage_info';
		$this->table['virtualization'] = 'virtualization_info';

		// used regexes
		$this->regex['name'] = '~^[a-z0-9]+$~i';
		$this->regex['hostname'] = '~^[a-z0-9]{1,}[a-z0-9-]*[a-z0-9]$~i';
		$this->regex['comment'] = '/^[a-z 0-9()._-]+$/i';
				
		$this->file = $file;
		$this->user = $user;
		// load object
		$this->lc();
	}

	//--------------------------------------------
	/**
	 * Init
	 *
	 * @access public
	 */
	//--------------------------------------------
	function init() {
		require_once($this->classdir.'/appliance.class.php');
		require_once($this->classdir.'/deployment.class.php');
		require_once($this->classdir.'/event.class.php');
		require_once($this->classdir.'/image.class.php');
		require_once($this->classdir.'/kernel.class.php');
		require_once($this->classdir.'/plugin.class.php');
		require_once($this->classdir.'/resource.class.php');
		require_once($this->classdir.'/storage.class.php');
		require_once($this->classdir.'/virtualization.class.php');
	}

	//--------------------------------------------
	/**
	 * Get object attributes
	 *
	 * @access public
	 * @param string $attrib name of attrib to return
	 * @param string $key name of attrib key to return
	 * @return mixed
	 */
	//--------------------------------------------
	function get($attrib, $key = null) {
	
		if(isset($this->$attrib)) {
			$attrib = $this->$attrib;
			if(isset($key)) {
				if(isset($attrib[$key])) {
					return $attrib[$key];
				}
			} else {
				return $attrib;
			}
		}
	}

	//--------------------------------------------
	/**
	 * Set object attributes
	 *
	 * @access public
	 * @param string $attrib name of attrib to return
	 * @param string $key name of attrib key to set
	 * @return bool
	 */
	//--------------------------------------------
	function set($attrib, $value, $key = null) {
		if(isset($this->$attrib)) {
			if(isset($key)) {
				$tmp = &$this->$attrib;
				if(isset($tmp[$key])) {
					$tmp[$key] = $value;
					return true;
				} else {
					return false;
				}
			} else {
				$this->$attrib = $value;
				return true;
			}
		} else {
			return false;
		}
	}

	//--------------------------------------------
	/**
	 * Get db object
	 *
	 * @access protected
	 * @return object db
	 */
	//--------------------------------------------
	protected function db() {
		if(!isset($this->db)) {
			require_once($this->classdir.'/db.class.php');
			$this->db = new db($this);
		}
		return $this->db;
	}

	//--------------------------------------------
	/**
	 * Get file object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function file() {
		return $this->file;
	}

	//--------------------------------------------
	/**
	 * Get admin object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function admin() {
		if(!isset($this->admin)) {
			$user  = $this->user();
			$class = get_class($user);
			$admin = new $class('htvcenter');
			$this->admin = $admin;
		}
		return $this->admin;
	}

	//--------------------------------------------
	/**
	 * Get user object
	 *
	 * @access public
	 * @return file_handler
	 */
	//--------------------------------------------
	function user() {
		return $this->user;
	}

	//--------------------------------------------
	/**
	 * Get role object (plugin loader)
	 *
	 * @access public
	 * @param htmlobject_response $response
	 * @return htvcenter_role
	 */
	//--------------------------------------------
	function role($response) {
		require_once($this->classdir.'/htvcenter.role.class.php');
		$role = new htvcenter_role($this, $response);
		return $role;
	}

	//--------------------------------------------
	/**
	 * Get appliance object
	 *
	 * @access public
	 * @return object appliance
	 */
	//--------------------------------------------
	function appliance() {
		require_once($this->classdir.'/appliance.class.php');
		return new appliance();
	}

	//--------------------------------------------
	/**
	 * Get deployment object
	 *
	 * @access public
	 * @return object deployment
	 */
	//--------------------------------------------
	function deployment() {
		require_once($this->classdir.'/deployment.class.php');
		return new deployment();
	}

	//--------------------------------------------
	/**
	 * Get event object
	 *
	 * @access public
	 * @return object event
	 */
	//--------------------------------------------
	function event() {
		require_once($this->classdir.'/event.class.php');
		return new event();
	}

	//--------------------------------------------
	/**
	 * Get image object
	 *
	 * @access public
	 * @return object image
	 */
	//--------------------------------------------
	function image() {
		require_once($this->classdir.'/image.class.php');
		return new image();
	}

	//--------------------------------------------
	/**
	 * Get kernel object
	 *
	 * @access public
	 * @return object kernel
	 */
	//--------------------------------------------
	function kernel() {
		require_once($this->classdir.'/kernel.class.php');
		return new kernel();
	}

	//--------------------------------------------
	/**
	 * Get plugin object
	 *
	 * @access public
	 * @return object plugin
	 */
	//--------------------------------------------
	function plugin() {
		require_once($this->classdir.'/plugin.class.php');
		return new plugin();
	}

	//--------------------------------------------
	/**
	 * Get resource object
	 *
	 * @access public
	 * @return object resource
	 */
	//--------------------------------------------
	function resource() {
		require_once($this->classdir.'/resource.class.php');
		return new resource();
	}

	//--------------------------------------------
	/**
	 * Get storage object
	 *
	 * @access public
	 * @return object storage
	 */
	//--------------------------------------------
	function storage() {
		require_once($this->classdir.'/storage.class.php');
		return new storage();
	}

	//--------------------------------------------
	/**
	 * Get virtualization object
	 *
	 * @access public
	 * @return object virtualization
	 */
	//--------------------------------------------
	function virtualization() {
		require_once($this->classdir.'/virtualization.class.php');
		return new virtualization();
	}

	//--------------------------------------------
	/**
	 * Get htvcenter server object
	 *
	 * @access public
	 * @return object htvcenter_server
	 */
	//--------------------------------------------
	function server() {
		require_once($this->classdir.'/htvcenter_server.class.php');
		return new htvcenter_server();
	}

	//--------------------------------------------
	/**
	 * Parse an htvcenter config file
	 *
	 * @access public
	 * @param string $path
	 * @param string $replace
	 * @return array
	 */
	//--------------------------------------------
	function parse_conf ( $path, $replace = null ) {
		if(file_exists($path)) {
			$ini = file( $path );
			if ( count( $ini ) == 0 ) { return array(); }
			$globals = array();
			foreach( $ini as $line ){
				$line = trim( $line );
				// Comments
				if ( $line == '' || $line{0} != 'O' ) { continue; }
				// Key-value pair
				list( $key, $value ) = explode( '=', $line, 2 );
				$key = trim( $key );
				if(isset($replace)) {
					$key = str_replace($replace, "", $key );
				}
				$value = trim( $value );
				$value = str_replace("\"", "", $value );
				$globals[ $key ] = $value;
			}
		return $globals;
		}
	}

	//--------------------------------------------
	/**
	 * lc
	 *
	 * @access public
	 * @param string $path
	 * @param string $replace
	 * @return array
	 */
	//--------------------------------------------
	function lc () {
		$now=$_SERVER['REQUEST_TIME'];
		$server_version = '5.2';
		$this->cd = '2419200';
		$lf = $this->basedir."/etc/license/htvcenter-enterprise-server-license.".$server_version.".dat";
		$en = $this->file->get_contents($lf);
		$de = '';

		$progress = $this->response->html->request()->get('upload');
		// check only if not in upload mode
		if($progress === '') {
			if (!$this->file->exists($lf)) {
				$this->response->redirect(
					$this->response->get_url('upload', 'true', 'upload_msg', 'License not available')
				);
			}
			else if (!$publickey = openssl_get_publickey("file://".$this->basedir."/etc/license/public.".$server_version.".key")) {
				$this->response->redirect(
					$this->response->get_url('upload', 'true', 'upload_msg', 'Public key failed')
				);
			}
			else if (!openssl_public_decrypt($en, $de, $publickey)) {
				$this->response->redirect(
					$this->response->get_url('upload', 'true', 'upload_msg', 'Failed to decrypt license')
				);
			}
		}

		$l = explode(',', $de);
		$this->l = $l;
		$cfs = array();
		if($this->file->exists($this->basedir."/etc/license/")) {
			$cfs = scandir($this->basedir."/etc/license/");
		}
		$tc = 0;
		$fc = array();
		foreach ($cfs as $cf) {
			if (strstr($cf, "htvcenter-enterprise-client-license.".$server_version."."))  {
				$md = md5_file($this->basedir."/etc/license/".$cf);
				if (in_array($md, $fc)) {
					continue;
				}
				$fc[] = $md;
				$en = $this->file->get_contents($this->basedir."/etc/license/".$cf);
				$de = '';
				// check only if not in upload mode
				if($progress === '') {
					if (!openssl_public_decrypt($en, $de, $publickey)) {
						$this->response->redirect(
							$this->response->get_url('upload', 'true', 'upload_msg', 'Failed to decrypt license')
						);
					}
				}
				$c = explode(',', $de);
				$this->c[] = $c;
				if (isset($c[3]) && $c[3] > $now && $c[1] == $server_version) {
					$tc = $tc + $c[2];
				}
			}
		}
		$this->tc = $tc;
		if(isset($publickey)) {
			openssl_free_key($publickey);
		}
	}


}
?>
