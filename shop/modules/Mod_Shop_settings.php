<?php

/**
 * This is a module that is part of the "shop" addon.
 * This module's URL is example.com/shop_settings
*/

namespace Zotlabs\Module;

use App;
use Zotlabs\Lib\Apps;
use Zotlabs\Web\Controller;
use Zotlabs\Extend\Route;

// Shop_settings class "controller" logic for the plugin's "shop_settings" route
class Shop_settings extends Controller {

	// Class Fields
	private string $_pluginName = '';
	
	// Method executed during page initialization
	public function init(): void {
		// Set pluginName string to this class's name 
		$this->_pluginName = strtolower(trim(strrchr(__CLASS__, '\\'), '\\'));
	}
	
	// Generic handler for a HTTP POST request (e.g., a form submission)
	public function post(): void {
        // If the user is NOT logged in as an Admin, then do nothing
		if (!is_site_admin()) killme();

		// Presumably, check for a valid CSRF form token
		check_form_security_token_redirectOnErr('/' . $this->_pluginName, $this->_pluginName);

		// Logic triggered by the "Add/Edit custom variable" operations of the plugin
		// If the 'custom_var_name' and 'custom_var_value' POST vars are set and not empty, then
		// Add the custom variable's key-value pair to the "config" database table
		if (argc() > 1 && argv(1) == 'add' && !empty(trim($_POST['plan_name'])) && preg_match('/^(\d+\.\d{2})$/', $_POST['plan_cost']) == 1)
		{
			$plans = (array)get_config('shop', 'plans');
			if (!isset($plans[$_POST['plan_cost']])) {
				$planName = preg_replace('/[^a-zA-Z0-9_-]/', "", trim($_POST['plan_name']));
				Route::register('addon/custompage/modules/shop/Mod_' . ucfirst($planName) . '.php', $planName);
				$newplan = [$_POST['plan_cost'] => $planName];
				$plans = (empty($plans) || current($plans) === false) ? $newplan : array_merge($newplan, $plans);
				ksort($plans);
				\Shop::makeServiceClasses($plans);
				set_config('shop', 'plans', 'json:' . json_encode($plans));
			}
		}
		if (argc() > 1 && argv(1) == 'remove' && !empty($_POST['plan_remove']))
		{
			$plans = get_config('shop', 'plans');
			if ($plans !== false && !empty($plans) && isset($plans[$_POST['plan_remove']])) {
				Route::unregister('addon/custompage/modules/shop/Mod_' . ucfirst($plans[$_POST['plan_remove']]) . '.php', $plans[$_POST['plan_remove']]);
				del_config('service_class', $plans[$_POST['plan_remove']]);
				unset($plans[$_POST['plan_remove']]);
				\Shop::makeServiceClasses($plans);
				set_config('shop', 'plans', 'json:' . json_encode($plans));
			}
		}		
		if (argc() > 1 && argv(1) == 'other')
		{
			if ((int)$_POST['term_lengthn'] > 0 && isset(\Shop::_TERM_OPTS[$_POST['term_length']])) {
				set_config('shop', 'term_length', $_POST['term_lengthn']);
				set_config('shop', 'term_units', $_POST['term_length']);
			}
			if (!empty(trim($_POST['paypal_emails']))) {
				set_config('shop', 'paypal_emails', trim($_POST['paypal_emails']));
			}
			if (!empty(trim($_POST['paypal_pdt_token']))) {
				set_config('shop', 'paypal_pdt_token', trim($_POST['paypal_pdt_token']));
			}	
			if (in_array((int)$_POST['paypal_sandbox'], [0, 1])) {
				set_config('shop', 'paypal_sandbox', (int)$_POST['paypal_sandbox']);
			}					
		}

		// Trigger the get() function in this class to render content
		$this->get();
	}

	// Generic handler for a HTTP GET request (e.g., viewing the page normally)
	public function get(): string {
        // If the user is NOT logged in as an Admin, then do nothing
		if (!is_site_admin()) return '';

		// Load "config" (database table) settings in the "shop" category into an array
        $variables = \Shop::loadShopConfig();

		// Create "Name" field markup in Add/Edit form and insert template vars
		$planName = replace_macros(get_markup_template('field_input.tpl'), [
			'$field' => [
				'plan_name', 
				t('Plan Name'), 
				'', 
				t('Single word; no spaces')
			]
		]);

		// Create "Value" field markup in Add/Edit form and insert template vars
		$planCost = replace_macros(get_markup_template('field_input.tpl'), [
			'$field' => [
				'plan_cost', 
				t('Plan Cost'), 
				'', 
				t('Number with 2 decimal places')
			]
		]);

		$plans = replace_macros(get_markup_template('field_select.tpl'), [
			'$field' => [
				'plan_remove', 
				t('Choose Plan'), 
				'',
				'',
				((!empty($variables['plans'])) ? array_combine(array_keys($variables['plans']), array_map(fn($k, $v) => $v . " - " . $k, array_keys($variables['plans']), array_values($variables['plans']))) : []),
				''
			]
		]); 

		$term = replace_macros(get_markup_template('field_duration.qmc.tpl'), [	
				'label'	 	=> t('Plan Term'), 			
				'qmc'	 	=> '', 				// not required
				'qmcid'	 	=> '',				// not required
				'wrapper' 	=> 'no',			// when no wrapper around is desired
				'field'		=> [				// fieldset properties
					'name'  => 'term_length', 
					'value' => $variables['term_length'] ?? '',			
					'min'  	=> 	"1", 			// the minimum value for the numeric input
					'max'  	=> 	"999999", 		// the maximum value for the numeric input
					'size' 	=> 	"6", 			// the max digits for the numeric input
					'title' => 'Term length',
					'default' => $variables['term_units'] ?? ''	  // say 'default' => '' if none defaults (or omit)
				],
				'rabot'	=> \Shop::_TERM_OPTS	// the radio buttons
			]
		);

		$paypalEmails = replace_macros(get_markup_template('field_input.tpl'), [
			'$field' => [
				'paypal_emails', 
				t('PayPal Emails (Live and/or Sandbox)'), 
				$variables['paypal_emails'] ?? '', 
				t('Separate mutiple emails with a comma')
			]
		]);

		$paypalPdtToken = replace_macros(get_markup_template('field_input.tpl'), [
			'$field' => [
				'paypal_pdt_token', 
				t('PayPal PDT token'), 
				$variables['paypal_pdt_token'] ?? '', 
				t('Token provided after enabling PDT in PayPal account')
			]
		]);	
		
		$paypalSandbox = replace_macros(get_markup_template('field_checkbox.tpl'), [
			'$field' => [
				'paypal_sandbox', 
				t('Enable PayPal Sandbox?'), 
				(bool)$variables['paypal_sandbox'], 
				t('Use sandbox to test transactions')
			]
		]);
		
		// Create page section and Add/Edit form markup, inserting form fields and other template vars
		$addPlan = replace_macros(get_markup_template("settings_addon.tpl"), [
			'$action_url' => $this->_pluginName . "/add",
			'$form_security_token' => get_form_security_token($this->_pluginName),
			'$title' => t('Add Plan'),
			'$content' => $planName . $planCost,
			'$submit' => t('Submit')
		]);
		$removePlan = replace_macros(get_markup_template("settings_addon.tpl"), [
			'$action_url' => $this->_pluginName . "/remove",
			'$form_security_token' => get_form_security_token($this->_pluginName),
			'$title' => t('Remove Plan'),
			'$content' => $plans,
			'$submit' => t('Submit')
		]);
		$settings = replace_macros(get_markup_template("settings_addon.tpl"), [
			'$action_url' => $this->_pluginName . "/other",
			'$form_security_token' => get_form_security_token($this->_pluginName),
			'$title' => t('Other Settings'),
			'$content' => $term . "<br>" . $paypalEmails . $paypalPdtToken . $paypalSandbox,
			'$submit' => t('Submit')
		]);		

		// Return/Render content in the plugin template's "content" region depending on whether this is an Add or Edit request
		return $addPlan . $removePlan . $settings;
	}

}
