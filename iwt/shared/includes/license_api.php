<?php
/**
|*  IWT License API
|*  Version: 1.0.0
|*  Created: July 22nd, 2011
|*  Last Modified: Never
|*  Author: Ideal Web Technologies (www.idealwebtech.com)
|*
|*  Copyright (c) 2011 Ideal Web Technologies
|*  This file is only to be used with the consent of Ideal Web Technologies 
|*  and may not be redistributed in whole or significant part!  By using
|*  this file, you agree to the Ideal Web Technologies' Terms of Service
|*  at www.idealwebtech.com/documents/tos.html
**/

/**
|*  --- IMPORTANT NOTICE ---
|*
|*  Any attempt made to bypass, alter, inhibit, or modify the behavor of this
|*  licensing system is a direct violation of Your License Agreement with Ideal Web Technologies.
|*
|*  The following actions, and more, may be taken against anyone in violation of said License Agreement:
|*	  - All active license keys for any Ideal Web Technologies' product will be terminated
|*    - Web Host and/or ISP contacted about fraudulent activity
|*    - Permanent ban for the Ideal Web Technologies' website
|*    - Permanent ban for any service, website, or product provided by Ideal Web Technologies
**/

/**
|*	@class  IWT_License
|*	@desc   Allows for easy usage of the Ideal Web Technologies' License API.
**/
class IWT_License
{
	/**
	|*	@prot	String	key			Stores the license key used for API calls.
	|*	@prot	String	identifier	Stores the identifier used for API calls.
	|*	@prot	String	errorCode	Stores the most recent errorCode.
	|*	@prot	String	errorString	Stores the most recent errorString.
	**/
	protected $key = '';
	protected $identifier = null;
	protected $errorCode = null;
	protected $errorString = null;

	/**
	|*	@func	__construct
	|*	@desc	Class constructor that sets the class up to be used properly.
	|*
	|*	@param	String	key			The license key used for API calls.
	|*	@param	String	identifier	{Defualt: FALSE} The identifier used for API calls.
	**/
	public function __construct($key, $identifier = false)
	{
		$this->key = $key;
		$this->identifier = $identifier;
	}

	/**
	|*	@func	set_key
	|*	@desc	Sets the key member variable.
	|*
	|*	@param	String	key		The new license key to set.
	**/
	public function set_key($key)
	{
		$this->key = $key;
	}

	/**
	|*	@func	set_identifier
	|*	@desc	Sets the identifier member variable.
	|*
	|*	@param	String	identifier	The new identifier to set.
	**/
	public function set_identifier($identifier)
	{
		$this->identifier = $identifier;
	}

	/**
	|*	@func	activate
	|*	@desc	Sends an activation request.
	|*
	|*	@param	Int		packageid	The product package this request belongs to.
	|*	@param	Array	extra		{Defualt: FALSE} Any extra information to send with the request.
	|*
	|*	@return	Mixed	The usage id if activation was successful, else FALSE.
	**/
	public function activate($packageid, $extra = false)
	{
		// First get the info and confrim we are looking at the right package
		$info = $this->send_call('info');
		if (!$info) { return false; }

		if ($info['purchase_pkg'] != $packageid)
		{
			$this->errorCode = '001';
			$this->errorString = 'WRONG_PACKAGE';

			return false;
		}

		// Set up the extra to send
		$params = array('extra' => array());

		if (is_array($extra))
		{
			$params['extra'] = $extra;
		}

		$params['extra']['HTTP Host'] = $_SERVER['HTTP_HOST'];

		// Send the activation call
		$activation = $this->send_call('activate', $params);

		// Check that we didn't get an error
		if ($activation)
		{
			return $activation['USAGE_ID'];
		}

		return false;
	}

	/**
	|*	@func	check
	|*	@desc	Sends a check request.
	|*
	|*	@param	Int		usageid		The usage id returned by the activation of the key.
	|*
	|*	@return	Boolean		TRUE if the request returns a status of ACTIVE, else FALSE.
	**/
	public function check($usageid)
	{
		$check = $this->send_call('check', array('usage_id' => $usageid));

		if ($check)
		{
			if ($check['STATUS'] == 'ACTIVE')
			{
				return true;
			}
			else if ($check['STATUS'] == 'EXPIRED')
			{
				$this->errorCode = '002';
				$this->errorString = $check['STATUS'];
			}
			else if ($check['STATUS'] == 'INACTIVE')
			{
				$this->errorCode = '003';
				$this->errorString = $check['STATUS'];
			}
			else
			{
				$this->errorCode = -1;
				$this->errorString = 'UNKNOWN';
			}
		}

		return false;
	}

	/**
	|*	@func	info
	|*	@desc	Sends an info request.
	|*
	|*	@return	Mixed	Returns FALSE on error, else returns an array containing information recieved from the request.
	**/
	public function info()
	{
		return $this->send_call('info');
	}

	/**
	|*	@func	update
	|*	@desc	Sends an updateExtra request.
	|*
	|*	@param	Int		usageid		The usage id returned by the activation of the key.
	|*	@param	Array	extra		The extra information to send with the request.
	|*
	|*	@return	Boolean		TRUE if the update was successful, else FALSE.
	**/
	public function update($usageid, $extra)
	{
		$params = array(
			'usage_id' => $usageid,
			'extra' => array()
		);

		if (is_array($extra))
		{
			$params['extra'] = $extra;
		}

		$params['extra']['HTTP Host'] = $_SERVER['HTTP_HOST'];

		return (bool) $this->send_call('updateExtra', $params);
	}

	/**
	|*	@func	get_error_code
	|*	@desc	Gets the errorCode member variable.
	|*
	|*	@return	String	The errorCode currently stored.
	**/
	public function get_error_code()
	{
		return $this->errorCode;
	}

	/**
	|*	@func	get_error_string
	|*	@desc	Gets the errorString member variable.
	|*
	|*	@return	String	The errorString currently stored.
	**/
	public function get_error_string()
	{
		return $this->errorString;
	}

	/**
	|*	@func	get_error_message
	|*	@desc	Gets a friendly error message for the currently stored errorCode member variable.
	|*
	|*	@return	String	The friendly error message.
	**/
	public function get_error_message()
	{
		switch($this->errorString)
		{
			case 'BAD_KEY':
				return "The key provided is not valid.";
			case 'BAD_ID':
				return "The identifier set was incorrect.";
			case 'WRONG_PACKAGE':
				return "The key provided does not belong to this product.";
			case 'MAX_USES':
				return "The key provided has already been activated the maximum number of times allowed.";
			case 'BAD_IP':
				return "The IP address the request originated from does not match the IP address of the activation request.";
			case 'EXPIRED':
				return "The purchase associated with the key provided has expired.";
			case 'INACTIVE':
				return "The key provided, or the purchase associated with it, has been deactivated.";
			case 'BAD_USAGE_ID':
				return "The usage ID provided was invalid.";
			case 'NO_USAGE_ID':
				return "You did not provide a usage ID.";
			case 'ID_ALREADY_SET':
				return "setIdentifier was TRUE, but the identifier was already set.";
			default:
				return "An unkown error has occurred.";
		}
	}

	/**
	|*	@func	build_value_xml
	|*	@desc	Builds the xml of a single value for use in send call.
	|*
	|*	@param	Mixed	value	The value to convert.
	|*
	|*	@return	String	The newly converted value.
	**/
	protected function build_value_xml($value)
	{
		if (is_bool($value))
		{
			return '<boolean>' . $value . '</boolean>';
		}
		else if (is_int($value))
		{
			return '<int>' . $value . '</int>';
		}
		else if (is_float($value))
		{
			return '<double>' . $value . '</double>';
		}
		else if (is_string($value))
		{
			return '<string>' . $value . '</string>';
		}
		else if (is_array($value))
		{
			// Doesn't handle non-associative arrays correctly
			$xml = '';

			foreach ($value AS $name => $value2)
			{
				$xml .= '<member><name>' . $name . '</name><value>' . $this->build_value_xml($value2) . '</value></member>';
			}

			return '<array><data><value><struct>' . $xml . '</struct></value></data></array>';
		}
		else
		{
			// We don't send objects, so we didn't code for them, but in later versions we might add support for them
			return '<nil/>';
		}
	}

	/**
	|*	@func	build_xml
	|*	@desc	Builds the xml for use in send call.
	|*
	|*	@param	String	method	The method to use for the request.
	|*	@param	Array	params	{Defualt: Array()} Any extra parameters to send with the request.
	|*
	|*	@return	String	The newly created xml.
	**/
	protected function build_xml($method, $params = array())
	{
		$paramXML = '<member><name>key</name><value>' . $this->build_value_xml($this->key) . '</value></member>';

		if ($this->identifier)
		{
			$paramXML .= '<member><name>identifier</name><value>' . $this->build_value_xml($this->identifier) . '</value></member>';
		}

		foreach ($params AS $name => $value)
		{
			$paramXML .= '<member><name>' . $name . '</name><value>' . $this->build_value_xml($value) . '</value></member>';
		}

		return '<?xml version="1.0" encoding="UTF-8" ?>
			<methodCall>
				<methodName>' . $method . '</methodName>
				<params>
					<param>
						<value>
							<struct>' . $paramXML . '</struct>
						</value>
					</param>
				</params>
			</methodCall>
		';
	}

	/**
	|*	@func	send_call
	|*	@desc	Sends the API call.
	|*
	|*	@param	String	method	The method to use for the request.
	|*	@param	Array	params	{Defualt: Array()} Any extra parameters to send with the request.
	|*
	|*	@return	Mixed	FALSE on error, else the information recieved from the call.
	**/
	protected function send_call($method, $params = array())
	{
		// Build the xml to send
		$xml = $this->build_xml($method, $params);

		// Init connection
		$fp = @fsockopen('idealwebtech.com', 80);

		// Set headers
		$header  = "POST /interface/licenses.php HTTP/1.0\r\n";
		$header .= "Host: community.idealwebtech.com\r\n";
		$header .= "Connection: close\r\n";
		$header .= "Content-Type: text/xml\r\n";
		$header .= "Content-Length: " . strlen($xml) . "\r\n\r\n";

		// Send the XML
		fputs($fp, $header . $xml);

		// Grab the response
		$data = '';

		while(!feof($fp))
		{
			$data .= fgets($fp, 8192);
		}
		
		// Close connection
		fclose($fp);

		// Strip the headers
		$temp = explode("\r\n\r\n", $data, 2);

		// Parse the response
		$response = $this->parse_response($temp[1]);

		// Check for errors
		if ($response['faultCode'] || $response['faultString'])
		{
			$this->errorCode = $response['faultCode'];
			$this->errorString = $response['faultString'];

			return false;
		}
		else if (!$response)
		{
			$this->errorCode = '000';
			$this->errorString = 'UNKNOWN';

			return false;
		}

		return $response;
	}

	/**
	|*	@func	parse_response
	|*	@desc	Parses the response for the information contained in it.
	|*
	|*	@param	Mixed	response	The response to parse.
	|*
	|*	@return	Array	The information contained in the response.
	**/
	protected function parse_response($response)
	{
		if (is_string($response))
		{
			$response = simplexml_load_string($response);
			
			if ($response->params->param->value->struct->member)
			{
				$response = $response->params->param->value->struct->member;
			}
			else if ($response->fault->value->struct->member)
			{
				$response = $response->fault->value->struct->member;
			}
		}

		$temp = $response;
		$response = array();

		foreach ($temp AS $tmp)
		{
			$key = (string) $tmp->name;

			if (!empty($key))
			{
				$value = $tmp->value;

				if ($value->array)
				{
					$value = $value->array->data->value->struct;
					$value = $this->parse_response($value);
				}
				else if ($value->string)
				{
					$value = (string) $value->string;
					$unserialized = unserialize($value);

					if  ($unserialized || $value == "a:0:{}")
					{
						$value = $unserialized;
					}
				}
				else if ($value->int)
				{
					$value = (int) $value->int;
				}
				else if (empty($value))
				{
					$value = '';
				}

				$response[$key] = $value;
			}
		}

		return $response;
	}
}

/*
	Code  String			Description
	000	(UNKNOWN)			An unknown error occurred.
	001	(WRONG_PACKAGE)		The key specified doesn't match the specified product package.
	002	(EXPIRED)			The purchase associated with the license key has expired.
	003	(INACTIVE)			The license key, or purchase associated with the license key, has been deactivated.

	101 (BAD_KEY)			The key provided does not exist.
	102 (INACTIVE)			The key provided has been deactivated.
	103 (INACTIVE)			The purchase the key is associated with has been cancelled.
	104 (INACTIVE)			The purchase the key is associated with has expired.

	201 (MAX_USES)			The key has already been activated the maximum number of times and cannot be activated again.
	202 (ID_ALREADY_SET)	setIdentifier was TRUE, but the key already has an identifier.
	203 (BAD_ID)			The identifier provided was incorrect.

	301 (BAD_ID)			The identifier provided was incorrect.
	302 (NO_USAGE_ID)		You did not provide a usage ID.
	303 (BAD_USAGE_ID)		The usage ID provided was invalid.
	304 (BAD_IP)			The request was received by a different IP address to the IP address that sent the "activate" API call.

	401 (BAD_ID)			The identifier provided was incorrect.

	501 (BAD_ID)			The identifier provided was incorrect.
	502 (BAD_USAGE_ID)		The usage ID provided was invalid.
	503 (BAD_IP)			The request was received by a different IP address to the IP address that sent the "activate" API call.	
*/
?>