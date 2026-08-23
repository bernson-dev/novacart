<?php
// admin/controller/tool/dump_install.php
class ControllerToolDumpInstall extends Controller {

	private function getDefaultExcludedTables(): array {
		// Исключаем данные "живых" таблиц по умолчанию
		$suffixes = [
			'api',
			'api_ip',
			'api_session',
			'customer',
			'customer_activity',
			'customer_affiliate',
			'customer_approval',
			'customer_history',
			'customer_ip',
			'customer_login',
			'customer_online',
			'customer_reward',
			'customer_search',
			'customer_transaction',
			'customer_wishlist',
			'extension_install',
			'extension_path',
			'filter',
			'filter_description',
			'filter_group',
			'filter_group_description',
			'geo_zone',
			'googleshopping_category',
			'googleshopping_product',
			'googleshopping_product_status',
			'googleshopping_product_target',
			'googleshopping_target',
			'location',
			'manufacturer_to_layout',
			'marketing',
			'modification',
			'modification_backup',
			'order',
			'order_history',
			'order_option',
			'order_product',
			'order_recurring',
			'order_recurring_transaction',
			'order_shipment',
			'order_total',
			'order_voucher',
			//'product_option',
			'product_special',
			'product_recurring',
			'product_to_download',
			'product_to_layout',
			'recurring',
			'recurring_description',
			'return_history',
			'review',
			//'review_article',
			'shipping_courier',
			'store',
			'tax_class',
			'tax_rate',
			'tax_rate_to_customer_group',
			'tax_rule',
			'theme',
			'translation',
			'upload',
			'user',
			'voucher',
			'voucher_history',
			//'voucher_theme',
			'session',
			'zone_to_geo_zone',
		];

		$tables = [];

		foreach ($suffixes as $suffix) {
			$tables[] = DB_PREFIX . $suffix;
		}

		return $tables;
	}

	private function getIniBytes($value): int {
		if (!$value) {
			return 0;
		}

		$value = trim((string)$value);

		if ($value === '' || $value === '-1') {
			return 0;
		}

		$unit = strtolower($value[strlen($value) - 1]);
		$bytes = (int)$value;

		switch ($unit) {
			case 'g':
				$bytes *= 1024;
				// no break
			case 'm':
				$bytes *= 1024;
				// no break
			case 'k':
				$bytes *= 1024;
				break;
		}

		return $bytes;
	}

/**
* Авто-определение ограничений хостинга.
*/
	private function getWeakHostingWarnings(): array {
		$warnings = [];

		$memoryRaw = (string)ini_get('memory_limit');
		$memory = $this->getIniBytes($memoryRaw);

		$maxExecRaw = (string)ini_get('max_execution_time');
		$maxExec = (int)$maxExecRaw;

		$packetRaw = (string)(ini_get('mysqli.max_allowed_packet') ?: '4M');
		$packet = $this->getIniBytes($packetRaw);

		$disableFunctions = (string)ini_get('disable_functions');
		$setTimeLimitDisabled = stripos($disableFunctions, 'set_time_limit') !== false;

		if ($memory > 0 && $memory < 268435456) {
			$warnings[] = sprintf(
			$this->language->get('text_hosting_limit_memory'),
			$memoryRaw
			);
		}

		if ($maxExec > 0 && $maxExec < 120) {
			$warnings[] = sprintf(
			$this->language->get('text_hosting_limit_execution_time'),
			$maxExecRaw
			);
		}

		if ($packet > 0 && $packet < 8388608) {
			$warnings[] = sprintf(
			$this->language->get('text_hosting_limit_packet'),
			$packetRaw
			);
		}

		if (!function_exists('set_time_limit')) {
			$warnings[] = $this->language->get('text_hosting_limit_set_time_missing');
		} elseif ($setTimeLimitDisabled) {
			$warnings[] = $this->language->get('text_hosting_limit_set_time_disabled');
		}

		return $warnings;
	}

	public function index() {
		$this->load->language('tool/dump_install');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('tool/dump_install');
		$this->load->model('setting/store');

		$data['user_token'] = $this->session->data['user_token'];
		
		$data['comment_stores'] = [];

		$data['comment_stores'][] = [
			'store_id' => 0,
			'name'    => $this->config->get('config_name') ?: $this->language->get('text_default_store')
		];

		foreach ($this->model_setting_store->getStores() as $store) {
			$data['comment_stores'][] = [
				'store_id' => (int)$store['store_id'],
				'name'    => $store['name']
			];
		}

		$data['comment_store_id'] = '';

		if (isset($this->request->post['comment_store_id']) && $this->request->post['comment_store_id'] !== '') {
			$data['comment_store_id'] = (string)(int)$this->request->post['comment_store_id'];
		}

		// Основные языковые переменные
//		$data['heading_title'] = $this->language->get('heading_title');
//		$data['text_home'] = $this->language->get('text_home');
//		$data['text_list'] = $this->language->get('text_list');
//		$data['text_help'] = $this->language->get('text_help');
//
//		$data['entry_comment'] = $this->language->get('entry_comment');
//		$data['entry_comment_help'] = $this->language->get('entry_comment_help');
//		$data['entry_installer_mode'] = $this->language->get('entry_installer_mode');
//		$data['entry_include_drop'] = $this->language->get('entry_include_drop');
//		$data['entry_include_drop_help'] = $this->language->get('entry_include_drop_help');
//		$data['entry_table_filter'] = $this->language->get('entry_table_filter');
//		$data['entry_table_filter_placeholder'] = $this->language->get('entry_table_filter_placeholder');
//		$data['entry_data_include'] = $this->language->get('entry_data_include');
//		$data['entry_data_include_help'] = $this->language->get('entry_data_include_help');
//
//		$data['text_installer_mode'] = $this->language->get('text_installer_mode');
//		$data['text_installer_mode_help'] = $this->language->get('text_installer_mode_help');
//		$data['text_installer_mode_notice'] = $this->language->get('text_installer_mode_notice');
//		$data['text_installer_auto_hint'] = $this->language->get('text_installer_auto_hint');
//
//		$data['text_hosting_limit_details_title'] = $this->language->get('text_hosting_limit_details_title');
//
//		$data['text_quick_help_title'] = $this->language->get('text_quick_help_title');
//		$data['text_quick_help_structure'] = $this->language->get('text_quick_help_structure');
//		$data['text_quick_help_data'] = $this->language->get('text_quick_help_data');
//		$data['text_quick_help_safe'] = $this->language->get('text_quick_help_safe');
//
//		$data['text_table_filter_help'] = $this->language->get('text_table_filter_help');
//		$data['text_visible_tables'] = $this->language->get('text_visible_tables');
//		$data['text_selected_tables'] = $this->language->get('text_selected_tables');
//		$data['text_default_excluded_tables'] = $this->language->get('text_default_excluded_tables');
//		$data['text_data_include_notice'] = $this->language->get('text_data_include_notice');
//		$data['text_export_processing'] = $this->language->get('text_export_processing');
//
//		$data['button_export'] = $this->language->get('button_export');
//		$data['button_sel_all'] = $this->language->get('button_sel_all');
//		$data['button_sel_none'] = $this->language->get('button_sel_none');
//		$data['button_default'] = $this->language->get('button_default');
//		$data['button_clear_filter'] = $this->language->get('button_clear_filter');

		// Авто-определение изменения параметров для установочного дампа при слабом хостинге.
		$hostingWarnings = $this->getWeakHostingWarnings();
		$isWeakHosting = !empty($hostingWarnings);

		$data['installer_mode_hint'] = $isWeakHosting ? $this->language->get('text_installer_auto_hint') : '';
		$data['installer_mode_details'] = $hostingWarnings;
		$data['installer_mode_default'] = $isWeakHosting ? 'checked="checked"' : '';

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' .$this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('tool/dump_install', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['action'] = $this->url->link('tool/dump_install/export', 'user_token=' . $this->session->data['user_token'], true);

		$tables = $this->model_tool_dump_install->getTablesByPrefix();

		$data['tables'] = $tables;
		$data['total_tables'] = count($tables);

		$defaultExcluded = $this->getDefaultExcludedTables();
		$defaultDataInclude = array_values(array_diff($tables, $defaultExcluded));

		if (!empty($this->request->post['data_include']) && is_array($this->request->post['data_include'])) {
			$dataInclude = [];

			foreach ($this->request->post['data_include'] as $table) {
				$table = (string)$table;

				if (in_array($table, $tables, true)) {
					$dataInclude[] = $table;
				}
			}

			$data['data_include'] = array_values(array_unique($dataInclude));
		} else {
			$data['data_include'] = $defaultDataInclude;
		}

		$data['selected_tables_count'] = count($data['data_include']);
		$data['default_excluded_count'] = count(array_intersect($tables, $defaultExcluded));

		$data['default_comment'] = $this->request->post['comment'] ?? 'Installation dump';

		try {
			$data['default_data_include_json'] = json_encode(
			array_values($defaultDataInclude),
			JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
			);
		} catch (\Exception $e) {
			$data['default_data_include_json'] = '[]';
		}

		$data['error_warning'] = '';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('tool/dump_install', $data));
	}

	public function export() {
		$this->load->language('tool/dump_install');

		if (!$this->user->hasPermission('modify', 'tool/dump_install')) {
			$this->response->addHeader('HTTP/1.1 403 Forbidden');
			$this->response->setOutput($this->language->get('error_permission'));
			return;
		}

		@set_time_limit(0);
		@ini_set('memory_limit', '512M');

		$this->load->model('tool/dump_install');
		$this->load->model('setting/store');

		$allTables = $this->model_tool_dump_install->getTablesByPrefix();

		$dataInclude = [];

		if (!empty($this->request->post['data_include']) && is_array($this->request->post['data_include'])) {
			foreach ($this->request->post['data_include'] as $table) {
				$table = (string)$table;

				if (in_array($table, $allTables, true)) {
					$dataInclude[] = $table;
				}
			}
		}

		$dataInclude = array_values(array_unique($dataInclude));

		$includeDrop = !empty($this->request->post['include_drop']);

		$comment = trim($this->request->post['comment'] ?? 'Installation dump');
		$comment = preg_replace('/[\r\n\t]+/', ' ', $comment);
		$comment = $comment ?: 'Installation dump';

		$commentStoreId = '';

		if (isset($this->request->post['comment_store_id']) && $this->request->post['comment_store_id'] !== '') {
			$commentStoreId = (string)(int)$this->request->post['comment_store_id'];
		}

		$commentStoreName = '';

		if ($commentStoreId !== '') {
			if ((int)$commentStoreId === 0) {
				$commentStoreName = $this->config->get('config_name') ?: $this->language->get('text_default_store');
			} else {
				$storeInfo = $this->model_setting_store->getStore((int)$commentStoreId);

				if (!empty($storeInfo['name'])) {
					$commentStoreName = $storeInfo['name'];
				}
			}
		}

		if ($commentStoreName !== '') {
			$comment .= ' — ' . $commentStoreName;
		}

		$comment = substr($comment, 0, 200);

		// Режим для установщика приходит из формы. Если хостинг слабый, checkbox уже отмечен в index().
		$installerMode = !empty($this->request->post['installer_mode']);

		$dbCharset = $this->model_tool_dump_install->getDatabaseCharset();

		$filename = 'install_dump_' . date('Y-m-d_H-i-s') . ($installerMode ? '_installer' : '') . '.sql';

		while (ob_get_level()) {
			ob_end_clean();
		}

		header('Content-Description: File Transfer');
		header('Content-Type: application/sql; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('X-Accel-Buffering: no');

		$out = [];

		$out[] = '-- Export DB';
		$out[] = '-- ' . $comment;
		$out[] = '-- Created at: ' . date('Y-m-d H:i:s');

		if ($installerMode) {
			$out[] = '-- FOR INSTALLER (safe mode: limit=100, compatible format)';
		}

		$out[] = '';
		$out[] = 'SET NAMES ' . $dbCharset . ';';
		$out[] = 'SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";';
		$out[] = 'SET time_zone = "+00:00";';
		$out[] = '/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;';
		$out[] = '/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;';
		$out[] = '/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;';
		$out[] = '';

		$separator = str_repeat('-', 58);

		foreach ($allTables as $table) {
			$out[] = '--';
			$out[] = '-- Table structure for table `' . $table . '`';
			$out[] = '--';
			$out[] = '';

			if ($includeDrop) {
				$out[] = 'DROP TABLE IF EXISTS `' . $table . '`;';
			}

			$createSql = $this->model_tool_dump_install->getCreateTableSql($table);
			$out[] = $createSql ? $createSql : '-- WARNING: cannot read CREATE TABLE for `' . $table . '`';

			$out[] = '';
			$out[] = $separator;
			$out[] = '';
			$out[] = '--';
			$out[] = '-- Dumping data for table `' . $table . '`';
			$out[] = '--';
			$out[] = '';

			if (in_array($table, $dataInclude, true)) {
				$insertBlocks = $this->model_tool_dump_install->getInsertSql($table, null, $installerMode);

				if (!empty($insertBlocks)) {
					foreach ($insertBlocks as $sql) {
						$out[] = $sql;
					}
				}
			}

			$out[] = '';
			$out[] = $separator;
			$out[] = '';
		}

		$out[] = '-- Dump finished at: ' . date('Y-m-d H:i:s');

		echo implode("\n", $out);
		exit;
	}
}