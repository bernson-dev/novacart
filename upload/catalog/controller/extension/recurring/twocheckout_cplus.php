<?php

class ControllerExtensionRecurringTwocheckoutCplus extends Controller {

	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->language('extension/recurring/twocheckout_cplus');
		$this->load->model('extension/payment/twocheckout_cplus');
		$this->load->model('account/recurring');
		$this->load->model('checkout/order');
	}

	public function index() {
		$order_recurring_id = (isset($this->request->get['order_recurring_id'])) ?
			$this->request->get['order_recurring_id'] : 0;
		$recurring_info = $this->model_account_recurring->getOrderRecurring($order_recurring_id);

		if ($recurring_info) {
			$data['continue'] = $this->url->link('account/recurring', '', true);
			$data['cancel_url'] = html_entity_decode($this->url->link('extension/recurring/twocheckout_cplus/cancel', 'order_recurring_id=' . $order_recurring_id, 'SSL'));

			$data['order_recurring_id'] = '';
			if ($recurring_info['status'] == 2 || $recurring_info['status'] == 3) {
				$data['order_recurring_id'] = $order_recurring_id;
			}

			return $this->load->view('extension/recurring/twocheckout_cplus', $data);
		}
	}

	//cancel an active recurring
	public function cancel() {
		$json = [];

		if (isset($this->request->get['order_recurring_id'])) {
			$order_recurring_id = $this->request->get['order_recurring_id'];
		} else {
			$order_recurring_id = 0;
		}
		$recurring_info = $this->model_account_recurring->getOrderRecurring($order_recurring_id);
		if ($recurring_info) {

			# make api call to 2Checkout to cancel subscription
			$api_response = $this->model_extension_payment_twocheckout_cplus->call('subscriptions/' . $recurring_info['reference'], 'DELETE');
			if ($api_response) {
				$order_info = $this->model_checkout_order->getOrder($recurring_info['order_id']);
				$this->model_account_recurring->editOrderRecurringStatus($order_recurring_id, 4);//RECURRING_SUSPENDED
				$this->model_account_recurring->addOrderRecurringTransaction($order_recurring_id, 5);//RECURRING_EXPIRED
				$json['success'] = $this->language->get('text_cancelled');

				$this->model_checkout_order->addOrderHistory($recurring_info['order_id'], $order_info['order_status_id'], $this->language->get('text_order_history_cancel'), true);
			} else {
				$json['error'] = $this->language->get('error_not_found');
			}
		} else {
			$json['error'] = $this->language->get('error_not_found');
		}

		# return response
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
