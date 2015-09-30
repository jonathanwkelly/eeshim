<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @package EEShim\shims
 * @version 1.0.1
 * @author Jonathan W. Kelly <jonathanwkelly@gmail.com>
 * @link https://github.com/jonathanwkelly/eeshim
 * @example 
 *   Crop and return the path to the image
 *   {exp:eeshim:cropImage
 * 	    in="images/raw.jpg"
 * 	    out="images/cropped.jpg"
 * 	    scale="50"
 *   }
 *   Call the crop shim from PHP
 *   ee()->load->model('eeshim_model');
 *   
 *   $obj = ee()->eeshim_model->cropImage(array(
 *       'in' => 'images/raw.jpg',
 *       'out' => 'images/cropped.jpg',
 *       'scale' => 50
 *   ));
 *   
 *   $obj->run();
 * 
 *   if(!$obj->hasErrors())
 *       $data = $obj->getSuccessData();
 *   else
 *       $errors = $obj->getErrors();
 */
class eeshim_cropImage extends eeshim_parent
{
	/**
	 * @var array $defaults Default parameters
	 */
	protected $defaults = array(
		'image_library' => 'GD2',
		'quality' => 80,
		'create_thumb' => FALSE,
		'maintain_ratio' => TRUE
	);

	// ---

	/**
	 * Performs the file crop
	 */
	function run()
	{
		// validate the params
		if(!file_exists($this->getParam('in')) || !is_readable($this->getParam('in')))
			return $this->fail(sprintf('Cannot read source image: ' . $this->getParam('in')));

		// get the original image dimensions
		$imgsize = getimagesize($this->getParam('in'));

		if(!is_array($imgsize) || !$imgsize[0] || !$imgsize[1])
			return $this->fail('Could not get image dimensions');

		ee()->load->library('image_lib');

		$config = array(
			'image_library' 	=> $this->getParam('image_library'),
			'quality' 			=> $this->getParam('quality'),
			'source_image' 		=> $this->getParam('in'),
			'new_image' 		=> $this->getParam('out'),
			'create_thumb' 		=> (bool) $this->getParam('create_thumb'),
			'maintain_ratio' 	=> (bool) $this->getParam('maintain_ratio'),
			'width' 			=> floor($imgsize[0] * ($this->getParam('scale') / 100)),
			'height' 			=> floor($imgsize[1] * ($this->getParam('scale') / 100)),
			'x_axis' 			=> floor((((100 - $this->getParam('scale')) / 2) / 100) * $imgsize[0]),
			'y_axis' 			=> floor((((100 - $this->getParam('scale')) / 2) / 100) * $imgsize[1]),
		);

		ee()->image_lib->initialize($config);

		ee()->image_lib->crop();

		$errors = ee()->image_lib->display_errors(null, null);

		ee()->image_lib->clear();

		if(!empty($errors))
			return $this->fail($errors);
		else
			return $this->success(array('path' => $this->getParam('out')));
	}
}