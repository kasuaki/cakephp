<?php
App::uses('AppController', 'Controller');

/**
 * Logins Controller
 *
 */
class LoginsController extends AppController {

	public $name = 'Logins';
	public $uses = array('User');

	public function beforeFilter() {
	    parent::beforeFilter();
	    // ユーザー自身による登録とログアウトを許可する
	    $this->Auth->allow('login', 'logout');

		$this->OAuth->allow(array('login'));
	}

	public function login() {

		$this->autoRender = false;
	    	$client = $this->OAuth->Client->add(array("Client" => array(
																	"client_id" => "",
																	"client_secret" => "",
																	"redirect_uri" => "http://localhost/logins/auth_callback",
																	"expires" => time() + 36000,
																)
													)
			);

			var_dump($this->OAuth->getClientDetails($client['Client']['client_id']));
			var_dump($this->OAuth->checkClientCredentials($client['Client']['client_id']));

		 $authCodeParams = array("response_type" => "code",
	 				 "client_id" => $client['Client']['client_id'],
	 				 "redirect_uri" => "http://localhost/logins/auth_callback");

			list($redirect_uri, $result) = $this->OAuth->OAuth2->getAuthResult(true, 1/* $this->Auth->user('id') */, $authCodeParams);

			$authCode = Hash::get($result, 'query.code');

		$tokenParams = array(
			"grant_type" => "authorization_code",
			"scope" => "",
			"code" => $authCode,
			"redirect_uri" => "http://localhost/logins/auth_callback2",
			"client_id" => $client['Client']['client_id'],
			"client_secret" => $client['Client']['client_secret'],
			"refresh_token" => "",
		);

		ob_start();
		$this->OAuth->OAuth2->grantAccessToken($tokenParams);
		$json = json_decode(ob_get_clean());
		var_dump($json);
	}

	public function logout() {
	    $this->redirect($this->Auth->logout());
	}
}
