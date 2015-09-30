<?php

/**
 * @package EEShim\shims
 * @version 1.0.1
 * @author Jonathan W. Kelly <jonathanwkelly@gmail.com>
 * @link https://github.com/jonathanwkelly/eeshim
 * @example 
 * Output a JSON object from tag params (one-level objects)
 *   {exp:eeshim:jsonResponse
 *       addon-name="EE Shim"
 *       shim="jsonResponse"
 *   }
 * Output a JSON object by parsing tagdata
 *   {exp:eeshim:jsonResponse}
 *       {
 *           "addon-name": "EE Shim",
 *           "shim-info": {
 *               "name": "jsonResponse",
 *               "description": "Outputs content as a JSON document"
 *           }
 *       }
 *   {/exp:eeshim:jsonResponse}
 * 
 * Output a JSON response from PHP
 *    ee()->load->model('eeshim_model');
 * 
 *    return ee()->eeshim_model->jsonResponse(array(
 *        'addon-name' => 'EE Shim',
 *        'shim-info' => array(
 *            'name' => 'jsonResponse',
 *            'description' => 'Outputs content as a JSON document'
 *        )
 *    ))->run();
 */
class eeshim_jsonResponse extends eeshim_parent
{
	// ---

	/**
	 * Outputs a JSON object of either a success or error response
	 */
	function run()
	{
		extract($this->params);

		// --- output the tagdata passed in
		if(ee()->TMPL->tagdata)
		{
			$output = json_decode(ee()->TMPL->tagdata);
		}

		// --- output the parameters as a JSON object
		elseif(!empty(ee()->TMPL->tagparams))
		{
			$output = array();

			foreach(ee()->TMPL->tagparams as $key => $val)
				$output[$key] = $val;
		}

		// --- output a clean JSON response
		
		@ob_end_clean();

		header('Content-Type: application/json');
		http_response_code(200);

		exit(json_encode($output));
	}
}