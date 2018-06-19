<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class YohaneMaps extends Table
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

    $this->setTable('yohane_maps');
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
      ->requirePresence('name', 'create')
      ->notEmpty('name');

    $validator
      ->requirePresence('description', 'create')
      ->allowEmpty('description');

    $validator
      ->requirePresence('address', 'create')
      ->allowEmpty('address');

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