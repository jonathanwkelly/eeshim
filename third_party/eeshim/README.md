### What's an EE Shim?
A shim refers to an encapsulated bit of code -- a PHP class -- that functions much like a regular EE Add-On, but are not themselves separate Add-Ons. For instance, a shim could allow an Add-On to give a JSON response, perform replacements on text, crop images -- anything that could be done with a standard EE Add-On. 

### Anatomy of a Shim
At its core, a shim is just a standard PHP class which extends a parent shim class which standardizes the way shims are loaded, called, and how their responses are handled. The shim can be called from your PHP code by loading the `eeshim_model`, or via the eeshim module which provides an gateway to the shims through template tags. Some usage examples:

```
{!-- template: site/index.html --}

{exp:eeshim:jsonOutput data="This is a JSON message"}
```

We'll look at a realistic scenario below, but for now, here's how you would call the same shim from PHP:

```
// file: third_party/mymodule/mod.mymodule.php

function asJson($data=array())
{
	return ee()->eeshim_model->jsonOutput($data);
}
```

Each shim file must contain at least one method named `run()` which will contain the guts of what should be executed. 

### Example Shims
Included in this repo are two example shims: `third_party/eeshim/shims/cropImage` and `third_party/eeshim/shims/jsonResponse`. Each contains example usages on how to call the shim from template tags or from PHP.

### Creating a Shim
Let's create a shim that downloads an image to our `./images/` directory. We'll look at what the template tag might look like first.
```
{!-- template: site/get-image.html --}

{exp:eeshim:downloadImage
	src="https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Merle_Haggard_in_concert_2013.jpg/220px-Merle_Haggard_in_concert_2013.jpg"
	to="images/eeshim-images"
	output="img"
	imgStyles="width: 200px"
}
```

Note that I can use whatever parameters I like. I will then have access to them inside the shim class by calling the $this->getParam() method.

The shim code:
```php
/* file: third_party/eeshim/shims/downloadImage.php */

class eeshim_downloadImage
{
	function run()
	{
		// define our "save to" path from our params
		$path = sprintf(
			'%s/%s',
			realpath($this->getParam('to')),
			pathinfo($this->getParam('src', PATHINFO_BASENAME))
		);

		$ch = curl_init($this->getParam('src'));
		$to = fopen($path, 'wb');

		if(!is_resource($to))
			return;

		curl_setopt_array($ch, array(
			CURLOPT_FILE => $to,
			CURLOPT_HEADER => 0,
			CURLOPT_FOLLOWLOCATION => TRUE,
			CURLOPT_TIMEOUT => 30
		));

		curl_exec($ch);

		fclose($to);

		// if there was an error, invoke fail()
		if(curl_errno($ch))
			return $this->fail(curl_error($ch));

		curl_close($ch);

		// figure out what to return
		switch($this->param('output', null))
		{
			case 'path':
				return $this->success(array('relPath' => $this->_relPath($path)));
				break;
			case 'abspath':
				return $path;
				break;
			case 'img':
				return sprintf('<img src="%s" style="%s">', $this->_relPath($path), $this->getParam('imgStyles'))
				break;
			default:
				return;
		}

		// call the success method
		return $this->success(array('path' => $path));
	}

	// ---

	/**
	 * Ensure we're returning a root-relative path
	 * 
	 * @return string
	 */
	private function _relPath($absPath='')
	{
		return str_replace(
			realpath($_SERVER['DOCUMENT_ROOT']), 
			'', 
			realpath($absPath)
		);
	}
}

``` 

As you can see, this is an isolated bit of code. I could call it from my template in a single tag with parameters, as in the example, or include it in a larger set of PHP code to handle the downloading of an image. Here's how it might look in the latter case.

```php
/* file: third_party/mymodule/mod.mymodule.php */

function someMethod()
{
	ee()->load->model('eeshim_model');

	// ... some PHP code

	$myDownloadedImages = array();

	// download this image
	$obj = ee()->eeshim_model->downloadImage(array(
		'src' => 'http://....jpg',
		'to' => realpath(dirname(__FILE__)) . '/my-module-images',
		'return' => 'path'
	));

	$obj->run();

	// no errors; handle as success
	if(!$obj->hasErrors())
	{
		$data = $obj->getSuccessData();

		$myDownloadedImages[] = $data['path'];
	}

	// deal with errors
	else
	{
		return $obj->getErrors();
	}

	// ... some more PHP code
}

```

Note that the `downloadImage` shim was initialized with an array of params, which returned the shim instance. Then, the `run()` method was called, which actually executes the initialized object. If our shim is properly written, it will call the `success()` or `fail()` method at some point. In our case above, calling these methods would not actually output anything, but instead set the object properties `$successData`, `$errors`, `$errorData` which are then accessed through the methods `getSuccessData()`, `getErrors()`, `getErrorData()`, and can be checked for errors with `hasErrors()`. 

### Using Closures

In our previous example, we made additional calls to get the response data after we determined there were no errors produced. (In other words, `success()` was called instead of `fail()`.) A primary reason is for scoping. We'll just get an array of our success data back, and continue in our processing. You can also pass success and fail closures to the shim call instantiation, which are truly anonymous functions, being invoked without the scope of the calling code. 
```php
/* file: third_party/mymodule/mod.mymodule.php */

function someMethod()
{
	ee()->load->model('eeshim_model');

	// ... some PHP code

	// download this image
	$obj = ee()->eeshim_model->downloadImage(array(
		'src' => 'http://....jpg',
		'to' => realpath(dirname(__FILE__)) . '/my-module-images',
		'return' => 'path'
	), function($successData)
	{

	}, function($errors, $errorData)
	{
		exit('do no more processing because of ' . implode(',', $errors));
	})->run();

	// OR define the closures with the setOnxxxx() methods

	$obj = ee()->eeshim_model->downloadImage(array(
		'src' => 'http://....jpg',
		'to' => realpath(dirname(__FILE__)) . '/my-module-images',
		'return' => 'path'
	))->setOnFail(function($errors, $errorData)
	{
		exit('do no more processing because of ' . implode(',', $errors));
	})->run();

	// ... some more PHP code
}

```