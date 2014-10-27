<?php
App::uses('AppController', 'Controller');

/**
 * Logins Controller
 *
 */
class LoginsController extends AppController {

	public $name = 'Logins';
	public $uses = array('User', 'ExtendsClient');

	public function beforeFilter() {
	    parent::beforeFilter();
	    // ユーザー自身による登録とログアウトを許可する
	    $this->Auth->allow('login', 'logout');

		$this->OAuth->allow(array('login'));
	}

	public function login() {

	    if ($this->request->is('post')) {

	        if ($this->Auth->login()) {

				// user_idに連動してレコードを一緒に削除.
				$this->OAuth->Client->hasMany['AccessToken']['dependent'] = true;
				$this->OAuth->Client->hasMany['AuthCode']['dependent'] = true;
				$this->OAuth->Client->hasMany['RefreshToken']['dependent'] = true;

				$this->OAuth->Client->deleteAll(array('user_id' => $this->Auth->user('id')), true/* cascade */);

			    $client = $this->OAuth->Client->add(array("Client" => array(
			    	"redirect_uri" => "http://localhost/dummy",
			    	"user_id" => $this->Auth->user('id'),
			    )));

//				var_dump($this->OAuth->getClientDetails($client['Client']['client_id']));
//				var_dump($this->OAuth->checkClientCredentials($client['Client']['client_id']));

				 $authCodeParams = array("response_type" => "code",
										 "client_id" => $client['Client']['client_id'],
										 "redirect_uri" => "http://localhost/dummy");

				list($redirect_uri, $result) = $this->OAuth->OAuth2->getAuthResult(true, $this->Auth->user('id'), $authCodeParams);

				$authCode = Hash::get($result, 'query.code');

				$tokenParams = array(
					"grant_type" => "authorization_code",
					"scope" => "",
					"code" => $authCode,
					"redirect_uri" => "http://localhost/dummy",
					"client_id" => $client['Client']['client_id'],
					"client_secret" => $client['Client']['client_secret'],
					"refresh_token" => "",
				);

				$authHeaders = array("PHP_AUTH_USER" => "", "PHP_AUTH_PW" => "");

				// 30秒以内に実施しないとエラーになる.
				ob_start();
				$this->OAuth->OAuth2->grantAccessToken($tokenParams);
				$tokenResult = (array)json_decode(ob_get_clean());

				$url = $this->Auth->redirect();
	            $this->redirect($url . "/" . $tokenResult['access_token']);

	        } else {
	            $this->Session->setFlash(__('Invalid username or password, try again'));
	        }
	    }
	}

	public function logout() {
	    $this->redirect($this->Auth->logout());
	}
}
