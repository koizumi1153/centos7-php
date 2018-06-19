<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class AqoursUserLiveNumberTable extends Table
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

        $this->setTable('aqours_user_live_number');
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
          ->requirePresence('shop_id', 'create')
          ->notEmpty('shop_id');

        $validator
            ->requirePresence('user_id', 'create')
            ->notEmpty('user_id');

        $validator
            ->integer('number')
            ->requirePresence('number', 'create')
            ->notEmpty('number','数字を入力してください')
            ->naturalNumber('number', __('数字を入力してください'));

        $validator
            ->requirePresence('date', 'create')
            ->notEmpty('date');

        $validator
            ->dateTime('deleted')
            ->allowEmpty('deleted');

        return $validator;
    }
}
