<?php
class ControllerCatalogAttributeBulk extends Controller {
	private $error = [];

	public function index() {
		$this->load->language('catalog/attribute_bulk');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('catalog/attribute_bulk');

		$user_token = $this->session->data['user_token'];

		// Получаем языки и дефолтный язык
		$languages = $this->model_catalog_attribute_bulk->getLanguages();
		$default_language_id = $this->getDefaultLanguageId($languages);

		// Режим разбивки: 'newline' или 'delimiter'
		$split_mode = $this->request->post['split_mode'] ?? 'newline';
		$data['split_mode'] = $split_mode;

		// Обработка POST
		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
			$group_id = (int)$this->request->post['attribute_group_id'];
			$attributes = $this->request->post['attributes'];

			$report = [];

			// Выбираем шаблон для preg_split
			if ($split_mode === 'newline') {
				$pattern = '/\r\n|\r|\n/u';
			} else {
				// старый режим: любая из [новая строка, запятая, точка с запятой, |
				$pattern = '/[\r\n;|]+/u';
			}

			// Разбиваем дефолтное поле
			$default_lines = preg_split($pattern, $attributes[$default_language_id], -1, PREG_SPLIT_NO_EMPTY);

			foreach ($default_lines as $index => $value) {
				$value = trim($value);
				if ($value === '') {
					continue;
				}

				// Собираем названия по языкам
				$names = [];
				foreach ($languages as $lang) {
					$lang_id = (int)$lang['language_id'];
					$lines = preg_split($pattern, $attributes[$lang_id] ?? '', -1, PREG_SPLIT_NO_EMPTY);
					$names[$lang_id] = isset($lines[$index]) ? trim($lines[$index]) : $value;
				}

				// Проверяем существование через attributeExists()
				if (!$this->model_catalog_attribute_bulk->attributeExists($value, $group_id)) {
					$this->model_catalog_attribute_bulk->addAttributeBulk($names, $group_id, $languages);
					$report[] = [
						'status' => 'added',
						'text'   => sprintf($this->language->get('text_added'), $value)
					];
				} else {
					$report[] = [
						'status' => 'skipped',
						'text'   => sprintf($this->language->get('text_skipped'), $value)
					];
				}
			}

			// Сохраняем отчёт и success и редиректим
			$this->session->data['report'] = $report;
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('catalog/attribute_bulk', 'user_token=' . $user_token, true));
		}

		// Готовим данные для шаблона
		$data['heading_title'] = $this->language->get('heading_title');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('catalog/attribute_bulk', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['error_warning'] = $this->error['warning'] ?? '';
		$data['error_group'] = $this->error['group'] ?? '';
		$data['error_attribute'] = $this->error['attributes'] ?? [];
		$data['success'] = $this->session->data['success'] ?? '';
		if (isset($this->session->data['success'])) {
			unset($this->session->data['success']);
		}

		$data['attribute_group_id'] = $this->request->post['attribute_group_id'] ?? '';
		$defaults = array_fill_keys(array_column($languages, 'language_id'), '');
		$data['attributes'] = $this->request->post['attributes'] ?? $defaults;

		$data['report'] = $this->session->data['report'] ?? [];
		if (isset($this->session->data['report'])) {
			unset($this->session->data['report']);
		}

		$data['languages'] = $languages;
		$data['default_language_id'] = $default_language_id;
		$data['attribute_groups'] = $this->model_catalog_attribute_bulk->getAttributeGroups($default_language_id);

		$data['action'] = $this->url->link('catalog/attribute_bulk', 'user_token=' . $user_token, true);
		$data['cancel'] = $this->url->link('catalog/attribute', 'user_token=' . $user_token, true);
		$data['user_token'] = $user_token;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/attribute_bulk', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'catalog/attribute_bulk')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		if (empty($this->request->post['attribute_group_id'])) {
			$this->error['group'] = $this->language->get('error_group');
		}

		$languages = $this->model_catalog_attribute_bulk->getLanguages();
		$default_language_id = $this->getDefaultLanguageId($languages);
		$attributes = $this->request->post['attributes'] ?? [];

		if (empty(trim((string)($attributes[$default_language_id] ?? '')))) {
			$this->error['attributes'][$default_language_id] = $this->language->get('error_empty_attribute_fields');
		}

		return !$this->error;
	}

	/**
	* Метод для выбора дефолтного языка по коду конфига
	*/
	private function getDefaultLanguageId(array $languages): int {
		$code = $this->config->get('config_admin_language');
		foreach ($languages as $lang) {
			if ($lang['code'] === $code) {
				return (int)$lang['language_id'];
			}
		}
		return !empty($languages) ? (int)$languages[0]['language_id'] : 0;
	}
}
