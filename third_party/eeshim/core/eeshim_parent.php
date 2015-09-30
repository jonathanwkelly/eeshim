<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Parent class for shim classes to extend
 * 
 * @package EEShim\core
 * @version 1.0.1
 * @author Jonathan W. Kelly <jonathanwkelly@gmail.com>
 * @link https://github.com/jonathanwkelly/eeshim
 */
class eeshim_parent
{
	/**
	 * @var array $params
	 * Will hold the merged params from $defaults and what is passed in 
	 * as arguments to the constructor. If calling from PHP code, this will 
	 * be populated by the first argument in the constructor call. If calling 
	 * from the EEShim module, this will be populated from the parameters on 
	 * the template tag.
	 */
	protected $params = array();

	/**
	 * @var array $defaults
	 * Default parameters for an instance
	 */
	protected $defaults = array();
	
	/**
	 * @var function $onSuccess
	 * A function to be invoked when success() is called
	 */
	protected $onSuccess = null;

	/**
	 * @var function $onFail
	 * A function to be invoked when fail() is called
	 */
	protected $onFail = null;

	/**
	 * @var array $successData
	 * Will hold any data to be passed to the success() method
	 */
	protected $successData = array();

	/**
	 * @var array $errorData
	 * Will hold any data to be passed to the fail() method
	 */
	protected $errorData = array();

	/**
	 * @var array $errors
	 * Will hold any errors generated and passed to the fail() method
	 */
	protected $errors = array();

	// ---

	/**
	 * Initialize a new instance of an EEShim class
	 * 
	 * @param array $params An array of any data that our implementing class will use; 
	 * these params will be merged with the $defaults content for the implementing class
	 * @param function $onSuccess Optional "success" callback to be invoked when the 
	 * implementing class determines execution was successful
	 * @param function $onFail Optional "fail" callback to be invoked when the 
	 * implementing class determines execution was a failure
	 * @return void
	 * @access public
	 */
	function __construct($params=array(), $onSuccess=null, $onFail=null)
	{
		$this->params = is_array($params) ? 
			array_replace_recursive($this->defaults, $params) : $this->defaults;

		$this->setOnSuccess($onSuccess);
		$this->setOnFail($onFail);
	}

	// ---

	/**
	 * Defines the closure function to execute when success() is called
	 * 
	 * @param function $onSuccess A closure to be invoked on success()
	 * @return object $this
	 * @access public
	 */
	public function setOnSuccess($onSuccess=null)
	{
		if(isset($onSuccess) && is_object($onSuccess) && is_callable($onSuccess))
			$this->onSuccess = $onSuccess;

		return $this;
	}

	// ---

	/**
	 * Defines the closure function to execute when fail() is called
	 * 
	 * @param function $onFail A closure to be invoked on fail()
	 * @return object $this
	 * @access public
	 */
	public function setOnFail($onFail=null)
	{
		if(isset($onFail) && is_object($onFail) && is_callable($onFail))
			$this->onFail = $onFail;

		return $this;
	}

	// ---

	/**
	 * Calls the run() method in the shim class
	 * 
	 * @param void
	 * @return mixed Passes back the return value of the shim's run() method; 
	 * If the method does not exist, will return null.
	 * @access public
	 */
	public function execute()
	{
		return method_exists($this, 'run') ? 
			 call_user_func_array(array($this, 'run'), $this->params) : null;
	}

	// ---

	/**
	 * Get a value from the $params property array
	 * 
	 * @param string $paramName For array children, separate the keys with 
	 * a colon, e.g. 'arr1key:childarrkey'
	 * @param mixed $default What to return if the key is not set
	 * @return mixed
	 * @access public
	 */
	public function getParam($paramName='', $default=null)
	{
		$item = $this->params;

		foreach(explode(':', $paramName) as $part)
		{
			if(isset($item[$part]))
				$item = $item[$part];
			else
				return $default;
		}

		return $item;
	}

	// ---

	/**
	 * Get the data passed to the success() method
	 * 
	 * @param void
	 * @return array
	 * @access public
	 */
	public function getSuccessData()
	{
		return $this->successData;
	}

	// ---

	/**
	 * Checks to see if any errors were produced / if fail() was called.
	 * 
	 * @param void
	 * @return boolean
	 * @access public
	 */
	public function hasErrors()
	{
		return !empty($this->errors);
	}

	// ---

	/**
	 * Get any errors passed to the fail() method
	 * 
	 * @param void
	 * @return array
	 * @access public
	 */
	public function getErrors()
	{
		if(is_array($this->errors))
			return $this->errors;
		elseif(is_string($this->errors))
			return array($this->errors);
		else
			return array();
	}

	// ---

	/**
	 * Get the data passed to the fail() method
	 * 
	 * @param void
	 * @return array
	 * @access public
	 */
	public function getErrorData()
	{
		return $this->errorData;
	}

	// ---

	/**
	 * Invoke the $onSuccess closure
	 * 
	 * @param array $data An array of values to be passed to the closure as 
	 * the single parameter
	 * @return mixed The return value of the closure
	 * @access public
	 */
	public function success($data=array())
	{
		$this->successData = $data;

		// --- reset any errors
		$this->errors = array();
		$this->errorData = array();

		// --- invoke a callback?
		if(is_object($this->onSuccess))
			return $this->onSuccess->__invoke($this->successData);

		return;
	}

	// ---

	/**
	 * Invoke the $onFail closure
	 * 
	 * @param array|string $errors An array of error messages, or a single 
	 * error message string, to be passed to the closure as the first parameter
	 * @param array $data Optional data to be passed to the closure as the second parameter
	 * @return mixed The return value of the closure
	 * @access public
	 */
	public function fail($errors=array(), $data=array())
	{
		$this->errors = $errors;
		$this->errorData = $data;

		// --- reset any success data
		$this->successData = array();

		// --- ensure our $errors property is an array
		if(is_string($this->errors))
			$this->errors = array($this->errors);
		
		// --- invoke a callback?
		if(is_object($this->onFail))
			return $this->onFail->__invoke($this->errors, $this->errorData);

		return;
	}
}

/* End of file eeshim_parent.php */
/* Location: ./system/expressionengine/third_party/eeshim/core/eeshim_parent.php */