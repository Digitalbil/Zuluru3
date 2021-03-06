<?php
use Cake\Core\Configure;

$this->Html->addCrumb(__('Game Slot'));
$this->Html->addCrumb(__('Edit'));
?>

<div class="gameSlots form">
	<?= $this->Form->create($game_slot, ['align' => 'horizontal']) ?>
	<fieldset>
		<legend><?= __('Edit Game Slot') ?></legend>
<?php
echo $this->Form->hidden('sport', ['id' => 'sport', 'value' => $game_slot->field->sport]);
echo $this->Jquery->ajaxInput('game_date', [
	'selector' => '#DivisionList',
	'url' => ['controller' => 'Divisions', 'action' => 'select', 'affiliate' => $affiliate],
	'additional-inputs' => '#sport',
], [
	'minYear' => Configure::read('options.year.gameslot.min'),
	'maxYear' => Configure::read('options.year.gameslot.max'),
	'looseYears' => true,
]);

echo $this->Form->input('game_start', [
	'label' => __('Game start time'),
]);
echo $this->Form->input('game_end', [
	'label' => __('Game time cap'),
	'empty' => '---',
	'help' => $game_slot->field->indoor ? null : __('Choose "---" to assign the default time cap (dark).'),
]);
?>
	</fieldset>
	<fieldset>
		<legend><?= __('Make Game Slot Available To') ?></legend>
		<div id="DivisionList">
<?php
if (empty($divisions)) {
	echo __('No divisions operate on the selected night.');
} else {
	echo $this->Form->input('divisions._ids', [
		'label' => false,
		'multiple' => 'checkbox',
		'hiddenField' => false,
		'secure' => false,
	]);
}
?>
		</div>
	</fieldset>
	<?= $this->Form->button(__('Submit'), ['class' => 'btn-success']) ?>
	<?= $this->Form->end() ?>
</div>
<div class="actions columns">
	<ul class="nav nav-pills">
<?php
echo $this->Html->tag('li', $this->Form->iconPostLink('delete_32.png',
	['action' => 'delete', 'gameSlot' => $game_slot->id],
	['alt' => __('Delete'), 'title' => __('Delete Game Slot')],
	['confirm' => __('Are you sure you want to delete this game_slot?')]));
?>
	</ul>
</div>
