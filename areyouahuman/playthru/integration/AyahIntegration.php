<?php
namespace areyouahuman\playthru\integration;

/**
*
* @package VC
* @version 1.0.2
* @copyright (c) AreYouAHuman
* @license http://opensource.org/licenses/gpl-license.php GNU Public License, v2
*/


/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class AyahIntegration {

        var $ayah_web_service_host;
        var $ayah_publisher_key;
        var $ayah_scoring_key;

        public function __construct($params) {
                foreach (array_keys($params) as $key) {
                        if (in_array($key, array(
                                "ayah_web_service_host", "ayah_publisher_key", "ayah_scoring_key" 
                                                ))) {
                                $this->$key = $params[$key];
                        }
                }
        }

	// Generates and returns the HTML to insert into the form.
        function generateHTML() {
		$url = 'https://' . $this->ayah_web_service_host . "/ws/script/" . 
			urlencode($this->ayah_publisher_key);
			
		return "<div id='AYAH'></div><script src='". $url ."'></script>";
        }



        // This should be called and return true (pass) or false (failure)
	// Modeled on the ReCaptcha interaction functions for backwards compatibility and no cURL
        function scoreResult() {
		$result = false;
		$session_secret = request_var("session_secret", "baadbaadbaadbaad");

		$request = "session_secret=" . urlencode($session_secret) . "&scoring_key=" . 
			urlencode($this->ayah_scoring_key);


		// Build a header
		$http_request  = "POST /ws/scoreGame HTTP/1.1\r\n";
		$http_request .= "Host: " . $this->ayah_web_service_host . "\r\n";
		$http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
		$http_request .= "Content-Length: " . strlen($request) . "\r\n";
		$http_request .= "User-Agent: AreYouAHuman/PHP 1.0.1\r\n";
		$http_request .= "Connection: Close\r\n";
		$http_request .= "\r\n";
		$http_request .= $request;

		$response = '';
		$errno = $errstr = "";
		if( false == ( $fs = @fsockopen("ssl://" . $this->ayah_web_service_host, 443, $errno, $errstr, 10) ) ) {
		        error_log('Could not open socket');
			return false;
		}

		fwrite($fs, $http_request);

		while ( !feof($fs) ) {
		        $response .= fgets($fs, 1160); // One TCP-IP packet
		}
		fclose($fs);
		$response = explode("\r\n\r\n", $response, 2);

		$resp = null;		

		if ($response) {
			// We just want the one line
			$resp = $this->DecodeJSON($response[1]);
		}

		if ($resp) {
			$result = ($resp->status_code == 1);
		} 
		return $result;
	}

	// JSON decoder
	function DecodeJSON($string) {
		if (function_exists('json_decode')) {
			$string = json_decode($string);
		} else {
			$json = new Services_JSON();
			$string = $json->decode($string);
		}
		return $string;
	}
}



