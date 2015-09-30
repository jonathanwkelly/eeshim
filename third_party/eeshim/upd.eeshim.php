<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Installation & Update 
 * 
 * @package EEShim
 * @version 1.0.1
 * @author Jonathan W. Kelly <jonathanwkelly@gmail.com>
 * @link https://github.com/jonathanwkelly/eeshim
 */
class Eeshim_upd
{
	public $version = '1.0';

	private $name = 'Eeshim';

	public function install()
	{
		ee()->db->insert(
			'modules', 
			array(
				'module_name' => $this->name,
				'module_version' => $this->version,
				'has_cp_backend' => 'n',
				'has_publish_fields' => 'n'
			)
		);

		return TRUE;
	}

	public function update($current = '')
	{
		return ($current != $this->version);
	}

	public function uninstall()
	{
		ee()->db->where('module_name', $this->name)->delete('modules');
		
		return TRUE;
	}
}

/* End of File: upd.eeshim.php */
/* Location: ./system/expressionengine/third_party/eeshim/upd.eeshim.php */