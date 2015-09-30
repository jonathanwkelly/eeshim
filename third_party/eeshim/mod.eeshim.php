<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * EE Add-On to facilitate calling a shim from template tags. 
 * When the eeshim module tag is present, all tag parameters 
 * will flow through to the shim to be accessible in the 
 * $params property array
 * 
 * @package EEShim
 * @version 1.0.1
 * @author Jonathan W. Kelly <jonathanwkelly@gmail.com>
 * @link https://github.com/jonathanwkelly/eeshim
 */
class Eeshim
{
	/**
	 * @var string $return_data What should be returned back to 
	 * the EE template parser, as a result of the module execution
	 */
	public $return_data = NULL;

	// ---
	
	/**
	 * Load the eeshim_model class
	 * 
	 * @param void
	 * @return void
	 * @access public
	 */
	public function __construct()
	{
		ee()->load->add_package_path(PATH_THIRD . 'eeshim', FALSE);

		ee()->load->model('eeshim_model');
	}

	// ---

	/**
	 * Catch-all method to pass along the shim class name and arguments
	 * to the shim model for invocation.
	 * 
	 * @param string $className
	 * @param array $args
	 * @return mixed Return value of the shim
	 * @access public
	 */
	function __call($className='', $args=array())
	{
		// --- digest params passed through on the module template tag
		return ee()->eeshim_model->$className(ee()->TMPL->tagparams)->run();
	}
}

/* End of File: mod.eeshim.php */
/* Location: ./system/expressionengine/third_party/eeshim/mod.eeshim.php */