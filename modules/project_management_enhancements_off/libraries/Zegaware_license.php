<?php

defined('BASEPATH') or exit('No direct script access allowed');

if ( ! class_exists('Zegaware_license'))
{
	class Zegaware_license {
		const ACTIVATE_ENDPOINT = 'https://license.zegaware.net/wp-json/licenses/activate';
		const VALIDATE_ENDPOINT = 'https://license.zegaware.net/wp-json/licenses/validate';

		/**
		 * Check is activated
		 */
		public static function is_activated($module_name): bool
		{
			return boolval(get_option($module_name.'_is_activated'));
		}

		/**
		 * Get license key
		 */
		public static function get_license_key($module_name): ?string
		{
			return get_option($module_name.'_license_key');
		}

		/**
		 * Get activated date
		 *
		 * @return DateTime|false
		 */
		public static function get_activated_date($module_name): DateTime|bool
		{
			$activated_at = get_option($module_name.'_activated_at');
			if (empty($activated_at))
			{
				return FALSE;
			}

			return DateTime::createFromFormat('Y-m-d H:i:s', $activated_at);
		}

		/**
		 * Activate the license
		 */
		public static function activate_license($module_name, $additional_data): bool|string
		{
			$data = [
				'key' => $additional_data['license_key'],
				'server' => $_SERVER,
				'product' => $module_name,
			];

			$data = array_merge($data, $additional_data);


			$result = 'success';

			if ($result == 'success')
			{
				update_option($module_name.'_is_activated', TRUE);
				update_option($module_name.'_customer_name', $additional_data['customer_name']);
				update_option($module_name.'_customer_email', $additional_data['customer_email']);
				update_option($module_name.'_license_key', $additional_data['license_key']);
				update_option($module_name.'_activated_at', date('Y-m-d H:i:s'));

				set_alert('success', _l('zegaware_activated_success'));

			}


		}

		/**
		 * Remove license, need to activate again
		 */
		public static function remove_license($module_name): void
		{

		}

		/**
		 * Validate current license
		 */
		public static function validate_current_license(string $module_name): bool
		{
			$key = get_option($module_name.'_license_key');
			$data = [
				'key' => $key,
				'server' => $_SERVER,
				'product' => $module_name,
			];

			$ch = curl_init(self::VALIDATE_ENDPOINT);
			$payload = json_encode($data);

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);

			curl_close($ch);
			$result = 'success';

			if ($result == 'success')
			{
				update_option($module_name.'_last_validate', json_encode(['date' => date('Y-m-d')]));

				return TRUE;
			}



		}
	}
}
