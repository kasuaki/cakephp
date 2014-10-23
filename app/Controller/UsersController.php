<?php
App::uses('AppController', 'Controller');
App::uses( 'HttpSocket', 'Network/Http');

/**
 * Users Controller
 *
 */
class UsersController extends AppController {

/**
 * Scaffold
 *
 * @var mixed
 */
	public $scaffold;

	public function opauthComplete() {
        $this->autoRender = false;
        debug($this->data);
	}

	public function beforeFilter() {
	    parent::beforeFilter();
	    // ユーザー自身による登録とログアウトを許可する
	    $this->Auth->allow('add', 'logout');

		$this->OAuth->allow(array('login', 'logout', 'index', 'auth_callback'));

//	    if($this->params['action'] == 'opauthComplete') {
//	        $this->Security->csrfCheck = false;
//	        $this->Security->validatePost = false;
//	    }
	}

	public function login() {
	    if ($this->request->is('post')) {
	    	$client = $this->OAuth->Client->add(array("Client" => array(
																	"client_id" => "",
																	"client_secret" => "",
																	"redirect_uri" => "http://localhost/users/auth_callback",
																	"expires" => time() + 3600,
																)
													)
			);
			debug($client);
			$this->Cookie->write('client_id', $client['Client']['client_id']);
			$this->Cookie->write('client_secret', $client['Client']['client_secret']);
			$this->redirect(array('controller' => 'oauth', 'action' => 'authorize', '?' => array('response_type' => 'code', 'client_id' => $client['Client']['client_id'], 'redirect_url' => 'http://localhost/users/auth_callback')));
//	        if ($this->Auth->login()) {
//	            $this->redirect($this->Auth->redirect());
//	        } else {
//	            $this->Session->setFlash(__('Invalid username or password, try again'));
//	        }
	    }
	}

	public function auth_callback2() {
		debug($this->request->query);
		Hash::get($this->request->query, 'access_token');
	}

	public function auth_callback() {

		debug($this->request->query);
		$this->autoRender = false;
		$sock = new HttpSocket(array( 'ssl_verify_host' => false));
		$this->redirect(array('controller' => 'oauth', 'action' => 'token', 
	   		'?' => array('grant_type' => 'authorization_code', 
	   					 'code' => Hash::get($this->request->query, 'code'), 
	   					 'client_id' => $this->Cookie->read('client_id'), 
	   					 'client_secret' => $this->Cookie->read('client_secret'),
	   					 'redirect_uri' => 'http://localhost/users/auth_callback2',
	   					 )));
	}
	public function logout() {
	    $this->redirect($this->Auth->logout());
	}

    public function index() {
        $this->User->recursive = 0;
        $this->set('users', $this->paginate());
    }

    public function view($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        $this->set('user', $this->User->read(null, $id));
    }

    public function add() {
        if ($this->request->is('post')) {
            $this->User->create();
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        }
    }

    public function edit($id = null) {
        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            if ($this->User->save($this->request->data)) {
                $this->Session->setFlash(__('The user has been saved'));
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The user could not be saved. Please, try again.'));
            }
        } else {
            $this->request->data = $this->User->read(null, $id);
            unset($this->request->data['User']['password']);
        }
    }

    public function delete($id = null) {
        $this->request->onlyAllow('post');

        $this->User->id = $id;
        if (!$this->User->exists()) {
            throw new NotFoundException(__('Invalid user'));
        }
        if ($this->User->delete()) {
            $this->Session->setFlash(__('User deleted'));
            $this->redirect(array('action' => 'index'));
        }
        $this->Session->setFlash(__('User was not deleted'));
        $this->redirect(array('action' => 'index'));
    }
}
