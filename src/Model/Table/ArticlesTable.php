<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Event\EventInterface;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;
use Cake\Utility\Text;
use Cake\Validation\Validator;

class ArticlesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('articles');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsToMany('Tags', [
            'joinTable' => 'articles_tags',
            'dependent' => true,
        ]);
    }

    public function beforeSave(EventInterface $event, $entity, $options): void
    {
        if ($entity->tag_string) {
            $entity->tags = $this->_buildTags($entity->tag_string);
        }

        if ($entity->isNew() && !$entity->slug) {
            $sluggedTitle = Text::slug($entity->title);
            $entity->slug = substr($sluggedTitle, 0, 191);
        }
    }

    protected function _buildTags(string $tagString): array
    {
        $newTags = array_map('trim', explode(',', $tagString));
        $newTags = array_filter($newTags);
        $newTags = array_unique($newTags);

        $out = [];
        $tags = $this->Tags->find()
            ->where(['Tags.title IN' => $newTags])
            ->all();

        foreach ($tags->extract('title') as $existing) {
            $index = array_search($existing, $newTags, true);
            if ($index !== false) {
                unset($newTags[$index]);
            }
        }

        foreach ($tags as $tag) {
            $out[] = $tag;
        }

        foreach ($newTags as $tag) {
            $out[] = $this->Tags->newEntity(['title' => $tag]);
        }

        return $out;
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->notEmptyString('title')
            ->minLength('title', 10)
            ->maxLength('title', 255)
            ->notEmptyString('body')
            ->minLength('body', 10);

        return $validator;
    }

    public function findTagged(SelectQuery $query, array $tags = []): SelectQuery
    {
        $columns = [
            'Articles.id',
            'Articles.user_id',
            'Articles.title',
            'Articles.body',
            'Articles.published',
            'Articles.created',
            'Articles.slug',
        ];

        $query = $query
            ->select($columns)
            ->distinct($columns);

        if (!$tags) {
            $query->leftJoinWith('Tags')
                ->where(['Tags.title IS' => null]);
        } else {
            $query->innerJoinWith('Tags')
                ->where(['Tags.title IN' => $tags]);
        }

        return $query->groupBy(['Articles.id']);
    }
}