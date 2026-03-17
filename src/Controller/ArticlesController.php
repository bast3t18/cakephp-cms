<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

class ArticlesController extends AppController
{
    public function index(): void
    {
        $this->Authorization->skipAuthorization();

        $articles = $this->paginate($this->Articles);
        $this->set(compact('articles'));
    }

    public function view(?string $slug): void
    {
        $this->Authorization->skipAuthorization();

        $article = $this->Articles
            ->findBySlug($slug)
            ->contain('Tags')
            ->firstOrFail();

        $this->set(compact('article'));
    }

    public function add()
    {
        $article = $this->Articles->newEmptyEntity();
        $this->Authorization->authorize($article);

        if ($this->request->is('post')) {
            $article = $this->Articles->patchEntity($article, $this->request->getData());

            // Asignar usuario logueado
            $article->user_id = $this->request->getAttribute('identity')->getIdentifier();

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been saved.'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Unable to add your article.'));
        }

        $tags = $this->Articles->Tags->find('list')->all();
        $this->set(compact('article', 'tags'));
    }

    public function edit(?string $slug)
    {
        $article = $this->Articles
            ->findBySlug($slug)
            ->contain('Tags')
            ->firstOrFail();

        $this->Authorization->authorize($article);

        if ($this->request->is(['post', 'put'])) {
            $this->Articles->patchEntity($article, $this->request->getData(), [
                'accessibleFields' => ['user_id' => false],
            ]);

            if ($this->Articles->save($article)) {
                $this->Flash->success(__('Your article has been updated.'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Unable to update your article.'));
        }

        $tags = $this->Articles->Tags->find('list')->all();
        $this->set(compact('article', 'tags'));
    }

    public function delete(?string $slug)
    {
        $this->request->allowMethod(['post', 'delete']);

        $article = $this->Articles->findBySlug($slug)->firstOrFail();
        $this->Authorization->authorize($article);

        if ($this->Articles->delete($article)) {
            $this->Flash->success(__('The {0} article has been deleted.', $article->title));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function tags()
    {
        $this->Authorization->skipAuthorization();

        $tags = $this->request->getParam('pass');

        $articles = $this->Articles->find('tagged', tags: $tags)->all();

        $this->set([
            'articles' => $articles,
            'tags' => $tags,
        ]);
    }
}