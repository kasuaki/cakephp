<?php
App::uses('AppController', 'Controller');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');

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
	    $this->Auth->allow('index', 'login');

		$this->OAuth->allow(array('login', 'index'));
	}

	public function index() {
	}

	public function login() {

		$this->layout = 'ajax';

		if ($this->request->is('ajax')) {

	        if ($this->Auth->login()) {

				try {

					$passwordHasher = new SimplePasswordHasher();
					$pass = $passwordHasher->hash($this->request->data('User.password'));

					// 前回までのaccess_token及びrefresh_tokenを削除.
					$this->OAuth->AccessToken->deleteAll(array('user_id' => $this->Auth->user('id')), false/* cascade */);
					$this->OAuth->RefreshToken->deleteAll(array('user_id' => $this->Auth->user('id')), false/* cascade */);

					// accessToken発行.
					$tokenParams = array(
						"grant_type" => "password",
						"username" => $this->request->data('User.username'),
						"password" => $pass,
					);

					$authHeaders = array("PHP_AUTH_USER" => $this->request->data('User.username'), 
										 "PHP_AUTH_PW" => $pass);

					ob_start();
					$this->OAuth->OAuth2->grantAccessToken($tokenParams, $authHeaders);
					$tokenResult = (array)json_decode(ob_get_clean());
					$tokenResult["result"] = "success";
					$tokenResult["accessToken"] = $tokenResult["access_token"];
				} catch(Exception $e) {

					$tokenResult["result"] = "error";
				}

				return new CakeResponse(array('body' => json_encode($tokenResult)));
	        } else {
				return new CakeResponse(array('body' => json_encode(array("result" => "error"))));
	        }
	    }
	}
}
