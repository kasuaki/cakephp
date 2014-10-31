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

}
