<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TwitterBotBase Model
 *
 * @method \App\Model\Entity\TwitterBotBase get($primaryKey, $options = [])
 * @method \App\Model\Entity\TwitterBotBase newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TwitterBotBase[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TwitterBotBase|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TwitterBotBase patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TwitterBotBase[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TwitterBotBase findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TwitterBotBaseTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('twitter_bot_base');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('consumer_key')
            ->notEmpty('consumer_key');

        $validator
            ->scalar('consumer_secret')
            ->notEmpty('consumer_secret');

        $validator
            ->scalar('api_token')
            ->allowEmpty('api_token');

        $validator
            ->scalar('api_token_secret')
            ->allowEmpty('api_token_secret');

        $validator
            ->scalar('url')
            ->requirePresence('url', 'create')
            ->allowEmpty('url');

        $validator
            ->scalar('word')
            ->maxLength('word', 140)
            ->requirePresence('word', 'create')
            ->allowEmpty('word');

        $validator
            ->scalar('img')
            ->maxLength('img', 50)
            ->requirePresence('img', 'create')
            ->allowEmpty('img');

        $validator
            ->scalar('screen_name')
            ->maxLength('screen_name', 50)
            ->requirePresence('screen_name', 'create')
            ->allowEmpty('screen_name');

        $validator
            ->dateTime('deleted')
            ->allowEmpty('deleted');

        return $validator;
    }
}
