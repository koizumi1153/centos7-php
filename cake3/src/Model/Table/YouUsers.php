<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class YouUsers extends Table
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

    $this->setTable('you_users');
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
      ->requirePresence('user_id', 'create')
      ->notEmpty('user_id');

    $validator
      ->requirePresence('name', 'create')
      ->allowEmpty('name');

    $validator
      ->requirePresence('push_flg', 'create')
      ->notEmpty('push_flg');

    $validator
      ->requirePresence('push_time', 'create')
      ->allowEmpty('push_time');

    $validator
      ->requirePresence('latitude', 'create')
      ->allowEmpty('latitude');

    $validator
      ->requirePresence('longitude', 'create')
      ->allowEmpty('longitude');

    $validator
      ->dateTime('deleted')
      ->allowEmpty('deleted');

    return $validator;
  }
}