#### What's an EE Shim?
A shim refers to an abstract chunk of PHP code that performs a single function. They can be invoked in your PHP code or through template tags. To provide a context, it is much like a library class that you'd add to your EE build, but with the following benefits:

* Encourages <a href="https://en.wikipedia.org/wiki/Single_responsibility_principle" target="_blank">Single Responsibility Principle</a>
* Provides functions to both PHP and template layers
* Makes code easier to maintain
* Probably less overhead than an equivalent number of separate Add-Ons (I've not performed proper benchmarks to know this for sure)

#### Anatomy of a Shim
A shim is just a simple PHP class that extends a parent class. The parent provides some encapsulating code which standardizes how the shim is initialized, executed, and how the results are accessed.

#### How To Use
Shims can be invoked a few different ways:

**Callback Style**
```php
ee()->eeshim_model->cropImage(
    array('in' => 'full.jpg', 'out' => 'cropped.jpg', 'scale' => 50),
    function($successData)
    {
        // do something with success
    }, function($errors, $errorData)
    {
        // do something with errors
    }
);
```

**In-Scope**   
_The Callback Style approach loses the scope of the calling code, so in many instances the following approach may be preferred._
```php
$obj = ee()->eeshim_model->cropImage(array(
    'in' => 'full.jpg', 
    'out' => 'cropped.jpg', 
    'scale' => 50
));

$obj->run();

if($obj->hasErrors())
{
    $errors = $obj->getErrors();
    $errorData = $obj->getErrorData();
}
else
{
    $data = $obj->getSuccessData();
}
```

**Template Tags**
```
{exp:eeshim:cropImage in="full.jpg" out="cropped.jpg" scale="50"}
```

#### Learn More
The [Wiki](https://github.com/jonathanwkelly/eeshim/wiki) contains documentation on the available methods.
