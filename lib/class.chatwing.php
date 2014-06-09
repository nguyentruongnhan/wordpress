<?php

final class ChatWing {

	const VERSION = 1;

	private static $_api_urls = array(
		'development' => 'http://staging.chatwing.com/api/',
		'production' => 'http://chatwing.com/api/',
	);
	private static $_instance = false;
	private static $_apis = array(
		'list' => array(
			'method' => 'GET',
			'command' => '/user/chatbox/list',
			'default_params' => array(
				'limit' => 100,
				'offset' => 0,
			),
		),
	);

	private $token = '';
	private $environment = 'production';

	public static function getInstance($token = '') {
		if (!self::$_instance) {
			self::$_instance = new self($token);
		}

		return self::$_instance;
	}

	public function __construct($token = null) {
		self::$_instance = $this;
		if ( $token ) $this->token = $token;
	}

	public function __call($name, $arguments) {
		if ( !key_exists($name, self::$_apis) ) {
			throw new Exception('Not found API function', '404');
		}

		return $this->callApi($name, $arguments);
	}

	public function setEnvironment($environment) {
		$this->environment = $environment;
	}

	public function setToken($token) {
		$this->token = $token;
	}

	private function getApiUrl($command, $default_params = array()) {

		if ( !isset(self::$_api_urls[$this->environment]) ) {
			throw new Exception(sprintf('Sorry! ChatWing API url not found for environment "%s"', $this->environment), 500);
		}

		$params = $default_params;
		$params['access_token'] = $this->token; // access token is need for every requests

		$url = rtrim(self::$_api_urls[$this->environment], '/') . '/' . self::VERSION . $command;
		$url = $url . '?' . http_build_query($params);

		return $url;
	}

	private function callApi($name, $arguments = array()) {
		$command = self::$_apis[$name];
		$method = $command['method'];

		$api_url = $this->getApiUrl($command['command']);

		$response = $this->request($api_url, $arguments, $method);

		if ( isset($response['error']) && $response['error'] ) {
			return array();
		}

		return $response['data'];
	}


	private function request($url, $params = array(), $method = 'GET') {
		$ch = curl_init();

		if ( $method === 'POST' && !empty($params) ) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		} elseif ( $method === 'GET' ) {
			$url .= '&' . http_build_query($params);
		}

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

		$result = curl_exec($ch);

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ( $status != 200 ) {
			return array('data' => array(), 'error' => true, 'error_code' => $status);
		}

		$response = json_decode($result, true);

		return is_array($response) ? $response : array('data' => array(), 'error' => true, 'error_code' => 500);
	}
}