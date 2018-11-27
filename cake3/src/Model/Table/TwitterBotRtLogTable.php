<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TwitterBotRtLog Model
 *
 * @property \App\Model\Table\BasesTable|\Cake\ORM\Association\BelongsTo $Bases
 *
 * @method \App\Model\Entity\TwitterBotRtLog get($primaryKey, $options = [])
 * @method \App\Model\Entity\TwitterBotRtLog newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TwitterBotRtLog[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TwitterBotRtLog|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TwitterBotRtLog patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TwitterBotRtLog[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TwitterBotRtLog findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TwitterBotRtLogTable extends Table
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

        $this->setTable('twitter_bot_rt_log');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('TwitterBotBase', [
            'foreignKey' => 'base_id',
            'joinType' => 'INNER'
        ]);
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
            ->scalar('tweet_id')
            ->maxLength('tweet_id', 140)
            ->requirePresence('tweet_id', 'create')
            ->notEmpty('tweet_id');

        $validator
            ->dateTime('deleted')
            ->allowEmpty('deleted');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['base_id'], 'TwitterBotBase'));

        return $rules;
    }
}
