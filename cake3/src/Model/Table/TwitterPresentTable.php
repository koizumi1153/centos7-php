<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * TwitterPresent Model
 *
 * @property \App\Model\Table\BasesTable|\Cake\ORM\Association\BelongsTo $Bases
 *
 * @method \App\Model\Entity\TwitterPresent get($primaryKey, $options = [])
 * @method \App\Model\Entity\TwitterPresent newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\TwitterPresent[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\TwitterPresent|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\TwitterPresent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\TwitterPresent[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\TwitterPresent findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class TwitterPresentTable extends Table
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

        $this->setTable('twitter_present');
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
            ->scalar('screen_name')
            ->maxLength('screen_name', 256)
            ->notEmpty('screen_name');

        $validator
            ->scalar('word')
            ->maxLength('word', 256)
            ->notEmpty('word');

        $validator
            ->dateTime('start_date')
            ->requirePresence('start_date', 'create')
            ->notEmpty('start_date');

        $validator
            ->dateTime('end_date')
            ->requirePresence('end_date', 'create')
            ->notEmpty('end_date');

        $validator
            ->integer('kind')
            ->notEmpty('kind');

        $validator
            ->dateTime('deleted')
            ->allowEmpty('deleted');

        return $validator;
    }
}
