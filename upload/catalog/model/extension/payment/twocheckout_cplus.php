<?php

class ModelExtensionPaymentTwoCheckoutCplus extends Model {

	/**
	 * Ipn Constants
	 *
	 * Not all are used, however they should be left here
	 * for future reference
	 */

	const RECURRING_ACTIVE = 1;
	const RECURRING_INACTIVE = 2;
	const RECURRING_CANCELLED = 3;
	const RECURRING_SUSPENDED = 4;
	const RECURRING_EXPIRED = 5;
	const RECURRING_PENDING = 6;

	const TRANSACTION_DATE_ADDED = 0;
	const TRANSACTION_PAYMENT = 1;
	const TRANSACTION_OUTSTANDING_PAYMENT = 2;
	const TRANSACTION_SKIPPED = 3;
	const TRANSACTION_FAILED = 4;
	const TRANSACTION_CANCELLED = 5;
	const TRANSACTION_SUSPENDED = 6;
	const TRANSACTION_SUSPENDED_FAILED = 7;
	const TRANSACTION_OUTSTANDING_FAILED = 8;
	const TRANSACTION_EXPIRED = 9;

	const API_URL = 'https://api.2checkout.com/rest/';
	const API_VERSION = '6.0';

	const ORDER_CREATED = 'ORDER_CREATED';
	const FRAUD_STATUS_CHANGED = 'FRAUD_STATUS_CHANGED';
	const INVOICE_STATUS_CHANGED = 'INVOICE_STATUS_CHANGED';
	const REFUND_ISSUED = 'REFUND_ISSUED';
	//Order Status Values:
	const ORDER_STATUS_PENDING = 'PENDING';
	const ORDER_STATUS_PAYMENT_AUTHORIZED = 'PAYMENT_AUTHORIZED';
	const ORDER_STATUS_SUSPECT = 'SUSPECT';
	const ORDER_STATUS_AUTHRECEIVED = 'AUTHRECEIVED';
	const ORDER_STATUS_INVALID = 'INVALID';
	const ORDER_STATUS_COMPLETE = 'COMPLETE';
	const ORDER_STATUS_REFUND = 'REFUND';
	const ORDER_STATUS_REVERSED = 'REVERSED';
	const ORDER_STATUS_PURCHASE_PENDING = 'PURCHASE_PENDING';
	const ORDER_STATUS_PAYMENT_RECEIVED = 'PAYMENT_RECEIVED';
	const ORDER_STATUS_CANCELED = 'CANCELED';
	const ORDER_STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
	const FRAUD_STATUS_APPROVED = 'APPROVED';
	const FRAUD_STATUS_DENIED = 'DENIED';
	const FRAUD_STATUS_REVIEW = 'UNDER REVIEW';
	const FRAUD_STATUS_PENDING = 'PENDING';


	public function recurringPayments() {
		return true;
	}

	/**
	 * @param $address
	 * @param $total
	 *
	 * @return array
	 */
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/twocheckout_cplus');
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_twocheckout_cplus_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		if ($this->config->get('payment_twocheckout_cplus_total') > 0 && $this->config->get('payment_twocheckout_cplus_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_twocheckout_cplus_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = [];

		if ($status) {
			$method_data = [
				'code'       => 'twocheckout_cplus',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('payment_twocheckout_cplus_sort_order')
			];
		}

		return $method_data;
	}

	/**
	 *
	 * @return mixed
	 * @throws Exception
	 */
	private function getHeaders() {
		$sellerId = $this->config->get('payment_twocheckout_cplus_account');
		$secretKey = $this->config->get('payment_twocheckout_cplus_secret_key');

		if (!$sellerId || !$secretKey) {
			throw new Exception('Merchandiser needs a valid 2Checkout SellerId and SecretKey to authenticate!');
		}
		$gmtDate = gmdate('Y-m-d H:i:s');
		$string = strlen($sellerId) . $sellerId . strlen($gmtDate) . $gmtDate;
		$hash = hash_hmac('sha3-256', $string, $secretKey);

		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Accept: application/json';
		$headers[] = 'X-Avangate-Authentication: code="' . $sellerId . '" date="' . $gmtDate . '" hash="' . $hash . '" algo="sha3-256"';

		return $headers;
	}

	/**
	 * @param $endpoint
	 * @param string $method
	 * @return mixed
	 * @throws Exception
	 */
	public function call($endpoint, $method = 'GET') {
		// if endpoint does not starts or end with a '/' we add it, as the API needs it
		if ($endpoint[0] !== '/') {
			$endpoint = '/' . $endpoint;
		}
		if ($endpoint[-1] !== '/') {
			$endpoint = $endpoint . '/';
		}

		try {
			$url = self::API_URL . self::API_VERSION . $endpoint;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

			$response = curl_exec($ch);
			if ($response === false) {
				exit(curl_error($ch));
			}
			curl_close($ch);

			return json_decode($response, true);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	/**
	 * @param $merchant_id
	 * @param $buy_link_secret_word
	 * @param $payload
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public function getSignature($merchant_id, $buy_link_secret_word, $payload) {
		$jwtToken = $this->generateJWTToken(
			$merchant_id,
			time(),
			time() + 3600,
			$buy_link_secret_word
		);

		$curl = curl_init();

		curl_setopt_array($curl, [
			CURLOPT_URL            => "https://secure.2checkout.com/checkout/api/encrypt/generate/signature",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => 'POST',
			CURLOPT_POSTFIELDS     => json_encode($payload),
			CURLOPT_HTTPHEADER     => [
				'content-type: application/json',
				'cache-control: no-cache',
				'merchant-token: ' . $jwtToken,
			],
		]);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if ($err) {
			throw new Exception(sprintf('Unable to get proper response from signature generation API. In file %s at line %s', __FILE__, __LINE__));
		}

		$response = json_decode($response, true);
		if (JSON_ERROR_NONE !== json_last_error() || !isset($response['signature'])) {
			throw new Exception(sprintf('Unable to get proper response from signature generation API. Signature not set. In file %s at line %s', __FILE__, __LINE__));
		}

		return $response['signature'];

	}

	/**
	 * @param $sub
	 * @param $iat
	 * @param $exp
	 * @param $buy_link_secret_word
	 *
	 * @return string
	 */
	private function generateJWTToken($sub, $iat, $exp, $buy_link_secret_word) {
		$header = $this->encode(json_encode(['alg' => 'HS512', 'typ' => 'JWT']));
		$payload = $this->encode(json_encode(['sub' => $sub, 'iat' => $iat, 'exp' => $exp]));
		$signature = $this->encode(
			hash_hmac('sha512', "$header.$payload", $buy_link_secret_word, true)
		);

		return implode('.', [
			$header,
			$payload,
			$signature
		]);
	}

	/**
	 * @param $data
	 *
	 * @return string|string[]
	 */
	private function encode($data) {
		return str_replace('=', '', strtr(base64_encode($data), '+/', '-_'));
	}

	/**
	 * @param $array
	 *
	 * @return string
	 */
	private function arrayExpand($array) {
		$retval = '';
		foreach ($array as $key => $value) {
			$size = strlen(stripslashes($value));
			$retval .= $size . stripslashes($value);
		}

		return $retval;
	}


	/**
	 * @param $key
	 * @param $data
	 * @params $algo
	 * @return string
	 */
	private function hmac($key, $data, $algo = 'sha3-256') {
		if ('sha3-256' === $algo) {
			return hash_hmac($algo, $data, $key);
		}

		$b = 64; // byte length for hash
		if (strlen($key) > $b) {
			$key = pack("H*", hash($algo, $key));
		}

		$key = str_pad($key, $b, chr(0x00));
		$ipad = str_pad('', $b, chr(0x36));
		$opad = str_pad('', $b, chr(0x5c));
		$k_ipad = $key ^ $ipad;
		$k_opad = $key ^ $opad;

		return hash($algo, $k_opad . pack("H*", hash($algo, $k_ipad . $data)));
	}

	/**
	 * @param $params
	 *
	 * @throws Exception
	 */
	public function indexAction($params) {
		if (!isset($params['REFNOEXT']) && (!isset($params['REFNO']) && empty($params['REFNO']))) {
			throw new Exception(sprintf(
				'Cannot identify order: "%s".',
				$params['REFNOEXT']
			));
		}
		$secret_key = $this->config->get('payment_twocheckout_cplus_secret_key');
		$hash = $this->extractHashFromParams($params);

		//if order exists
		if (isset($params['REFNOEXT']) && !empty($params['REFNOEXT'])) {
			$order = $this->model_checkout_order->getOrder($params['REFNOEXT']);
			//        ignore all other payment methods
			if ($order && $order['payment_code'] === 'twocheckout_cplus') {
				if (!$this->isIpnResponseValid($params, $secret_key, $hash)) {
					throw new Exception(sprintf(
						'Hash mismatch for 2Checkout IPN with date: "%s".',
						$params['IPN_DATE']
					));
				}
				$this->_processFraud($params);
				if ($this->_isNotFraud($params)) {
					$this->_processOrderStatus($params);
				}
			}
		} else {
			if (!$this->isIpnResponseValid($params, $secret_key, $hash)) {
				throw new Exception(sprintf(
					'Hash mismatch for 2Checkout IPN with date: "%s".',
					$params['IPN_DATE']
				));
			}
			$this->_processFraud($params);
			if ($this->_isNotFraud($params)) {
				$this->addUpdateSubscriptionsTransaction($params);
			}
		}

		echo $this->_calculateIpnResponse(
			$params,
			html_entity_decode($this->config->get('payment_twocheckout_cplus_secret_key')),
			$hash['algo']
		);
		die;
	}

	/**
	 * @param $params
	 *
	 * @return bool
	 */
	private function _isNotFraud($params) {
		return (isset($params['FRAUD_STATUS']) && trim($params['FRAUD_STATUS']) === self::FRAUD_STATUS_APPROVED);
	}

	/**
	 * @param $params
	 * @param $secret_key
	 * @param array $hash   [algo, hash]
	 * @return bool
	 */
	private function isIpnResponseValid($params, $secret_key, $hash) {
		$result = '';
		$receivedHash = $hash['hash'];
		foreach ($params as $key => $val) {
			if (!in_array($key, ["HASH", "SIGNATURE_SHA2_256", "SIGNATURE_SHA3_256"])) {
				if (is_array($val)) {
					$result .= $this->arrayExpand($val);
				} else {
					$size = strlen(stripslashes($val));
					$result .= $size . stripslashes($val);
				}
			}
		}
		if (isset($params['REFNO']) && !empty($params['REFNO'])) {
			$calcHash = $this->hmac($secret_key, $result, $hash['algo']);
			if ($receivedHash === $calcHash) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $params
	 * @param $secret_key
	 *
	 * @return string
	 */
	private function _calculateIpnResponse($params, $secret_key, $algo = 'sha3-256') {
		$resultResponse = '';
		$ipnParamsResponse = [];
		// we're assuming that these always exist, if they don't then the problem is on avangate side
		$ipnParamsResponse['IPN_PID'][0] = $params['IPN_PID'][0];
		$ipnParamsResponse['IPN_PNAME'][0] = $params['IPN_PNAME'][0];
		$ipnParamsResponse['IPN_DATE'] = $params['IPN_DATE'];
		$ipnParamsResponse['DATE'] = date('YmdHis');

		foreach ($ipnParamsResponse as $key => $val) {
			$resultResponse .= $this->arrayExpand((array)$val);
		}

		if ('md5' === $algo) {
			return sprintf(
				'<EPAYMENT>%s|%s</EPAYMENT>',
				$ipnParamsResponse['DATE'],
				$this->hmac($secret_key, $resultResponse, $algo)
			);
		} else {
			return sprintf(
				'<sig algo="%s" date="%s">%s</sig>',
				$algo,
				$ipnParamsResponse['DATE'],
				$this->hmac($secret_key, $resultResponse, $algo)
			);
		}
	}

	/**
	 * @param $params
	 *
	 * @throws Exception
	 */
	private function _processOrderStatus($params) {
		$orderStatus = $params['ORDERSTATUS'];
		$text = $this->language->get('updated_order_status');
		$this->addUpdateTransaction($params); // for further refunds

		if (!empty($orderStatus)) {
			switch (trim($orderStatus)) {
				case self::ORDER_STATUS_PENDING:
				case self::ORDER_STATUS_PURCHASE_PENDING:
				case self::ORDER_STATUS_AUTHRECEIVED:
				case self::ORDER_STATUS_PAYMENT_RECEIVED:
				case self::ORDER_STATUS_PENDING_APPROVAL:
				case self::ORDER_STATUS_PAYMENT_AUTHORIZED:
					$this->log->write('Order status changed to processing');
					$order_status_id = $this->config->get('payment_twocheckout_cplus_processing_status_id');
					$this->model_checkout_order->addOrderHistory($params['REFNOEXT'], $order_status_id, $text . 'PROCESSING');
					break;
				case self::ORDER_STATUS_COMPLETE:
					$this->log->write('Order status changed to complete');
					$order_status_id = $this->config->get('payment_twocheckout_cplus_order_status_id');
					if (!$this->isChargeBack($params)) {
						$this->model_checkout_order->addOrderHistory($params['REFNOEXT'], $order_status_id, $text . 'COMPLETE');
					}
					break;
				case self::ORDER_STATUS_INVALID:
					$this->log->write('Order status changed to cancelled');
					$order_status_id = $this->config->get('payment_twocheckout_cplus_canceled_status_id');
					$this->model_checkout_order->addOrderHistory($params['REFNOEXT'], $order_status_id, $text . 'CANCELLED');
					break;

				default:
					throw new Exception('Cannot handle Ipn message type for message');
			}
		}
	}

	/**
	 * Update status & place a note on the Order
	 * @param $params
	 * @return bool
	 */
	private function isChargeBack($params) {
		$chargeBackResolution = isset($params['CHARGEBACK_RESOLUTION']) ? trim($params['CHARGEBACK_RESOLUTION']) : '';
		$chargeBackReasonCode = isset($params['CHARGEBACK_REASON_CODE']) ? trim($params['CHARGEBACK_REASON_CODE']) : '';

		// we need to mock up a message with some params in order to add this note
		if (!empty($chargeBackResolution) && $chargeBackResolution !== 'NONE' && !empty($chargeBackReasonCode)) {

			$this->load->model('checkout/order');
			// list of chargeback reasons on 2CO platform
			$reasons = [
				'UNKNOWN'                  => 'Unknown', //default
				'MERCHANDISE_NOT_RECEIVED' => 'Order not fulfilled/not delivered',
				'DUPLICATE_TRANSACTION'    => 'Duplicate order',
				'FRAUD / NOT_RECOGNIZED'   => 'Fraud/Order not recognized',
				'FRAUD'                    => 'Fraud',
				'CREDIT_NOT_PROCESSED'     => 'Agreed refund not processed',
				'NOT_RECOGNIZED'           => 'New/renewal order not recognized',
				'AUTHORIZATION_PROBLEM'    => 'Authorization problem',
				'INFO_REQUEST'             => 'Information request',
				'CANCELED_RECURRING'       => 'Recurring payment was canceled',
				'NOT_AS_DESCRIBED'         => 'Product(s) not as described/not functional'
			];

			$why = isset($reasons[$chargeBackReasonCode]) ?
				$reasons[$chargeBackReasonCode] :
				$reasons['UNKNOWN'];
			$message = '2Checkout chargeback status is now ' . $chargeBackResolution . '. Reason: ' . $why . '!';

			$this->log->write('Order status changed to chargeback');
			$order_status_id = $this->config->get('payment_twocheckout_cplus_chargeback_status_id');
			$this->model_checkout_order->addOrderHistory($params['REFNOEXT'], $order_status_id, $message);

			return true;
		}

		return false;
	}


	/**
	 * @param $params
	 */
	private function _processFraud($params) {
		$text = $this->language->get('updated_order_status');
		if (isset($params['FRAUD_STATUS'])) {
			if (trim($params['FRAUD_STATUS']) == self::FRAUD_STATUS_DENIED) {
				$this->log->write('Order status changed to failed');
				$order_status_id = $this->config->get('payment_twocheckout_cplus_failed_status_id');
				$this->model_checkout_order->addOrderHistory($params['REFNOEXT'], $order_status_id, $text . ' failed/denied');
			}
		}
	}

	/**
	 * @param $order_info
	 *
	 * @return array
	 */
	public function getBillingDetails($order_info) {
		$buy_link_params = [];
		$buy_link_params['name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
		$buy_link_params['phone'] = $order_info['telephone'];
		$buy_link_params['country'] = $order_info['payment_iso_code_2'];
		$buy_link_params['state'] = $order_info['payment_zone'];
		$buy_link_params['email'] = $order_info['email'];
		$buy_link_params['address'] = $order_info['payment_address_1'];
		$buy_link_params['city'] = $order_info['payment_city'];
		$buy_link_params['company-name'] = $order_info['payment_company'];

		return $buy_link_params;
	}

	/**
	 * @param $order_info
	 * @param $cart
	 *
	 * @return array
	 */
	public function getShippingDetails($order_info, $cart) {
		$buy_link_params = [];

		if ($cart) {
			$buy_link_params['ship-name'] = $order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'];
			$buy_link_params['ship-country'] = $order_info['shipping_iso_code_2'];
			$buy_link_params['ship-state'] = $order_info['shipping_zone'];
			$buy_link_params['ship-city'] = $order_info['shipping_city'];
			$buy_link_params['ship-email'] = $order_info['email'];
			$buy_link_params['ship-address'] = $order_info['shipping_address_1'];
			$buy_link_params['ship-address2'] = $order_info['shipping_address_2'];
			$buy_link_params['zip'] = $order_info['shipping_postcode'];
		} else {
			$buy_link_params['ship-name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
			$buy_link_params['ship-country'] = $order_info['payment_iso_code_2'];
			$buy_link_params['ship-state'] = $order_info['payment_zone'];
			$buy_link_params['ship-city'] = $order_info['payment_city'];
			$buy_link_params['ship-email'] = $order_info['email'];
			$buy_link_params['ship-address'] = $order_info['payment_address_1'];
			$buy_link_params['ship-address2'] = $order_info['payment_address_2'];
			$buy_link_params['zip'] = $order_info['payment_postcode'];
		}

		return $buy_link_params;
	}

	/**
	 * @param $order_info
	 * @param $test
	 * @param $seller_id
	 * @param $language
	 *
	 * @return array
	 */
	public function getOtherDetails($order_info, $test, $seller_id, $language) {
		$buy_link_params = [];
		$buy_link_params['src'] = 'OPENCART_' . str_replace('.', '_', VERSION);
		$buy_link_params['return-type'] = 'redirect';
		$buy_link_params['return-url'] = $this->url->link('extension/payment/twocheckout_cplus/success');
		$buy_link_params['expiration'] = time() + (3600 * 5);
		$buy_link_params['order-ext-ref'] = $order_info['order_id'];
		$buy_link_params['item-ext-ref'] = date('YmdHis');
		$buy_link_params['customer-ext-ref'] = $order_info['email'];
		$buy_link_params['currency'] = strtolower($order_info['currency_code']);
		$buy_link_params['language'] = $language;
		$buy_link_params['test'] = ($test == 1) ? 1 : 0;
		$buy_link_params['merchant'] = $seller_id;
		$buy_link_params['dynamic'] = 1;

		return $buy_link_params;
	}

	/**
	 * @param $orderId
	 * @param $products
	 * @param $total
	 * @param $hasRecurring
	 * @return array
	 */
	public function getProducts($orderId, $products, $total, $hasRecurring) {
		$buy_link_params = [];

		if ($hasRecurring) {
			$itemsArray = [];
			$this->load->language('extension/payment/twocheckout_cplus');
			$this->load->model('extension/payment/twocheckout_cplus');

			$itemsArray["qty"][] = 1;
			$itemsArray["prod"][] = 'Cart_' . $orderId;
			$itemsArray["item-ext-ref"][] = date('YmdHis');
			$itemsArray["tangible"][] = 0;
			$itemsArray["type"][] = "PRODUCT";
			$itemsArray["price"][] = $total;
			$itemsArray['recurrence'][] = '';
			$itemsArray['renewal-price'][] = 0;
			$itemsArray['duration'][] = '';

			foreach ($products as $item) {

				$itemsArray["qty"][] = $item['quantity'];
				$itemsArray["tangible"][] = 0;
				$itemsArray["type"][] = "PRODUCT";
				$itemsArray["price"][] = 0;

				if (isset($item['recurring']) && !empty($item['recurring'])) { // is a subscription
					$recurring = $item['recurring'];

					$productName = $this->language->get('subscription_for') . $item['name'] . ' ('
						. $this->language->get('taxes_included') . ')';
					$price = (
						$this->tax->calculate(
							$recurring['price'],
							$item['tax_class_id'],
							$this->config->get('config_tax')
						)
						* $item['quantity']
					);

					$recurring_amt = $this->currency->format(
						$this->tax->calculate($recurring['price'], $item['tax_class_id'], $this->config->get('config_tax')),
						$this->session->data['currency'],
						false,
						false
					) * $item['quantity'];
					$recurring_description = $recurring_amt . ' every ' . $recurring['cycle'] . ' ' . $recurring['frequency'];
					if ($item['recurring']['duration'] > 0) {
						$recurring_description .= ' for ' . $recurring['duration'] . ' payments';
					}
					$recurring['product_id'] = $item['product_id'];
					$recurring['quantity'] = $item['quantity'];
					$recurring['product_quantity'] = $item['quantity'];

					$itemsArray["prod"][] = $productName;
					$itemsArray['recurrence'][] = $this->_mapRecurringUnit($recurring['frequency'], $item['recurring']['cycle']);
					$itemsArray['renewal-price'][] = number_format($price, '2', '.', '');
					$itemsArray['duration'][] = $this->_mapRecurringUnit($item['recurring']['frequency'], $item['recurring']['duration']);

					$orderRecurringId = $this->model_checkout_recurring->addRecurring($orderId, $recurring_description, $recurring);
					//we build the item ref from product ID and the recurringId so we can trace back the subscription itself
					$itemsArray["item-ext-ref"][] = $item['product_id'] . '_' . $orderRecurringId;

				} else {
					$itemsArray["item-ext-ref"][] = $item['product_id'] . '_0';

					$itemsArray["prod"][] = $item['name'];
					$itemsArray['recurrence'][] = '';
					$itemsArray['renewal-price'][] = 0;
					$itemsArray['duration'][] = '';
				}
			}
			$buy_link_params['prod'] = implode(';', $itemsArray["prod"]);
			$buy_link_params['price'] = implode(';', $itemsArray["price"]);
			$buy_link_params['qty'] = implode(';', $itemsArray["qty"]);
			$buy_link_params['type'] = implode(';', $itemsArray["type"]);
			$buy_link_params['tangible'] = implode(';', $itemsArray["tangible"]);
			$buy_link_params['item-ext-ref'] = implode(';', $itemsArray["item-ext-ref"]);

			if (isset($itemsArray["recurrence"])) {
				$buy_link_params['recurrence'] = implode(';', $itemsArray["recurrence"]);
			}
			if (isset($itemsArray["duration"])) {
				$buy_link_params['duration'] = implode(';', $itemsArray["duration"]);
			}
			if (isset($itemsArray["renewal-price"])) {
				$buy_link_params['renewal-price'] = implode(';', $itemsArray["renewal-price"]);
			}

		} else { // if there are no recurring items  we send only one line item with the total of the ORDER
			$buy_link_params["qty"] = 1;
			$buy_link_params["prod"] = 'Cart_' . $orderId;
			$buy_link_params["item-ext-ref"] = date('YmdHis');
			$buy_link_params["tangible"] = 0;
			$buy_link_params["type"] = "PRODUCT";
			$buy_link_params["price"] = $total;

		}

		return $buy_link_params;
	}

	/**
	 * once the payment is made we save/update the transaction for future refunds
	 * @param $params
	 * @return mixed
	 */
	public function addUpdateTransaction($params) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "twocheckout_cplus` 
        WHERE `order_id` = '" . (int)$params['REFNOEXT'] . "' LIMIT 1");
		if ($query->num_rows) {
			return $this->db->query("
                UPDATE `" . DB_PREFIX . "twocheckout_cplus` SET 
                `transaction_id` = '" . trim($this->db->escape($params['REFNO'])) . "',
                `amount` = '" . trim($this->db->escape($params['IPN_TOTALGENERAL'])) . "',
                `currency` = '" . trim(strtoupper($this->db->escape($params['CURRENCY']))) . "',
                `tco_order_status` = '" . trim(strtoupper($this->db->escape($params['ORDERSTATUS']))) . "'
                WHERE `order_id` = '" . (int)$params['REFNOEXT'] . "'
                ");
		}

		return $this->db->query("
                INSERT INTO `" . DB_PREFIX . "twocheckout_cplus` SET 
                `order_id` = '" . (int)$params['REFNOEXT'] . "', 
                `transaction_id` = '" . trim($this->db->escape($params['REFNO'])) . "',
                `amount` = '" . trim($this->db->escape($params['IPN_TOTALGENERAL'])) . "',
                `currency` = '" . trim(strtoupper($this->db->escape($params['CURRENCY']))) . "',
                `tco_order_status` = '" . trim(strtoupper($this->db->escape($params['ORDERSTATUS']))) . "'
                ");

	}

	public function addUpdateSubscriptionsTransaction($params) {
		if (isset($params["ORIGINAL_REFNOEXT"][0]) && !empty($params["ORIGINAL_REFNOEXT"][0]) && ($params["FRAUD_STATUS"] == 'APPROVED') && ($params["MESSAGE_TYPE"] == 'COMPLETE')) {
			$externalRef = $params["IPN_LICENSE_REF"][0];
			$recurring = $this->model_account_recurring->getOrderRecurringByReference($externalRef);
			if ($recurring != false) {
				if ($recurring['status'] != 1) {
					$this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = 2 WHERE `order_recurring_id` = '" . (int)$recurring['order_recurring_id'] . "'");
				}
				$paymentAmount = 0;
				if (!empty($_POST['IPN_PRICE'])) {
					foreach ($_POST['IPN_PRICE'] as $priceAdd) {
						$paymentAmount = $paymentAmount + $priceAdd;
					}
				}
				$this->addRecurringTransaction($recurring['order_recurring_id'], $externalRef, $paymentAmount, 1);
			}
		}
	}

	/**
	 * @param $unit
	 * @param $duration
	 *
	 * @return string
	 */
	private function _mapRecurringUnit($unit, $duration) {
		if ($unit === 'semi_month') {

			return ($duration * 15) . ':DAY';
		}
		$recurringUnits = [
			"day"        => "DAY",
			"week"       => "WEEK",
			"month"      => "MONTH",
			'semi_month' => "DAY",
			"year"       => "YEAR"
		];

		return $duration . ':' . $recurringUnits[$unit];
	}

	public function updateRecurring($order_recurring_id, $reference) {
		$this->db->query("UPDATE " . DB_PREFIX . "order_recurring SET  `status` = '" . self::RECURRING_ACTIVE . "', reference = '" . $this->db->escape($reference) . "' WHERE order_recurring_id = '" . (int)$order_recurring_id . "'");

		return $this->db->countAffected() > 0;
	}

	public function addRecurringTransaction($order_recurring_id, $reference, $amount, $status) {
		if ($status) {
			$transaction_type = self::TRANSACTION_PAYMENT;
		} else {
			$transaction_type = self::TRANSACTION_FAILED;
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET order_recurring_id='" . (int)$order_recurring_id . "', reference='" . $this->db->escape($reference) . "', type='" . (int)$transaction_type . "', amount='" . (float)$amount . "', date_added=NOW()");
	}

	/**
	 * @params array $params
	 * @return array    [hash, algo]
	 */
	protected function extractHashFromParams($params): array {
		if (!empty($params['SIGNATURE_SHA3_256'])) {
			$receivedAlgo = 'sha3-256';
			$receivedHash = $params['SIGNATURE_SHA3_256'];
		}

		if (empty($receivedHash) && !empty($params['SIGNATURE_SHA2_256'])) {
			$receivedAlgo = 'sha256';
			$receivedHash = $params['SIGNATURE_SHA2_256'];
		}

		if (empty($receivedHash)) {
			$receivedAlgo = 'md5';
			$receivedHash = $params['HASH'];
		}

		return ['hash' => $receivedHash, 'algo' => $receivedAlgo];
	}
}
