<?php
class ModelCatalogStockPolicy extends Model {
	public function evaluate(array $product, $quantity = 1, array $option = array(), $include_cart = true) {
		$this->load->model('catalog/product');

		$quantity = max(1, (int)$quantity);
		$existing_quantity = $include_cart ? $this->getExistingCartQuantity((int)$product['product_id'], $option) : 0;
		$requested_total = $existing_quantity + $quantity;
		$available = null;
		$remaining = null;
		$limited_by = '';

		if (!empty($product['subtract'])) {
			$available = max(0, (int)$product['quantity']);
			$limited_by = 'product';
		}

		if ($option) {
			$product_options = $this->model_catalog_product->getProductOptions((int)$product['product_id']);

			foreach ($product_options as $product_option) {
				$product_option_id = (int)$product_option['product_option_id'];

				if (!isset($option[$product_option_id])) {
					continue;
				}

				$selected = $option[$product_option_id];

				if ($product_option['type'] === 'checkbox') {
					$selected_values = is_array($selected) ? $selected : array($selected);
				} elseif (in_array($product_option['type'], array('select', 'radio', 'image'), true)) {
					$selected_values = array($selected);
				} else {
					$selected_values = array();
				}

				if (!$selected_values) {
					continue;
				}

				foreach ($product_option['product_option_value'] as $product_option_value) {
					if (!in_array((string)$product_option_value['product_option_value_id'], array_map('strval', $selected_values), true)) {
						continue;
					}

					if (!empty($product_option_value['subtract'])) {
						$option_available = max(0, (int)$product_option_value['quantity']);

						if ($available === null || $option_available < $available) {
							$available = $option_available;
							$limited_by = 'option';
						}
					}
				}
			}
		}

		if ($available !== null) {
			$remaining = max(0, $available - $existing_quantity);
		}

		if (!$this->config->get('config_stock_purchase_status')) {
			return array(
				'can_buy'         => true,
				'action'          => 'buy',
				'available'       => $available,
				'remaining'       => $remaining,
				'limited_by'      => $limited_by,
				'reason'          => '',
				'stock_status_id' => isset($product['stock_status_id']) ? (int)$product['stock_status_id'] : 0,
				'stock_status'    => isset($product['stock_status']) ? (string)$product['stock_status'] : '',
				'stock_rule'      => 'inherit',
				'button_text'     => $this->language->get('button_cart'),
				'existing_quantity'=> $existing_quantity,
				'requested_total' => $requested_total
			);
		}

		$stock_status_id = isset($product['stock_status_id']) ? (int)$product['stock_status_id'] : 0;

		if (!$stock_status_id && !empty($product['product_id'])) {
			$stock_status_query = $this->db->query("SELECT stock_status_id FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product['product_id'] . "' LIMIT 1");

			if ($stock_status_query->num_rows) {
				$stock_status_id = (int)$stock_status_query->row['stock_status_id'];
			}
		}

		$stock_status = isset($product['stock_status']) ? (string)$product['stock_status'] : '';
		$action = $this->getStockStatusAction($stock_status_id);
		$can_buy = true;
		$reason = '';

		if ($available !== null && $requested_total > $available) {
			if ($action === 'block') {
				$can_buy = false;
			} elseif ($action === 'allow') {
				$can_buy = true;
			} else {
				$excess_mode = (string)$this->config->get('config_stock_purchase_excess');

				if (!$excess_mode) {
					$excess_mode = 'block';
				}

				if ($excess_mode === 'checkout') {
					$can_buy = (bool)$this->config->get('config_stock_checkout');
				} else {
					$can_buy = false;
				}
			}

			if (!$can_buy) {
				$reason = ($available <= 0) ? 'out_of_stock' : 'insufficient';
			}
		}

		if ($can_buy) {
			$button_text = $this->language->get('button_cart');

			if ($available !== null && $available <= 0 && $stock_status) {
				$button_text = $stock_status;
			}
		} else {
			$button_text = $stock_status ? $stock_status : $this->language->get('button_cart');
		}

		return array(
			'can_buy'         => $can_buy,
			'action'          => $can_buy ? (($available !== null && $available <= 0) ? 'allow' : 'buy') : 'block',
			'available'       => $available,
			'remaining'       => $remaining,
			'limited_by'      => $limited_by,
			'reason'          => $reason,
			'stock_status_id' => $stock_status_id,
			'stock_status'    => $stock_status,
			'stock_rule'      => $action,
			'button_text'     => $button_text,
			'existing_quantity'=> $existing_quantity,
			'requested_total' => $requested_total
		);
	}

	public function evaluateCartProduct(array $cart_product) {
		$this->load->model('catalog/product');

		$product = $this->model_catalog_product->getProduct((int)$cart_product['product_id']);

		if (!$product) {
			return array(
				'can_buy'    => false,
				'stock_rule' => 'inherit',
				'reason'     => 'unavailable',
				'available'  => 0
			);
		}

		$option = array();

		if (!empty($cart_product['option']) && is_array($cart_product['option'])) {
			foreach ($cart_product['option'] as $item) {
				if (empty($item['product_option_id'])) {
					continue;
				}

				$product_option_id = (int)$item['product_option_id'];

				if ($item['type'] === 'checkbox') {
					if (!isset($option[$product_option_id]) || !is_array($option[$product_option_id])) {
						$option[$product_option_id] = array();
					}

					if ($item['product_option_value_id'] !== '') {
						$option[$product_option_id][] = (int)$item['product_option_value_id'];
					}
				} elseif (in_array($item['type'], array('select', 'radio', 'image'), true)) {
					$option[$product_option_id] = (int)$item['product_option_value_id'];
				} else {
					$option[$product_option_id] = $item['value'];
				}
			}
		}

		return $this->evaluate($product, max(1, (int)$cart_product['quantity']), $option, false);
	}

	public function getCartStockState() {
		$raw_has_stock = $this->cart->hasStock();

		if (!$this->config->get('config_stock_purchase_status')) {
			return array(
				'has_shortage' => !$raw_has_stock,
				'can_checkout' => $raw_has_stock || (bool)$this->config->get('config_stock_checkout'),
				'warning'      => !$raw_has_stock && (!(bool)$this->config->get('config_stock_checkout') || (bool)$this->config->get('config_stock_warning'))
			);
		}

		$has_shortage = false;
		$can_checkout = true;
		$warning = false;

		foreach ($this->cart->getProducts() as $product) {
			if (!empty($product['stock'])) {
				continue;
			}

			$has_shortage = true;
			$policy = $this->evaluateCartProduct($product);

			if (isset($policy['stock_rule']) && $policy['stock_rule'] === 'allow') {
				continue;
			}

			if (empty($policy['can_buy'])) {
				$can_checkout = false;
				$warning = true;
			} elseif ($this->config->get('config_stock_warning')) {
				$warning = true;
			}
		}

		return array(
			'has_shortage' => $has_shortage,
			'can_checkout' => $can_checkout,
			'warning'      => $warning
		);
	}

	public function getCartProductState(array $product) {
		if (!empty($product['stock'])) {
			return array(
				'shortage'     => false,
				'can_checkout' => true,
				'warning'      => false,
				'policy'       => null
			);
		}

		if (!$this->config->get('config_stock_purchase_status')) {
			$can_checkout = (bool)$this->config->get('config_stock_checkout');

			return array(
				'shortage'     => true,
				'can_checkout' => $can_checkout,
				'warning'      => !$can_checkout || (bool)$this->config->get('config_stock_warning'),
				'policy'       => null
			);
		}

		$policy = $this->evaluateCartProduct($product);

		if (isset($policy['stock_rule']) && $policy['stock_rule'] === 'allow') {
			return array(
				'shortage'     => true,
				'can_checkout' => true,
				'warning'      => false,
				'policy'       => $policy
			);
		}

		return array(
			'shortage'     => true,
			'can_checkout' => !empty($policy['can_buy']),
			'warning'      => empty($policy['can_buy']) || (bool)$this->config->get('config_stock_warning'),
			'policy'       => $policy
		);
	}

	public function getListPolicy(array $product) {
		return $this->evaluate($product, isset($product['minimum']) ? max(1, (int)$product['minimum']) : 1, array(), false);
	}

	private function getExistingCartQuantity($product_id, array $option) {
		$normalized_option = $this->normalizeOption($option);

		$query = $this->db->query("SELECT `option`, quantity FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "'");

		$total = 0;

		foreach ($query->rows as $row) {
			$row_option = json_decode($row['option'], true);

			if (!is_array($row_option)) {
				$row_option = array();
			}

			if ($this->normalizeOption($row_option) === $normalized_option) {
				$total += (int)$row['quantity'];
			}
		}

		return $total;
	}

	private function normalizeOption(array $option) {
		foreach ($option as $key => $value) {
			if (is_array($value)) {
				$value = array_map('strval', $value);
				sort($value, SORT_STRING);
				$option[$key] = $value;
			} else {
				$option[$key] = (string)$value;
			}
		}

		ksort($option, SORT_NUMERIC);

		return $option;
	}

	private function getStockStatusAction($stock_status_id) {
		$actions = (array)$this->config->get('config_stock_purchase_status_action');

		if (isset($actions[$stock_status_id]) && in_array($actions[$stock_status_id], array('inherit', 'block', 'allow'), true)) {
			return $actions[$stock_status_id];
		}

		return 'inherit';
	}
}
