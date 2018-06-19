<?php
namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * AqoursScrapingData Model
 *
 * @property \App\Model\Table\ScrapingsTable|\Cake\ORM\Association\BelongsTo $Scrapings
 *
 * @method \App\Model\Entity\AqoursScrapingData get($primaryKey, $options = [])
 * @method \App\Model\Entity\AqoursScrapingData newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\AqoursScrapingData[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AqoursScrapingData|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AqoursScrapingData patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AqoursScrapingData[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\AqoursScrapingData findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class AqoursScrapingDataTable extends Table
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

        $this->setTable('aqours_scraping_data');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Scrapings', [
            'foreignKey' => 'scraping_id',
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
            ->scalar('url')
            ->allowEmpty('url');

        $validator
            ->scalar('title')
            ->allowEmpty('title');

        $validator
            ->scalar('contents_data')
            ->allowEmpty('contents_data');

        $validator
            ->integer('link_num')
            ->allowEmpty('link_num');

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
        $rules->add($rules->existsIn(['scraping_id'], 'Scrapings'));

        return $rules;
    }
}
