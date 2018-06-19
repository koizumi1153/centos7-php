<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AqoursScraping Model
 *
 * @method \App\Model\Entity\AqoursScraping get($primaryKey, $options = [])
 * @method \App\Model\Entity\AqoursScraping newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AqoursScraping[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AqoursScraping|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AqoursScraping patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AqoursScraping[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AqoursScraping findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AqoursScrapingTable extends Table
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

        $this->setTable('aqours_scraping');
        $this->setDisplayField('title');
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
            ->scalar('url')
            ->allowEmpty('url');

        $validator
            ->scalar('title')
            ->allowEmpty('title');

        $validator
            ->integer('kind')
            ->allowEmpty('kind');

        $validator
            ->allowEmpty('link_flg');

        $validator
            ->dateTime('deleted')
            ->allowEmpty('deleted');

        return $validator;
    }
}
