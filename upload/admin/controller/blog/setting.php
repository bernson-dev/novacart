<?php
class ControllerBlogSetting extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('blog/setting');
		$this->load->model('setting/setting');
		$this->load->model('localisation/language');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('configblog', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('blog/setting', 'user_token=' . $this->session->data['user_token'], true));
		}

		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_image_category'] = isset($this->error['image_category']) ? $this->error['image_category'] : '';
		$data['error_image_article'] = isset($this->error['image_article']) ? $this->error['image_article'] : '';
		$data['error_image_related'] = isset($this->error['image_related']) ? $this->error['image_related'] : '';
		$data['error_article_limit'] = isset($this->error['article_limit']) ? $this->error['article_limit'] : '';
		$data['error_article_description_length'] = isset($this->error['article_description_length']) ? $this->error['article_description_length'] : '';
		$data['error_limit_admin'] = isset($this->error['limit_admin']) ? $this->error['limit_admin'] : '';

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('blog/setting', 'user_token=' . $this->session->data['user_token'], true)
		);

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['action'] = $this->url->link('blog/setting', 'user_token=' . $this->session->data['user_token'], true);

		// Маршрут для логики возврата
		$data['cancel'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);

		$data['user_token'] = $this->session->data['user_token'];

		$data['configblog_article_limit'] = isset($this->request->post['configblog_article_limit'])
		? $this->request->post['configblog_article_limit']
		: $this->config->get('configblog_article_limit');

		$data['configblog_article_description_length'] = isset($this->request->post['configblog_article_description_length'])
		? $this->request->post['configblog_article_description_length']
		: $this->config->get('configblog_article_description_length');

		$data['configblog_limit_admin'] = isset($this->request->post['configblog_limit_admin'])
		? $this->request->post['configblog_limit_admin']
		: $this->config->get('configblog_limit_admin');

		$data['configblog_article_count'] = isset($this->request->post['configblog_article_count'])
		? $this->request->post['configblog_article_count']
		: $this->config->get('configblog_article_count');

		$data['configblog_blog_menu'] = isset($this->request->post['configblog_blog_menu'])
		? $this->request->post['configblog_blog_menu']
		: $this->config->get('configblog_blog_menu');

		$data['configblog_article_download'] = isset($this->request->post['configblog_article_download'])
		? $this->request->post['configblog_article_download']
		: $this->config->get('configblog_article_download');

		$data['configblog_review_status'] = isset($this->request->post['configblog_review_status'])
		? $this->request->post['configblog_review_status']
		: $this->config->get('configblog_review_status');

		$data['configblog_review_guest'] = isset($this->request->post['configblog_review_guest'])
		? $this->request->post['configblog_review_guest']
		: $this->config->get('configblog_review_guest');

		$data['configblog_review_mail'] = isset($this->request->post['configblog_review_mail'])
		? $this->request->post['configblog_review_mail']
		: $this->config->get('configblog_review_mail');

		$data['configblog_image_category_width'] = isset($this->request->post['configblog_image_category_width'])
		? $this->request->post['configblog_image_category_width']
		: $this->config->get('configblog_image_category_width');

		$data['configblog_image_category_height'] = isset($this->request->post['configblog_image_category_height'])
		? $this->request->post['configblog_image_category_height']
		: $this->config->get('configblog_image_category_height');

		$data['configblog_image_article_width'] = isset($this->request->post['configblog_image_article_width'])
		? $this->request->post['configblog_image_article_width']
		: $this->config->get('configblog_image_article_width');

		$data['configblog_image_article_height'] = isset($this->request->post['configblog_image_article_height'])
		? $this->request->post['configblog_image_article_height']
		: $this->config->get('configblog_image_article_height');

		$data['configblog_image_related_width'] = isset($this->request->post['configblog_image_related_width'])
		? $this->request->post['configblog_image_related_width']
		: $this->config->get('configblog_image_related_width');

		$data['configblog_image_related_height'] = isset($this->request->post['configblog_image_related_height'])
		? $this->request->post['configblog_image_related_height']
		: $this->config->get('configblog_image_related_height');

		$configblog_name = $this->config->get('configblog_name');
		$configblog_html_h1 = $this->config->get('configblog_html_h1');
		$configblog_meta_title = $this->config->get('configblog_meta_title');
		$configblog_meta_description = $this->config->get('configblog_meta_description');
		$configblog_meta_keyword = $this->config->get('configblog_meta_keyword');

		$data['configblog_name'] = isset($this->request->post['configblog_name'])
		? $this->request->post['configblog_name']
		: (is_array($configblog_name) ? $configblog_name : array());

		$data['configblog_html_h1'] = isset($this->request->post['configblog_html_h1'])
		? $this->request->post['configblog_html_h1']
		: (is_array($configblog_html_h1) ? $configblog_html_h1 : array());

		$data['configblog_meta_title'] = isset($this->request->post['configblog_meta_title'])
		? $this->request->post['configblog_meta_title']
		: (is_array($configblog_meta_title) ? $configblog_meta_title : array());

		$data['configblog_meta_description'] = isset($this->request->post['configblog_meta_description'])
		? $this->request->post['configblog_meta_description']
		: (is_array($configblog_meta_description) ? $configblog_meta_description : array());

		$data['configblog_meta_keyword'] = isset($this->request->post['configblog_meta_keyword'])
		? $this->request->post['configblog_meta_keyword']
		: (is_array($configblog_meta_keyword) ? $configblog_meta_keyword : array());

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/setting', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'blog/setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (
		!isset($this->request->post['configblog_image_category_width']) ||
		!isset($this->request->post['configblog_image_category_height']) ||
		!is_numeric($this->request->post['configblog_image_category_width']) ||
		!is_numeric($this->request->post['configblog_image_category_height']) ||
		(int)$this->request->post['configblog_image_category_width'] < 1 ||
		(int)$this->request->post['configblog_image_category_height'] < 1
		) {
			$this->error['image_category'] = $this->language->get('error_image_category');
		}

		if (
		!isset($this->request->post['configblog_image_article_width']) ||
		!isset($this->request->post['configblog_image_article_height']) ||
		!is_numeric($this->request->post['configblog_image_article_width']) ||
		!is_numeric($this->request->post['configblog_image_article_height']) ||
		(int)$this->request->post['configblog_image_article_width'] < 1 ||
		(int)$this->request->post['configblog_image_article_height'] < 1
		) {
			$this->error['image_article'] = $this->language->get('error_image_article');
		}

		if (
		!isset($this->request->post['configblog_image_related_width']) ||
		!isset($this->request->post['configblog_image_related_height']) ||
		!is_numeric($this->request->post['configblog_image_related_width']) ||
		!is_numeric($this->request->post['configblog_image_related_height']) ||
		(int)$this->request->post['configblog_image_related_width'] < 1 ||
		(int)$this->request->post['configblog_image_related_height'] < 1
		) {
			$this->error['image_related'] = $this->language->get('error_image_related');
		}

		if (
		!isset($this->request->post['configblog_article_limit']) ||
		!is_numeric($this->request->post['configblog_article_limit']) ||
		(int)$this->request->post['configblog_article_limit'] < 1
		) {
			$this->error['article_limit'] = $this->language->get('error_limit');
		}

		if (
		!isset($this->request->post['configblog_article_description_length']) ||
		!is_numeric($this->request->post['configblog_article_description_length']) ||
		(int)$this->request->post['configblog_article_description_length'] < 1
		) {
			$this->error['article_description_length'] = $this->language->get('error_limit');
		}

		if (
		!isset($this->request->post['configblog_limit_admin']) ||
		!is_numeric($this->request->post['configblog_limit_admin']) ||
		(int)$this->request->post['configblog_limit_admin'] < 1
		) {
			$this->error['limit_admin'] = $this->language->get('error_limit');
		}

		// Проверка мультиязычного названия
		/*
		if (isset($this->request->post['configblog_name']) && is_array($this->request->post['configblog_name'])) {
			foreach ($this->request->post['configblog_name'] as $language_id => $value) {
				if (utf8_strlen(trim($value)) < 1 || utf8_strlen(trim($value)) > 255) {
					$this->error['warning'] = $this->language->get('error_warning');
					break;
				}
			}
		}
		*/

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}
}