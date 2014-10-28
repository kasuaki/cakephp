<?php
App::uses('AppModel', 'Model');
App::uses('SimplePasswordHasher', 'Controller/Component/Auth');
/**
 * User Model
 *
 * @property Client $Client
 */
class User extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed
	public $virtualFields = array(
			'client_id' => '"Client".client_id',
			'client_secret' => '"Client".client_secret',
			'redirect_uri' => '"Client".redirect_uri',
			'user_id' => '"Client".user_id',
	);

	public $recursive = 2;
/**
 * hasOne associations
 *
 * @var array
 */
	public $hasOne = array(
		'Client' => array(
			'className' => 'Client',
			'foreignKey' => 'user_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

	public function afterFind($results, $primary = false) {

		return $results;
	}

    public $validate = array(
        'username' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'A username is required'
            )
        ),
        'password' => array(
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'A password is required'
            )
        ),
        'role' => array(
            'valid' => array(
                'rule' => array('inList', array('admin', 'author')),
                'message' => 'Please enter a valid role',
                'allowEmpty' => false
            )
        )
    );

/**
 * Validate前処理.
 *
 * @param 
 * @return 
 */
	public function beforeValidate($options = array() ) {
	}

/**
 * エラー時の処理.
 *
 * @param 
 * @return 
 */
	public function onError() {

		echo debug($this->User->getDataSource()->getLog());
	}

	public function beforeSave($options = array()) {
	    if (isset($this->data[$this->alias]['password'])) {
	        $passwordHasher = new SimplePasswordHasher();
	        $this->data[$this->alias]['password'] = $passwordHasher->hash($this->data[$this->alias]['password']);
	    }
	    return true;
	}
}
