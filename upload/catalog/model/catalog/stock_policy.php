<?php
class ModelCatalogStockPolicy extends Model {
	public function evaluate(array $product, $quantity = 1, array $option = array()) {
		$quantity = max(1, (int)$quantity);
		$available = null;
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

		$stock_status_id = isset($product['stock_status_id']) ? (int)$product['stock_status_id'] : 0;
		$stock_status = isset($product['stock_status']) ? (string)$product['stock_status'] : '';
		$action = $this->getStockStatusAction($stock_status_id);
		$can_buy = true;
		$reason = '';

		if ($available !== null && $available <= 0) {
			if ($action === 'block') {
				$can_buy = false;
				$reason = 'out_of_stock';
			} elseif ($action === 'allow') {
				$can_buy = true;
			} else {
				$can_buy = (bool)$this->config->get('config_stock_checkout');

				if (!$can_buy) {
					$reason = 'out_of_stock';
				}
			}
		} elseif ($available !== null && $quantity > $available) {
			$excess_mode = (string)$this->config->get('config_stock_purchase_excess');

			if (!$excess_mode) {
				$excess_mode = 'block';
			}

			if ($excess_mode === 'checkout') {
				$can_buy = (bool)$this->config->get('config_stock_checkout');
			} else {
				$can_buy = false;
			}

			if (!$can_buy) {
				$reason = 'insufficient';
			}
		}

		if ($can_buy) {
			$button_text = $this->language->get('button_cart');

			if ($available !== null && $available <= 0 && $stock_status) {
				$button_text = $stock_status;
			}
		} else {
			$button_text = $stock_status ? $stock_status : $this->language->get('text_out_of_stock');
		}

		return array(
			'can_buy'         => $can_buy,
			'action'          => $can_buy ? (($available !== null && $available <= 0) ? 'allow' : 'buy') : 'block',
			'available'       => $available,
			'limited_by'      => $limited_by,
			'reason'          => $reason,
			'stock_status_id' => $stock_status_id,
			'stock_status'    => $stock_status,
			'button_text'     => $button_text
		);
	}

	public function getListPolicy(array $product) {
		return $this->evaluate($product, isset($product['minimum']) ? max(1, (int)$product['minimum']) : 1);
	}

	private function getStockStatusAction($stock_status_id) {
		$actions = (array)$this->config->get('config_stock_purchase_status_action');

		if (isset($actions[$stock_status_id]) && in_array($actions[$stock_status_id], array('inherit', 'block', 'allow'), true)) {
			return $actions[$stock_status_id];
		}

		return 'inherit';
	}
}
