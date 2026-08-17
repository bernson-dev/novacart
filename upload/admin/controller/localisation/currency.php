<?php
class ControllerLocalisationCurrency extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('localisation/currency');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/currency');

		$this->getList();
	}

	public function add() {
		$this->load->language('localisation/currency');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/currency');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_currency->addCurrency($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('localisation/currency');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/currency');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_localisation_currency->editCurrency($this->request->get['currency_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('localisation/currency');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/currency');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $currency_id) {
				$this->model_localisation_currency->deleteCurrency($currency_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function refresh() {
		$this->load->language('localisation/currency');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('localisation/currency');

		if ($this->validateRefresh()) {
			$result = $this->model_localisation_currency->refresh();

			if (is_array($result)) {
				if (!empty($result['success'])) {
					$this->session->data['success'] = $this->language->get($result['message']);
				} else {
					$this->session->data['warning'] = $this->language->get($result['message']);
				}
			} elseif ($result) {
				$this->session->data['success'] = $this->language->get('text_success');
			} else {
				$this->session->data['warning'] = $this->language->get('error_currency_engine');
			}

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('localisation/currency/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('localisation/currency/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['refresh'] = $this->url->link('localisation/currency/refresh', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$currency_total = $this->model_localisation_currency->getTotalCurrencies();
		$results = $this->model_localisation_currency->getCurrencies($filter_data);

		$store_currency_titles = array();
		$store_currency_codes = array();

		$all_store_currencies = $this->db->query("SELECT `code`, `title` FROM `" . DB_PREFIX . "currency`")->rows;

		foreach ($all_store_currencies as $currency_row) {
			$store_currency_titles[$currency_row['code']] = $currency_row['title'];
			$store_currency_codes[$currency_row['code']] = true;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} elseif (isset($this->session->data['warning'])) {
			$data['error_warning'] = $this->session->data['warning'];
			unset($this->session->data['warning']);
		} elseif (isset($this->error['currency_engine'])) {
			$data['error_warning'] = $this->error['currency_engine'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_title'] = $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . '&sort=title' . $url, true);
		$data['sort_code'] = $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url, true);
		$data['sort_value'] = $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . '&sort=value' . $url, true);
		$data['sort_status'] = $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		$data['sort_date_modified'] = $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . '&sort=date_modified' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $currency_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf(
		$this->language->get('text_pagination'),
		($currency_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0,
		((($page - 1) * $this->config->get('config_limit_admin')) > ($currency_total - $this->config->get('config_limit_admin')))
		? $currency_total
		: ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')),
		$currency_total,
		ceil($currency_total / $this->config->get('config_limit_admin'))
		);

		$data['sort'] = $sort;
		$data['order'] = $order;

		$currency_engine_value = $this->config->get('config_currency_engine');

		$data['provider_rates'] = array();
		$data['provider_name'] = '';
		$data['text_provider_rates'] = $this->language->get('text_provider_rates');
		$data['column_rate'] = $this->language->get('column_rate');
		$data['column_provider_title'] = $this->language->get('column_provider_title');

		$provider_rate_map = array();
		$provider_base_code = '';

		$data['currency_engine_link'] = $this->url->link(
		'marketplace/extension',
		'user_token=' . $this->session->data['user_token'] . '&type=currency',
		true
		);

		$data['currency_engine_title'] = $this->language->get('text_currency_engine_extensions');
		$data['currency_engine_label_class'] = 'label-warning';

		$data['currency_engine_set_link'] = $this->url->link(
		'setting/setting',
		'user_token=' . $this->session->data['user_token'],
		true
		);

		$data['currency_engine_set_text'] = $this->language->get('text_currency_engine_set');

		if ($currency_engine_value && $this->config->get('currency_' . $currency_engine_value . '_status')) {
			$this->load->language('extension/currency/' . $currency_engine_value, 'currency_engine');

			$currency_engine_link = $this->url->link(
			'extension/currency/' . $currency_engine_value,
			'user_token=' . $this->session->data['user_token'],
			true
			);

			$data['currency_engine_link'] = $currency_engine_link;
			$data['currency_engine_title'] = $this->language->get('currency_engine')->get('heading_title');
			$data['currency_engine_label_class'] = 'label-primary';

			$data['currency_engine_set_link'] = $this->url->link(
			'setting/setting',
			'user_token=' . $this->session->data['user_token'],
			true
			);

			$data['currency_engine_set_text'] = $this->language->get('text_currency_engine_set');

			$this->load->model('extension/currency/' . $currency_engine_value);
			$this->load->language('extension/currency/' . $currency_engine_value, 'currency_engine');

			$data['provider_name'] = $this->language->get('currency_engine')->get('heading_title');

			$model_key = 'model_extension_currency_' . str_replace('-', '_', $currency_engine_value);
			$provider_rates = $this->{$model_key}->getRates();

			if (is_callable(array($this->{$model_key}, 'getBaseCurrencyCode'))) {
				$provider_base_code = (string)$this->{$model_key}->getBaseCurrencyCode();
			}

			if ($provider_rates) {
				foreach ($provider_rates as $rate) {
					if (
					isset($rate['code']) &&
					isset($rate['rate']) &&
					(float)$rate['rate'] > 0 &&
					isset($store_currency_codes[$rate['code']])
					) {
						$provider_rate_map[$rate['code']] = (float)$rate['rate'];

						if ((!isset($rate['title']) || $rate['title'] === '') && isset($store_currency_titles[$rate['code']])) {
							$rate['title'] = $store_currency_titles[$rate['code']];
						}

						$data['provider_rates'][] = $rate;
					}
				}
			}
		} else {
			// Формируем ссылку с хэшем в конце
			$settings_link = $this->url->link('setting/setting', 'user_token=' . $this->session->data['user_token'], true) . '#tab-local';

			// Заменяем %1 на ссылку
			$data['currency_engine_error'] = str_replace(
			'%1',
			$settings_link,
			$this->language->get('text_unknown_engine')
			);
		}

		$default_currency_code = $this->config->get('config_currency');

		// базовая валюта магазина ОБЯЗАНА иметь значение
		if (isset($provider_rate_map[$default_currency_code])) {
			$raw_default = (float)$provider_rate_map[$default_currency_code];
		} else {
			// fallback — считаем её базой
			$raw_default = 1.00000000;
		}

		$data['currencies'] = array();
		$data['warning_update_needed'] = '';
		$has_negative_delta = false;

		/*
		$delta_threshold - порог отклонения курса в процентах
		$alert_mode =
				"any" - алерт при любом отклонении больше порога (вверх или вниз).
				"negative" - алерт только если магазинный курс ниже источника.
				"positive" - алерт только если магазинный курс выше источника.
		*/
		$delta_threshold = (float)$this->config->get('config_delta_threshold');
		$alert_mode = $this->config->get('config_alert_mode');

		foreach ($results as $result) {
			$provider_value = null;
			$delta_percent = null;

			$code = $result['code'];

			if ($raw_default !== null) {
				if (isset($provider_rate_map[$code]) && (float)$provider_rate_map[$code] > 0) {
					$raw_current = (float)$provider_rate_map[$code];
					$provider_value = $raw_default / $raw_current;
				} elseif ($provider_base_code !== '' && $code === $provider_base_code) {
					$provider_value = $raw_default;
				}

				if ($provider_value !== null) {
					$db_value = (float)$result['value'];

					if ($db_value > 0) {
						// сравнивать обратные значения
						//$delta_percent = (($provider_value / $db_value) - 1) * 100;

						// сравниваем магазинный курс с провайдерским
						$delta_percent = (($db_value / $provider_value) - 1) * 100;

						if (abs($delta_percent) < 0.00001) {
							$delta_percent = 0.0;
						}

						// выбор режима проверки
						if ($delta_percent !== null) {
							if ($alert_mode === 'any' && abs($delta_percent) >= $delta_threshold) {
								$has_negative_delta = true;
							} elseif ($alert_mode === 'negative' && $delta_percent <= -$delta_threshold) {
								$has_negative_delta = true;
							} elseif ($alert_mode === 'positive' && $delta_percent >= $delta_threshold) {
								$has_negative_delta = true;
							}
						}
					}
				}
			}

			if ($has_negative_delta) {
				$data['warning_update_needed'] = sprintf($this->language->get('warning_update_needed'), $delta_threshold);
			}

			$statusText = [
				0 => $this->language->get('text_disabled'),
				1 => $this->language->get('text_enabled')
			];

			$data['currencies'][] = array(
			'currency_id'     => $result['currency_id'],
			'title'           => $result['title'] . (($result['code'] == $default_currency_code) ? $this->language->get('text_default') : ''),
			'code'            => $result['code'],
			'value'           => $result['value'],
			'provider_value'  => $provider_value,
			'correction_rate' => isset($result['correction_rate']) ? $result['correction_rate'] : '1.00000000',
			'delta_percent'   => $delta_percent,
			'status'          => (bool)$result['status'],
			'status_text'     => $statusText[(int)$result['status']],
			'date_modified'   => date($this->language->get('datetime_format'), strtotime($result['date_modified'])),
			'edit'            => $this->url->link('localisation/currency/edit', 'user_token=' . $this->session->data['user_token'] . '&currency_id=' . $result['currency_id'] . $url, true)
			);
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/currency_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = (!isset($this->request->get['currency_id']) ? $this->language->get('text_add') : $this->language->get('text_edit'));

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		if (isset($this->error['correction_rate'])) {
			$data['error_correction_rate'] = $this->error['correction_rate'];
		} else {
			$data['error_correction_rate'] = '';
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['currency_id'])) {
			$data['action'] = $this->url->link('localisation/currency/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('localisation/currency/edit', 'user_token=' . $this->session->data['user_token'] . '&currency_id=' . $this->request->get['currency_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('localisation/currency', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$currency_info = array();

		if (isset($this->request->get['currency_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$currency_info = $this->model_localisation_currency->getCurrency($this->request->get['currency_id']);
		}

		if (isset($this->request->post['title'])) {
			$data['title'] = $this->request->post['title'];
		} elseif (!empty($currency_info)) {
			$data['title'] = $currency_info['title'];
		} else {
			$data['title'] = '';
		}

		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($currency_info)) {
			$data['code'] = $currency_info['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->request->post['symbol_left'])) {
			$data['symbol_left'] = $this->request->post['symbol_left'];
		} elseif (!empty($currency_info)) {
			$data['symbol_left'] = $currency_info['symbol_left'];
		} else {
			$data['symbol_left'] = '';
		}

		if (isset($this->request->post['symbol_right'])) {
			$data['symbol_right'] = $this->request->post['symbol_right'];
		} elseif (!empty($currency_info)) {
			$data['symbol_right'] = $currency_info['symbol_right'];
		} else {
			$data['symbol_right'] = '';
		}

		if (isset($this->request->post['decimal_place'])) {
			$data['decimal_place'] = $this->request->post['decimal_place'];
		} elseif (!empty($currency_info)) {
			$data['decimal_place'] = $currency_info['decimal_place'];
		} else {
			$data['decimal_place'] = '';
		}

		if (isset($this->request->post['value'])) {
			$data['value'] = $this->request->post['value'];
		} elseif (!empty($currency_info)) {
			$data['value'] = $currency_info['value'];
		} else {
			$data['value'] = '';
		}

		if (isset($this->request->post['correction_rate'])) {
			$data['correction_rate'] = $this->request->post['correction_rate'];
		} elseif (!empty($currency_info) && isset($currency_info['correction_rate'])) {
			$data['correction_rate'] = $currency_info['correction_rate'];
		} else {
			$data['correction_rate'] = '1.00000000';
		}

		if ($data['value'] !== '' && (float)$data['value'] > 0) {
			$data['reverse_rate'] = 1 / (float)$data['value'];
		} elseif (isset($this->request->post['reverse_rate']) && (float)$this->request->post['reverse_rate'] > 0) {
			$data['reverse_rate'] = $this->request->post['reverse_rate'];
			$data['value'] = 1 / (float)$data['reverse_rate'];
		} else {
			$data['reverse_rate'] = '';
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($currency_info)) {
			$data['status'] = $currency_info['status'];
		} else {
			$data['status'] = 1;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('localisation/currency_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'localisation/currency')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['title']) || (utf8_strlen($this->request->post['title']) < 3) || (utf8_strlen($this->request->post['title']) > 32)) {
			$this->error['title'] = $this->language->get('error_title');
		}

		if (!isset($this->request->post['code']) || utf8_strlen($this->request->post['code']) != 3) {
			$this->error['code'] = $this->language->get('error_code');
		}

		if (!isset($this->request->post['correction_rate']) || !is_numeric($this->request->post['correction_rate']) || (float)$this->request->post['correction_rate'] <= 0) {
			$this->error['correction_rate'] = $this->language->get('error_correction_rate');
		}

		if (isset($this->request->post['reverse_rate']) && is_numeric($this->request->post['reverse_rate']) && (float)$this->request->post['reverse_rate'] > 0) {
			$calculated_value = 1 / (float)$this->request->post['reverse_rate'];

			if (!isset($this->request->post['value']) || abs((float)$this->request->post['value'] - $calculated_value) > 0.00000001) {
				$this->error['warning'] = $this->language->get('error_inconsistent_rates');
				$this->request->post['value'] = $calculated_value;
			}
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'localisation/currency')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('setting/store');
		$this->load->model('sale/order');

		foreach ($this->request->post['selected'] as $currency_id) {
			$currency_info = $this->model_localisation_currency->getCurrency($currency_id);

			if ($currency_info) {
				if ($this->config->get('config_currency') == $currency_info['code']) {
					$this->error['warning'] = $this->language->get('error_default');
				}

				$store_total = $this->model_setting_store->getTotalStoresByCurrency($currency_info['code']);

				if ($store_total) {
					$this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
				}
			}

			$order_total = $this->model_sale_order->getTotalOrdersByCurrencyId($currency_id);

			if ($order_total) {
				$this->error['warning'] = sprintf($this->language->get('error_order'), $order_total);
			}
		}

		return !$this->error;
	}

	protected function validateRefresh() {
		if (!$this->user->hasPermission('modify', 'localisation/currency')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$config_currency_engine = $this->config->get('config_currency_engine');

		if (!$config_currency_engine) {
			$this->error['currency_engine'] = $this->language->get('error_currency_engine');
		} elseif (!$this->config->get('currency_' . $config_currency_engine . '_status')) {
			$this->error['currency_engine'] = $this->language->get('error_currency_engine');
		}

		return !$this->error;
	}
}