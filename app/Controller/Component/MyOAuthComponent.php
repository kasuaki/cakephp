<?php

/**
 * CakePHP OAuth Server Plugin
 *
 * This is the main component.
 *
 * It provides:
 *	- Cakey interface to the OAuth2-php library
 *	- AuthComponent like action allow/deny's
 *	- Easy access to user associated to an access token
 *	- More!?
 *
 * @author Thom Seddon <thom@seddonmedia.co.uk>
 * @see https://github.com/thomseddon/cakephp-oauth-server
 *
 */

App::uses('OAuthComponent', 'Plugin/OAuth/Controller/Component');

class MyOAuthComponent extends OAuthComponent {

/**
 * A URL (defined as a string or array) to the controller action that handles
 * logins. Defaults to `/users/login`.
 *
 * @var mixed
 */
	public $loginAction = array(
		'controller' => 'logins',
		'action' => 'index',
		'plugin' => null
	);

/**
 * Constructor - Adds class associations
 *
 * @see OAuth2::__construct().
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		$this->User = ClassRegistry::init(array('class' => 'User', 'alias' => 'User'));
	}

/**
 * Check client details are valid
 *
 * @see IOAuth2Storage::checkClientCredentials().
 *
 * @param string $client_id
 * @param string $client_secret
 * @return mixed array of client credentials if valid, false if not
 */
	public function checkClientCredentials($username, $password = null) {
		$conditions = array('username' => $username);
		if ($password) {
			$conditions['password'] = $password;
		}
		$user = $this->User->find('first', array(
			'fields' => array('username', 'password'),
			'conditions' => $conditions,
			'recursive' => -1
		));
		if ($user) {
			return $user['User'];
		};
		return false;
	}

/**
 * Grant type: user_credentials
 *
 * @see IOAuth2GrantUser::checkUserCredentials()
 *
 * @param type $client_id
 * @param type $username
 * @param type $password
 */
	public function checkUserCredentials($client_id, $username, $password) {
		$user = $this->User->find('first', array(
			'fields' => array('id', 'username', 'password'),
			'conditions' => array(
				$this->authenticate['fields']['username'] => $username,
				$this->authenticate['fields']['password'] => $password,
			),
			'recursive' => -1
		));
		if ($user) {
			return array('user_id' => $user['User']['id']);
		}
		return false;
	}

/**
 * Main engine that checks valid access_token and stores the associated user for retrival
 *
 * @see AuthComponent::startup()
 *
 * @param type $controller
 * @return boolean
 */
	public function startup(Controller $controller) {
		$methods = array_flip(array_map('strtolower', $controller->methods));
		$action = strtolower($controller->request->params['action']);

		$this->authenticate = Hash::merge($this->_authDefaults, $this->authenticate);
		$this->User = ClassRegistry::init(array(
			'class' => $this->authenticate['userModel'],
			'alias' => $this->authenticate['userModel']
			));

		$isMissingAction = (
			$controller->scaffold === false &&
			!isset($methods[$action])
		);
		if ($isMissingAction) {
			return true;
		}

		$allowedActions = $this->allowedActions;
		$isAllowed = (
			$this->allowedActions == array('*') ||
			in_array($action, array_map('strtolower', $allowedActions))
		);
		if ($isAllowed) {
			return true;
		}

		try {
			$result = $this->isAuthorized();
			if ($result == false) {

				$controller->redirect($this->loginAction);
				return false;
			}
			$this->user(null, $this->AccessToken->id);
		} catch (OAuth2AuthenticateException $e) {
			return false;
		}
		return true;
	}

/**
 * Fakes the OAuth2.php vendor class extension for methods
 *
 * @param string $name
 * @param mixed $arguments
 * @return mixed
 * @throws Exception
 */
	public function __call($name, $arguments) {
		if (method_exists($this->OAuth2, $name)) {
			try {
				return call_user_func_array(array($this->OAuth2, $name), $arguments);
			} catch (Exception $e) {
				throw $e;
			}
		}
	}
}
