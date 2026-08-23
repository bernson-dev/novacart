<?php
class ModelReportOrderProfit extends Model {
    public function getOrderProfit($data = []) {
        $sql = "
            SELECT
                o.order_id,
                o.date_added,
                o.firstname,
                o.lastname,
                o.email,
                o.telephone,
                o.total AS order_total,

                SUM(op.total) AS revenue_products,
                SUM(op.cost_price * op.quantity) AS cost_products,
                (SUM(op.total) - SUM(op.cost_price * op.quantity)) AS profit_products

            FROM `" . DB_PREFIX . "order` o
            LEFT JOIN `" . DB_PREFIX . "order_product` op ON (op.order_id = o.order_id)
            WHERE o.order_status_id > '0'
        ";

        // фильтр по датам
        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
        }

        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
        }

        // фильтр по статусу заказа
        if (!empty($data['filter_order_status_id'])) {
            $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        }

        $sql .= " GROUP BY o.order_id";

        // сортировка
        $sort_data = [
            'o.order_id',
            'o.date_added',
            'revenue_products',
            'cost_products',
            'profit_products',
            'o.total'
        ];

        $sort = (!empty($data['sort']) && in_array($data['sort'], $sort_data)) ? $data['sort'] : 'o.order_id';
        $order = (!empty($data['order']) && ($data['order'] == 'ASC')) ? 'ASC' : 'DESC';

        $sql .= " ORDER BY " . $sort . " " . $order;

        // пагинация
        $start = isset($data['start']) ? (int)$data['start'] : 0;
        $limit = isset($data['limit']) ? (int)$data['limit'] : 20;

        if ($start < 0) $start = 0;
        if ($limit < 1) $limit = 20;

        $sql .= " LIMIT " . $start . "," . $limit;

        return $this->db->query($sql)->rows;
    }

    public function getTotalOrderProfit($data = []) {
        $sql = "
            SELECT COUNT(DISTINCT o.order_id) AS total
            FROM `" . DB_PREFIX . "order` o
            WHERE o.order_status_id > '0'
        ";

        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
        }

        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
        }

        if (!empty($data['filter_order_status_id'])) {
            $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        }

        return (int)$this->db->query($sql)->row['total'];
    }

    public function getSummary($data = []) {
        $sql = "
            SELECT
                SUM(op.total) AS revenue_products,
                SUM(op.cost_price * op.quantity) AS cost_products,
                (SUM(op.total) - SUM(op.cost_price * op.quantity)) AS profit_products
            FROM `" . DB_PREFIX . "order` o
            LEFT JOIN `" . DB_PREFIX . "order_product` op ON (op.order_id = o.order_id)
            WHERE o.order_status_id > '0'
        ";

        if (!empty($data['filter_date_start'])) {
            $sql .= " AND DATE(o.date_added) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
        }

        if (!empty($data['filter_date_end'])) {
            $sql .= " AND DATE(o.date_added) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
        }

        if (!empty($data['filter_order_status_id'])) {
            $sql .= " AND o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
        }

        return $this->db->query($sql)->row;
    }
}
