<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package EEShim\models
 * @version 1.0.1
 * @author Jonathan W. Kelly <jonathanwkelly@gmail.com>
 * @link https://github.com/jonathanwkelly/eeshim
 */
class eeshim_model
{
	/**
	 * Ensure our parent class is loaded so that it can be extended.
	 * 
	 * @param void
	 * @return void
	 * @access public
	 */
	function __construct()
	{
		require_once realpath(dirname(__FILE__) . '/../') . '/core/eeshim_parent.php';
	}

	// ---

	/**
	 * A catch-all for calls to ee()->eeshim_model->foobar() calls. Will load 
	 * in and instantiate the shim class called, passing along the parameters, 
	 * success closure, and fail closure.
	 * 
	 * @param string $className The shim class name
	 * @param array $args Includes parameters, success closure, and fail closure. 
	 * array(
	 * 	array('param1', 'param2'),
	 * 	function($successData)
	 * 	{
	 * 		// do something 
	 * 	},
	 * 	function($errors, $errorData)
	 * 	{
	 * 		// do something 
	 * 	}
	 * @return object An instance of the shim class
	 * @access public
	 */
	function __call($className='', $args=array())
	{
		// --- try to load the extending class
		if($classFullName = $this->_loadClass($className))
			return new $classFullName(@$args[0], @$args[1], @$args[2]);
	}

	// ---

	/**
	 * Load and instantiate a shim class
	 * 
	 * @param string $className
	 * @return object|FALSE Either a new instance of the class, or FALSE if could not be loaded
	 * @access private
	 */
	private function _loadClass($className='')
	{
		// --- remove the 'eeshim_' prefix, in case it was included
		$className = str_replace('eeshim_', '', $className);

		// --- build the full class name, e.g. 'myClass' --> 'eeshim_myClass'
		$fullClassName = sprintf('eeshim_%s', $className);

		// --- already loaded it...
		if(class_exists($fullClassName))
			return $fullClassName;
		
		// --- else, try to load it 
		$abspath = $this->_classPath($className);

		if(
			file_exists($abspath) && 
			is_readable($abspath) && 
			include_once($abspath)
		) return "eeshim_{$className}";

		return FALSE;
	}

	// ---

	/**
	 * Return the absolute path to the class file
	 * 
	 * @param string $className
	 * @return string
	 * @access private
	 */
	private function _classPath($className='')
	{
		return sprintf('%s/shims/%s.php', realpath(dirname(__FILE__) . '/../'), $className);
	}
}

/* End of file eeshim_model.php */
/* Location: ./system/expressionengine/third_party/eeshim/models/eeshim_model.php */