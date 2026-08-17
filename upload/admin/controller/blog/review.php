<?php
// admin/controller/blog/review.php
class ControllerBlogReview extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('blog/review');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('blog/review');

		$this->getList();
	}

	public function add() {
		$this->load->language('blog/review');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('blog/review');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$review_article_id = $this->model_blog_review->addReview($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			if (isset($this->request->post['apply']) && (int)$this->request->post['apply'] === 1) {
				$url .= '&review_article_id=' . $review_article_id;
				$this->response->redirect($this->url->link('blog/review/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
				$this->response->redirect($this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('blog/review');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('blog/review');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_blog_review->editReview((int)$this->request->get['review_article_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			if (isset($this->request->post['apply']) && (int)$this->request->post['apply'] === 1) {
				$url .= '&review_article_id=' . (int)$this->request->get['review_article_id'];
				$this->response->redirect($this->url->link('blog/review/edit', 'user_token=' . $this->session->data['user_token'] . $url, true));
			} else {
				$this->response->redirect($this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
			}
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('blog/review');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('blog/review');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ((array)$this->request->post['selected'] as $review_article_id) {
				$this->model_blog_review->deleteReview((int)$review_article_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			$this->response->redirect($this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		$filter_article = isset($this->request->get['filter_article']) ? $this->request->get['filter_article'] : '';
		$filter_author = isset($this->request->get['filter_author']) ? $this->request->get['filter_author'] : '';
		$filter_status = isset($this->request->get['filter_status']) ? $this->request->get['filter_status'] : '';
		$filter_date_added = isset($this->request->get['filter_date_added']) ? $this->request->get['filter_date_added'] : '';
		$order = isset($this->request->get['order']) ? $this->request->get['order'] : 'DESC';
		$sort = isset($this->request->get['sort']) ? $this->request->get['sort'] : 'r.date_added';
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

		$url = $this->buildUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('blog/review/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('blog/review/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['enabled'] = $this->url->link('blog/review/enable', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['disabled'] = $this->url->link('blog/review/disable', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['reviews'] = array();

		$filter_data = array(
		'filter_article'    => $filter_article,
		'filter_author'     => $filter_author,
		'filter_status'     => $filter_status,
		'filter_date_added' => $filter_date_added,
		'sort'              => $sort,
		'order'             => $order,
		'start'             => ($page - 1) * (int)$this->config->get('config_limit_admin'),
		'limit'             => (int)$this->config->get('config_limit_admin')
		);

		$review_total = $this->model_blog_review->getTotalReviews($filter_data);
		$results = $this->model_blog_review->getReviews($filter_data);

		foreach ($results as $result) {
			$data['reviews'][] = array(
			'review_article_id' => $result['review_article_id'],
			'name'              => $result['name'],
			'author'            => $result['author'],
			'rating'            => $result['rating'],
			'status'            => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
			'date_added'        => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
			'edit'              => $this->url->link('blog/review/edit', 'user_token=' . $this->session->data['user_token'] . '&review_article_id=' . $result['review_article_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['selected'] = isset($this->request->post['selected']) ? (array)$this->request->post['selected'] : array();

		$sort_url = '';

		if (isset($this->request->get['filter_article'])) {
			$sort_url .= '&filter_article=' . urlencode(html_entity_decode($this->request->get['filter_article'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$sort_url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$sort_url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$sort_url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		$sort_url .= ($order == 'ASC') ? '&order=DESC' : '&order=ASC';

		if (isset($this->request->get['page'])) {
			$sort_url .= '&page=' . (int)$this->request->get['page'];
		}

		$data['sort_article'] = $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name' . $sort_url, true);
		$data['sort_author'] = $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . '&sort=r.author' . $sort_url, true);
		$data['sort_rating'] = $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . '&sort=r.rating' . $sort_url, true);
		$data['sort_status'] = $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . '&sort=r.status' . $sort_url, true);
		$data['sort_date_added'] = $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . '&sort=r.date_added' . $sort_url, true);

		$page_url = '';

		if (isset($this->request->get['filter_article'])) {
			$page_url .= '&filter_article=' . urlencode(html_entity_decode($this->request->get['filter_article'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$page_url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$page_url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$page_url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$page_url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$page_url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = (int)$this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $page_url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf(
		$this->language->get('text_pagination'),
		($review_total) ? (($page - 1) * (int)$this->config->get('config_limit_admin')) + 1 : 0,
		((($page - 1) * (int)$this->config->get('config_limit_admin')) > ($review_total - (int)$this->config->get('config_limit_admin'))) ? $review_total : ((($page - 1) * (int)$this->config->get('config_limit_admin')) + (int)$this->config->get('config_limit_admin')),
		$review_total,
		ceil($review_total / (int)$this->config->get('config_limit_admin'))
		);

		$data['filter_article'] = $filter_article;
		$data['filter_author'] = $filter_author;
		$data['filter_status'] = $filter_status;
		$data['filter_date_added'] = $filter_date_added;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/review_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['review_article_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';
		$data['error_article'] = isset($this->error['article']) ? $this->error['article'] : '';
		$data['error_author'] = isset($this->error['author']) ? $this->error['author'] : '';
		$data['error_text'] = isset($this->error['text']) ? $this->error['text'] : '';
		$data['error_rating'] = isset($this->error['rating']) ? $this->error['rating'] : '';

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$url = $this->buildUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['review_article_id'])) {
			$data['action'] = $this->url->link('blog/review/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('blog/review/edit', 'user_token=' . $this->session->data['user_token'] . '&review_article_id=' . (int)$this->request->get['review_article_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['review_article_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$review_info = $this->model_blog_review->getReview((int)$this->request->get['review_article_id']);
		} else {
			$review_info = array();
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('blog/article');

		$data['article_id'] = isset($this->request->post['article_id']) ? $this->request->post['article_id'] : (!empty($review_info) ? $review_info['article_id'] : '');
		$data['article'] = isset($this->request->post['article']) ? $this->request->post['article'] : (!empty($review_info) ? $review_info['article'] : '');
		$data['author'] = isset($this->request->post['author']) ? $this->request->post['author'] : (!empty($review_info) ? $review_info['author'] : '');
		$data['text'] = isset($this->request->post['text']) ? $this->request->post['text'] : (!empty($review_info) ? $review_info['text'] : '');
		$data['rating'] = isset($this->request->post['rating']) ? $this->request->post['rating'] : (!empty($review_info) ? $review_info['rating'] : '');
		$data['date_added'] = isset($this->request->post['date_added']) ? $this->request->post['date_added'] : (!empty($review_info) ? ($review_info['date_added'] != '0000-00-00 00:00' ? $review_info['date_added'] : '') : '');
		$data['status'] = isset($this->request->post['status']) ? $this->request->post['status'] : (!empty($review_info) ? $review_info['status'] : 1);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('blog/review_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'blog/review')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (empty($this->request->post['article_id'])) {
			$this->error['article'] = $this->language->get('error_article');
		}

		if (!isset($this->request->post['author']) || (utf8_strlen($this->request->post['author']) < 3) || (utf8_strlen($this->request->post['author']) > 64)) {
			$this->error['author'] = $this->language->get('error_author');
		}

		if (!isset($this->request->post['text']) || utf8_strlen($this->request->post['text']) < 1) {
			$this->error['text'] = $this->language->get('error_text');
		}

		if (!isset($this->request->post['rating']) || (int)$this->request->post['rating'] < 1 || (int)$this->request->post['rating'] > 5) {
			$this->error['rating'] = $this->language->get('error_rating');
		}

		return !$this->error;
	}

	public function enable() {
		$this->load->language('blog/review');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('blog/review');

		if (isset($this->request->post['selected']) && $this->validateEnable()) {
			foreach ((array)$this->request->post['selected'] as $review_article_id) {
				$this->model_blog_review->editStatus((int)$review_article_id, 1);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			$this->response->redirect($this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	public function disable() {
		$this->load->language('blog/review');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('blog/review');

		if (isset($this->request->post['selected']) && $this->validateDisable()) {
			foreach ((array)$this->request->post['selected'] as $review_article_id) {
				$this->model_blog_review->editStatus((int)$review_article_id, 0);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->buildUrl();

			$this->response->redirect($this->url->link('blog/review', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function validateEnable() {
		if (!$this->user->hasPermission('modify', 'blog/review')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDisable() {
		if (!$this->user->hasPermission('modify', 'blog/review')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'blog/review')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	protected function buildUrl() {
		$url = '';

		if (isset($this->request->get['filter_article'])) {
			$url .= '&filter_article=' . urlencode(html_entity_decode($this->request->get['filter_article'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_author'])) {
			$url .= '&filter_author=' . urlencode(html_entity_decode($this->request->get['filter_author'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . (int)$this->request->get['page'];
		}

		return $url;
	}
}