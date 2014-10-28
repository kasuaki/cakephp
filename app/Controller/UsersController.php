<?php
App::uses('AppController', 'Controller');

/**
 * Users Controller
 *
 */
class UsersController extends AppController {

	public $components = array('RequestHandler');

	public function beforeFilter() {
	    parent::beforeFilter();

		if (!$this->request->is('ajax')) throw new BadRequestException('Ajax以外でのアクセスは許可されていません。');
        $this->response->header('X-Content-Type-Options', 'nosniff');


//	    $this->Auth->allow('index', 'view');

//		$this->OAuth->allow(array('index', 'view'));

	}

    public function index() {
        $users = $this->User->find('all');
        $users = Hash::extract($users,  '{n}.User');
        foreach($users as $key => $value) {
	        $this->set(array($key => $value));
	        $serialize[] = $key;
        }
		$this->set(array('_serialize' => $serialize));
    }

    public function view($id = null) {
        $user = $this->User->findById($id);
        $user = Hash::extract($user,  'User');
        
        $serialize = array();
        foreach($user as $key => $value) {
	        $this->set(array($key => $value));
	        $serialize[] = $key;
        }
		$this->set(array('_serialize' => $serialize));
    }

    public function add() {
        if ($this->request->is('post')) {

			$serialize = array();
	        $this->User->create();
	        if ($this->User->save($this->request->data)) {
	            $message = 'Saved';

			    $user = $this->User->findById($this->User->id);
			    $user = Hash::extract($user,  'User');

			    foreach($user as $key => $value) {
				    $this->set(array($key => $value));
				    $serialize[] = $key;
			    }
	        } else {
	            $message = 'Error';
	            $message .= ':' . $this->User->validationErrors;
	        }

	        $this->set(array('message'    => $message));
			$serialize[] = "message";

	        $this->set(array('_serialize' => $serialize));
        }
    }

    public function edit($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }

        if ($this->request->is('post') || $this->request->is('put')) {

	        $this->User->id = $id;
	        if ($this->User->save($this->request->data)) {
	            $message = 'Saved';
	        } else {
	            $message = 'Error';
	        }

	        $this->set(array(
	            'message' => $message,
	            '_serialize' => array('message')
	        ));
        } else {
            $this->request->data = $this->User->read(null, $id);
            unset($this->request->data['User']['password']);
        }
    }

    public function delete($id = null) {

        $this->request->onlyAllow('delete');

        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }

        if ($this->User->delete($id)) {
            $message = 'Deleted';
        } else {
            $message = 'Error';
        }
        $this->set(array(
            'message' => $message,
            '_serialize' => array('message')
        ));
    }
}
