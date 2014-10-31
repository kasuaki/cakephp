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
	    $this->Auth->allow('login', 'logout');

		$this->OAuth->allow(array('login'));
	}

	public function login() {

	    if ($this->request->is('post')) {

	        if ($this->Auth->login()) {

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
//				$tokenResult = (array)json_decode(ob_get_clean());
				$tokenResult = ob_get_clean();

				// localStrageにsetItemしてからリダイレクト.
				$url = $this->Auth->redirectUrl();
				$this->setAction('sendAccessToken', $tokenResult, $url);

	        } else {
	            $this->Session->setFlash(__('Invalid username or password, try again'));
	        }
	    }
	}

	public function sendAccessToken($tokenResult = null, $url = '/') {

		$this->layout = false;
		$this->set("tokenResult", $tokenResult);
		$this->set("url", $url);
	}

	public function logout() {
	    $this->redirect($this->Auth->logout());
	}
}
