<?php
App::uses('AppController', 'Controller');
App::uses( 'HttpSocket', 'Network/Http');

/**
 * Users Controller
 *
 */
class UsersController extends AppController {

	public $components = array('RequestHandler');

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
	    $this->Auth->allow('logout', 'auth_callback', 'auth_callback2', 'add');

		$this->OAuth->allow(array('login', 'logout', 'auth_callback', 'auth_callback2'));

//		if (!$this->request->is('ajax')) throw new BadRequestException('Ajax以外でのアクセスは許可されていません。');
        $this->response->header('X-Content-Type-Options', 'nosniff');

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
			$this->redirect(array('controller' => 'oauth', 'action' => 'authorize', 
							'?' => array('response_type' => 'code', 
										'client_id' => $client['Client']['client_id'], 
										'redirect_url' => 'http://localhost/users/auth_callback')));

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
        $users = $this->User->find('all');
        $this->set(array(
            'users' => $users,
        ));
    }

    public function view($id = null) {
        $user = $this->User->findById($id);
        $this->set(array(
            'user' => $user,
            '_serialize' => array('user')
        ));
    }

    public function add() {
        if ($this->request->is('post')) {

	        $this->User->create();
	        if ($this->User->save($this->request->data)) {
	            $message = 'Saved';
	        } else {
	            $message = 'Error';
	            $message .= ':' . $this->User->validationErrors;
	        }

	        $this->set(array(
	            'message'    => $message,
	            '_serialize' => array('message')
	        ));
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
