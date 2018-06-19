<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class AqoursLiveShopTable extends Table
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

        $this->setTable('aqours_live_shop');
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
            ->allowEmpty('title')
            ->add('title', [
              'maxLength' => [
                'rule' => ['maxLength', 200],
                'message' => '200文字以内で設定してください'
              ]
            ]);

        $validator
            ->requirePresence('date', 'create')
            ->notEmpty('date');

        $validator
            ->requirePresence('start_date', 'create')
            ->notEmpty('start_date');

        $validator
            ->requirePresence('end_date', 'create')
            ->notEmpty('end_date');

        $validator
            ->requirePresence('screen_name', 'create')
            ->allowEmpty('screen_name')
            ->add('screen_name', [
              'maxLength' => [
                'rule' => ['maxLength', 100],
                'message' => '100文字以内で設定してください'
              ]
            ]);

        $validator
            ->dateTime('deleted')
            ->allowEmpty('deleted');

        return $validator;
    }
}
