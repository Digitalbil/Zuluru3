<?php
namespace App\Model\Table;

use Cake\Datasource\EntityInterface;
use Cake\ORM\RulesChecker;

class DivisionsGameslotsTable extends AppTable {

	public function initialize(array $config) {
		parent::initialize($config);

		$this->table('divisions_gameslots');

		$this->belongsTo('Divisions');
		$this->belongsTo('GameSlots');
	}

	/**
	 * Returns a rules checker object that will be used for validating
	 * application integrity.
	 *
	 * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
	 * @return \Cake\ORM\RulesChecker
	 */
	public function buildRules(RulesChecker $rules) {
		$rules->add($rules->isUnique(['game_slot_id', 'division_id']));

		return $rules;
	}

}
