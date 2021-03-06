<?php
namespace App\Test\TestCase\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\RegistrationsController Test Case
 */
class RegistrationsControllerTest extends ControllerTestCase {

	/**
	 * Fixtures
	 *
	 * @var array
	 */
	public $fixtures = [
		'app.event_types',
		'app.affiliates',
			'app.users',
				'app.people',
					'app.affiliates_people',
					'app.people_people',
					'app.skills',
					'app.credits',
			'app.groups',
				'app.groups_people',
			'app.upload_types',
				'app.uploads',
			'app.leagues',
				'app.divisions',
					'app.teams',
						'app.teams_people',
					'app.divisions_days',
					'app.divisions_people',
			'app.franchises',
				'app.franchises_people',
				'app.franchises_teams',
			'app.questions',
				'app.answers',
			'app.questionnaires',
				'app.questionnaires_questions',
			'app.events',
				'app.prices',
					'app.registrations',
						'app.payments',
							'app.registration_audits',
						'app.responses',
				'app.preregistrations',
			'app.badges',
			'app.settings',
			'app.waivers',
				'app.waivers_people',
	];

	/**
	 * Test full_list method
	 *
	 * @return void
	 */
	public function testFullList() {
		// Admins are allowed to see the full list of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseContains('/registrations/view?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP);
		$this->assertResponseContains('/registrations/edit?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP, '_ext' => 'csv'], PERSON_ID_ADMIN);
		$this->assertResponseContains('Home Phone');
		$this->assertResponseContains('Total Amount');

		// Managers are allowed to see the full list of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->assertResponseContains('/registrations/view?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP);
		$this->assertResponseContains('/registrations/edit?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP, '_ext' => 'csv'], PERSON_ID_MANAGER);
		$this->assertResponseContains('Home Phone');
		$this->assertResponseContains('Total Amount');

		// Coordinators are not allowed to see the full list of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_COORDINATOR);

		// Except that they are allowed to see the list of registrations for divisions they coordinate, but without registration view and edit links
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY], PERSON_ID_COORDINATOR);
		$this->assertResponseContains('/people/view?person=' . PERSON_ID_PLAYER);
		$this->assertResponseNotContains('/registrations/view?registration=' . REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY);
		$this->assertResponseNotContains('/registrations/edit?registration=' . REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY, '_ext' => 'csv'], PERSON_ID_COORDINATOR);
		$this->assertResponseNotContains('Home Phone');
		$this->assertResponseNotContains('Work Phone');
		$this->assertResponseNotContains('Work Ext');
		$this->assertResponseNotContains('Mobile Phone');
		$this->assertResponseNotContains('Total Amount');

		// Others are not allowed to see the list of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'full_list', 'event' => EVENT_ID_MEMBERSHIP]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test summary method
	 *
	 * @return void
	 */
	public function testSummary() {
		// Admins are allowed to see the summary of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_ADMIN);

		// Managers are allowed to see the summary of registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_MANAGER);

		// Coordinators are not allowed to see the summary of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_COORDINATOR);

		// Except that they are allowed to see the summary of registrations for divisions they coordinate
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY], PERSON_ID_COORDINATOR);

		// Others are not allowed to see the summary of registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'summary', 'event' => EVENT_ID_MEMBERSHIP]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test statistics method
	 *
	 * @return void
	 */
	public function testStatistics() {
		// Admins are allowed to see statistics
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_ADMIN);

		// Managers are allowed to see statistics
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_MANAGER);

		// Others are not allowed to see statistics
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'statistics'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'statistics']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test report method
	 *
	 * @return void
	 */
	public function testReport() {
		// Admins are allowed to report
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_ADMIN);

		// Managers are allowed to report
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_MANAGER);

		// Others are not allowed to report
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'report'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'report']);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test accounting method
	 *
	 * @return void
	 */
	public function testAccounting() {
		$this->markTestIncomplete('Operation not implemented yet.');

		// Admins are allowed to see accounting
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'accounting'], PERSON_ID_ADMIN);

		// Managers are allowed to see accounting
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'accounting'], PERSON_ID_MANAGER);

		// Others are not allowed to see accounting
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'accounting'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'accounting'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'accounting'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'accounting'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'accounting']);
	}

	/**
	 * Test view method
	 *
	 * @return void
	 */
	public function testView() {
		// Admins are allowed to view registrations, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseContains('/registrations/edit?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP);
		// Paid registrations cannot be removed
		$this->assertResponseNotContains('/registrations/unregister?registration=' . REGISTRATION_ID_PLAYER_MEMBERSHIP);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_MANAGER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertResponseContains('/registrations/edit?registration=' . REGISTRATION_ID_MANAGER_MEMBERSHIP);
		$this->assertResponseContains('/registrations/unregister?registration=' . REGISTRATION_ID_MANAGER_MEMBERSHIP);

		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_ADMIN);
		$this->assertResponseContains('/registrations/edit?registration=' . REGISTRATION_ID_ANDY_SUB_INDIVIDUAL);
		$this->assertResponseContains('/registrations/unregister?registration=' . REGISTRATION_ID_ANDY_SUB_INDIVIDUAL);

		// Managers are allowed to view registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->assertResponseContains('/registrations/edit?registration=' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP);
		$this->assertResponseNotContains('/registrations/unregister?registration=' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_MANAGER);

		// Others are not allowed to view registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_INDIVIDUAL_THURSDAY], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'view', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test register method as an admin
	 *
	 * @return void
	 */
	public function testRegisterAsAdmin() {
		// Admins are allowed to register, within or somewhat before the date range
		FrozenDate::setTestNow(new FrozenDate('first Monday of April'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_ADMIN);

		FrozenDate::setTestNow(new FrozenDate('first Monday of March'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_ADMIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a manager
	 *
	 * @return void
	 */
	public function testRegisterAsManager() {
		// Managers are allowed to register, within or somewhat before the date range
		FrozenDate::setTestNow(new FrozenDate('first Monday of April'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_MANAGER);

		FrozenDate::setTestNow(new FrozenDate('first Monday of March'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_MANAGER);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a coordinator
	 *
	 * @return void
	 */
	public function testRegisterAsCoordinator() {
		// Coordinators are allowed to register, within the date range
		FrozenDate::setTestNow(new FrozenDate('first Monday of April'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_COORDINATOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a captain
	 *
	 * @return void
	 */
	public function testRegisterAsCaptain() {
		// Captains are allowed to register, within the date range
		FrozenDate::setTestNow(new FrozenDate('first Monday of April'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as a player
	 *
	 * @return void
	 */
	public function testRegisterAsPlayer() {
		// Players are allowed to register, within the date range
		FrozenDate::setTestNow(new FrozenDate('first Monday of April'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_PLAYER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method as someone else
	 *
	 * @return void
	 */
	public function testRegisterAsVisitor() {
		// Visitors are allowed to register, within the date range
		FrozenDate::setTestNow(new FrozenDate('first Monday of April'));
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test register method without being logged in
	 *
	 * @return void
	 */
	public function testRegisterAsAnonymous() {
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'register', 'event' => EVENT_ID_LEAGUE_TEAM]);
	}

	/**
	 * Test register_payment_fields method
	 *
	 * @return void
	 */
	public function testRegisterPaymentFields() {
		$this->enableCsrfToken();

		// Anyone logged in is allowed to get register payment fields, within the date range
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			PERSON_ID_ADMIN, ['price_id' => PRICE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			PERSON_ID_MANAGER, ['price_id' => PRICE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			PERSON_ID_COORDINATOR, ['price_id' => PRICE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			PERSON_ID_CAPTAIN, ['price_id' => PRICE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			PERSON_ID_PLAYER, ['price_id' => PRICE_ID_MEMBERSHIP]);
		$this->assertPostAjaxAsAccessOk(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			PERSON_ID_VISITOR, ['price_id' => PRICE_ID_MEMBERSHIP]);

		// Others are not allowed to register payment fields
		$this->assertPostAjaxAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'register_payment_fields'],
			['price_id' => PRICE_ID_MEMBERSHIP]);

		$this->markTestIncomplete('More scenarios to test above.');
	}

	/**
	 * Test redeem method for success
	 *
	 * @return void
	 */
	public function testRedeemSuccess() {
		// People are allowed to redeem their own credits
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_CAPTAIN);

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test redeem method for various failure scenarios
	 *
	 * @return void
	 */
	public function testRedeemFailure() {
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => REGISTRATION_ID_COORDINATOR_MEMBERSHIP],
			PERSON_ID_COORDINATOR, ['controller' => 'Registrations', 'action' => 'checkout'],
			'You have no available credits.');
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP],
			PERSON_ID_PLAYER, ['controller' => 'Registrations', 'action' => 'checkout'],
			'This registration is already paid in full.');
	}

	/**
	 * Test redeem method as others
	 *
	 * @return void
	 */
	public function testRedeemAsOthers() {
		// Even admins are not allowed to redeem credits on behalf of players
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'redeem', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test checkout method for success
	 *
	 * @return void
	 */
	public function testCheckoutSuccess() {
		// Anyone with outstanding registrations is allowed to checkout
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'checkout'], PERSON_ID_MANAGER);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'checkout'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'checkout'], PERSON_ID_CAPTAIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test checkout method for various failure scenarios
	 *
	 * @return void
	 */
	public function testCheckoutFailure() {
		// Anyone without an outstanding registration has no reason to checkout
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout'],
			PERSON_ID_ADMIN, ['controller' => 'Events', 'action' => 'wizard']);
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout'],
			PERSON_ID_PLAYER, ['controller' => 'Events', 'action' => 'wizard']);
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'checkout'],
			PERSON_ID_VISITOR, ['controller' => 'Events', 'action' => 'wizard']);

		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'checkout']);
	}

	/**
	 * Test unregister method as an admin
	 *
	 * @return void
	 */
	public function testUnregisterAsAdmin() {
		// Admins are allowed to unregister
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => REGISTRATION_ID_CAPTAIN2_MEMBERSHIP],
			PERSON_ID_ADMIN, '/',
			'Successfully unregistered from this event.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a manager
	 *
	 * @return void
	 */
	public function testUnregisterAsManager() {
		// Managers are allowed to unregister
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => REGISTRATION_ID_CAPTAIN2_MEMBERSHIP],
			PERSON_ID_MANAGER, '/',
			'Successfully unregistered from this event.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a coordinator
	 *
	 * @return void
	 */
	public function testUnregisterAsCoordinator() {
		// Coordinators are allowed to unregister
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => REGISTRATION_ID_COORDINATOR_MEMBERSHIP],
			PERSON_ID_COORDINATOR, ['controller' => 'Registrations', 'action' => 'checkout'],
			'Successfully unregistered from this event.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a captain
	 *
	 * @return void
	 */
	public function testUnregisterAsCaptain() {
		// Captains are allowed to unregister
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => REGISTRATION_ID_CAPTAIN2_MEMBERSHIP],
			PERSON_ID_CAPTAIN2, ['controller' => 'Registrations', 'action' => 'checkout'], 'Successfully unregistered from this event.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as a player
	 *
	 * @return void
	 */
	public function testUnregisterAsPlayer() {
		// Players are allowed to unregister
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP],
			PERSON_ID_PLAYER, ['controller' => 'Registrations', 'action' => 'checkout'],
			'You have already paid for this! Contact the office to arrange a refund.');
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method as someone else
	 *
	 * @return void
	 */
	public function testUnregisterAsVisitor() {
		// Visitors are allowed to unregister
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test unregister method without being logged in
	 *
	 * @return void
	 */
	public function testUnregisterAsAnonymous() {
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'unregister', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test payment method
	 *
	 * @return void
	 */
	public function testPaymentReceipt() {
		// Payments come from third-party payment processors, and don't use CSRF or form security

		// Default setting is PayPal, which sends parameters in the URL
		$this->assertGetAnonymousAccessOk(['controller' => 'Registrations', 'action' => 'payment', 'token' => 'TESTING']);

		// Also test Chase, which posts parameters
		TableRegistry::get('Settings')->updateAll(['value' => 'chase'], ['name' => 'payment_implementation']);
		Cache::clear(false, 'long_term');

		$login = Configure::read('payment.chase_test_store');
		$key = Configure::read('payment.chase_test_response');
		$calculated_hash = md5("{$key}{$login}12345678907.00");

		$this->assertPostAnonymousAccessOk(['controller' => 'Registrations', 'action' => 'payment'], [
			'exact_ctr' => 'DATE/TIME: ' . FrozenTime::now()->format('d M y H:i:s'),
			'Reference_No' => 'R00000000' . REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
			'Bank_Resp_Code' => '000',
			'Bank_Message' => 'APPROVED',
			'CardHoldersName' => 'Crystal Captain',
			'Expiry_Date' => FrozenTime::now()->addYear()->format('MMyy'),
			'Card_Number' => '############1234',
			'TransactionCardType' => 'VISA',
			'x_response_code' => 1,
			'x_trans_id' => '1234567890',
			'x_auth_code' => '12345A',
			'x_amount' => '7.00',
			'x_MD5_Hash' => $calculated_hash,
			'x_description' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP,
		]);

		$registration = TableRegistry::get('Registrations')->get(REGISTRATION_ID_CAPTAIN_MEMBERSHIP, [
			'contain' => ['Payments']
		]);
		$this->assertEquals('Paid', $registration->payment);
		$this->assertEquals(3, count($registration->payments));

		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as an admin
	 *
	 * @return void
	 */
	public function testAddPaymentAsAdmin() {
		// Admins are allowed to add payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as a manager
	 *
	 * @return void
	 */
	public function testAddPaymentAsManager() {
		// Managers are allowed to add payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test add_payment method as others
	 *
	 * @return void
	 */
	public function testAddPaymentAsOthers() {
		// Others are not allowed to add payments
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'add_payment', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP]);
	}

	/**
	 * Test refund_payment method as an admin
	 *
	 * @return void
	 */
	public function testRefundPaymentAsAdmin() {
		// Admins are allowed to refund payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as a manager
	 *
	 * @return void
	 */
	public function testRefundPaymentAsManager() {
		// Managers are allowed to refund payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test refund_payment method as others
	 *
	 * @return void
	 */
	public function testRefundPaymentAsOthers() {
		// Others are not allowed to refund payments
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'refund_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test credit_payment method as an admin
	 *
	 * @return void
	 */
	public function testCreditPaymentAsAdmin() {
		// Admins are allowed to credit payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as a manager
	 *
	 * @return void
	 */
	public function testCreditPaymentAsManager() {
		// Managers are allowed to credit payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test credit_payment method as others
	 *
	 * @return void
	 */
	public function testCreditPaymentAsOthers() {
		// Others are not allowed to credit payments
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'credit_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test transfer_payment method as an admin
	 *
	 * @return void
	 */
	public function testTransferPaymentAsAdmin() {
		// Admins are allowed to transfer payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'transfer_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as a manager
	 *
	 * @return void
	 */
	public function testTransferPaymentAsManager() {
		// Managers are allowed to transfer payments
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'transfer_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');
	}

	/**
	 * Test transfer_payment method as others
	 *
	 * @return void
	 */
	public function testTransferPaymentAsOthers() {
		// Others are not allowed to transfer payments
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'transfer_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'transfer_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'transfer_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'transfer_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'transfer_payment', 'payment' => PAYMENT_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test edit method as an admin
	 *
	 * @return void
	 */
	public function testEditAsAdmin() {
		// Admins are allowed to edit registrations, with full edit permissions
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_MANAGER_MEMBERSHIP], PERSON_ID_ADMIN);
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_ADMIN);
	}

	/**
	 * Test edit method as a manager
	 *
	 * @return void
	 */
	public function testEditAsManager() {
		// Managers are allowed to edit registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP], PERSON_ID_MANAGER);

		// But not ones in other affiliates
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_ANDY_SUB_INDIVIDUAL], PERSON_ID_MANAGER);
	}

	/**
	 * Test edit method as others
	 *
	 * @return void
	 */
	public function testEditAsOthers() {
		// Others are allowed to edit, if payments have not been made
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_COORDINATOR_MEMBERSHIP], PERSON_ID_COORDINATOR);

		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_CAPTAIN_MEMBERSHIP],
			PERSON_ID_CAPTAIN, '/',
			'You cannot edit a registration once a payment has been made.');

		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP],
			PERSON_ID_PLAYER, '/',
			'You cannot edit a registration once a payment has been made.');

		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'edit', 'registration' => REGISTRATION_ID_PLAYER_MEMBERSHIP]);
	}

	/**
	 * Test unpaid method
	 *
	 * @return void
	 */
	public function testUnpaid() {
		// Admins are allowed to list unpaid registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_ADMIN);
		$this->markTestIncomplete('Not implemented yet.');

		// Managers are allowed to list unpaid registrations
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_MANAGER);
		$this->markTestIncomplete('Not implemented yet.');

		// Others are not allowed to list unpaid registrations
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'unpaid']);
	}

	/**
	 * Test credits method
	 *
	 * @return void
	 */
	public function testCredits() {
		// Admins are allowed to list credits
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_ADMIN);

		// Managers are allowed to list credits
		$this->assertGetAsAccessOk(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_MANAGER);

		// Others are not allowed to list credits
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_COORDINATOR);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'credits'], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'credits']);
	}

	/**
	 * Test waiting list method
	 *
	 * @return void
	 */
	public function testWaiting() {
		// Admins are allowed to see the waiting list
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP],
			PERSON_ID_ADMIN, '/',
			'There is nobody on the waiting list for this event.');

		// Managers are allowed to see the waiting list
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP],
			PERSON_ID_MANAGER, '/',
			'There is nobody on the waiting list for this event.');

		// Coordinators are not allowed to see the waiting list
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_COORDINATOR);

		// Except that they are allowed to see the waiting list for divisions they coordinate, but without registration view and edit links
		$this->assertGetAsAccessRedirect(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_LEAGUE_INDIVIDUAL_THURSDAY],
			PERSON_ID_COORDINATOR, '/',
			'There is nobody on the waiting list for this event.');

		// Others are not allowed to see the waiting list
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_CAPTAIN);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_PLAYER);
		$this->assertGetAsAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP], PERSON_ID_VISITOR);
		$this->assertGetAnonymousAccessDenied(['controller' => 'Registrations', 'action' => 'waiting', 'event' => EVENT_ID_MEMBERSHIP]);
	}

}
