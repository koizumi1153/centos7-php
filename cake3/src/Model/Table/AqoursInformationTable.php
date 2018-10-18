<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Users Model
 *
 * @method \App\Model\Entity\User get($primaryKey, $options = [])
 * @method \App\Model\Entity\User newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\User[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\User|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\User patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\User[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\User findOrCreate($search, callable $callback = null)
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AqoursInformationTable extends Table
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

        $this->setTable('aqours_information');
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
            ->integer('kind')
            ->notEmpty('kind');

        $validator
            ->requirePresence('title', 'create')
            ->notEmpty('title');

        $validator
            ->integer('price')
            ->allowEmpty('price');

        $validator
            ->requirePresence('jan', 'create')
            ->allowEmpty('jan');

        $validator
            ->requirePresence('discription', 'create')
            ->allowEmpty('discription');

        $validator
            ->requirePresence('img', 'create')
            ->allowEmpty('img');

        $validator
            ->requirePresence('date', 'create')
            ->allowEmpty('date');

        $validator
            ->date('push_date', ['ymd'])
            ->allowEmpty('push_date');

        $validator
            ->integer('push')
            ->allowEmpty('push');

        $validator
          ->requirePresence('member_ids')
          ->allowEmpty('member_ids', 'create');


        $validator
              ->dateTime('deleted')
              ->allowEmpty('deleted');

        return $validator;
    }
}
