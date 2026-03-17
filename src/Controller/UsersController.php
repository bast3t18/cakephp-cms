<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['login', 'add']);
    }

    public function login()
    {
        $this->Authorization->skipAuthorization();

        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? [
                'controller' => 'Articles',
                'action' => 'index',
            ];

            return $this->redirect($target);
        }

        if ($this->request->is('post')) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

    public function logout()
    {
        $this->Authorization->skipAuthorization();

        $this->Authentication->logout();

        return $this->redirect([
            'controller' => 'Users',
            'action' => 'login',
        ]);
    }

    public function index()
    {
        $query = $this->Users->find();
        $users = $this->paginate($query);

        $this->set(compact('users'));
    }

    public function view($id = null)
    {
        $user = $this->Users->get($id, contain: ['Articles']);
        $this->set(compact('user'));
    }

    public function add()
    {
        $this->Authorization->skipAuthorization();

        $user = $this->Users->newEmptyEntity();

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }

        $this->set(compact('user'));
    }

    public function edit($id = null)
    {
        $user = $this->Users->get($id, contain: []);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }

        $this->set(compact('user'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);

        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}