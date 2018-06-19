<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;


class YohaneFortunes extends Table
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

    $this->setTable('yohane_fortunes');
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
      ->requirePresence('title', 'create')
      ->notEmpty('title');

    $validator
      ->requirePresence('description', 'create')
      ->notEmpty('description');

    $validator
      ->requirePresence('img', 'create')
      ->allowEmpty('img');

    $validator
      ->requirePresence('preview', 'create')
      ->allowEmpty('preview');

    $validator
      ->dateTime('deleted')
      ->allowEmpty('deleted');

    return $validator;
  }
}