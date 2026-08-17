<?php
class ControllerReportOrderProfit extends Controller {
    public function index() {
        $this->load->language('report/order_profit');

        $this->document->setTitle($this->language->get('heading_title'));

        $filter_date_start = $this->request->get['filter_date_start'] ?? '';
        $filter_date_end   = $this->request->get['filter_date_end'] ?? '';
        $filter_order_status_id = $this->request->get['filter_order_status_id'] ?? 0;

        $sort  = $this->request->get['sort']  ?? 'o.order_id';
        $order = $this->request->get['order'] ?? 'DESC';
        $page  = (int)($this->request->get['page'] ?? 1);

        $url = '';
        if ($filter_date_start) $url .= '&filter_date_start=' . urlencode($filter_date_start);
        if ($filter_date_end)   $url .= '&filter_date_end=' . urlencode($filter_date_end);
        if ($filter_order_status_id) $url .= '&filter_order_status_id=' . (int)$filter_order_status_id;
        if ($sort)  $url .= '&sort=' . urlencode($sort);
        if ($order) $url .= '&order=' . urlencode($order);

        $this->load->model('report/order_profit');
        $this->load->model('localisation/order_status');

        $limit = $this->config->get('config_limit_admin');

        $filter_data = [
            'filter_date_start' => $filter_date_start,
            'filter_date_end'   => $filter_date_end,
            'filter_order_status_id' => (int)$filter_order_status_id,
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        ];

        $results = $this->model_report_order_profit->getOrderProfit($filter_data);
        $total   = $this->model_report_order_profit->getTotalOrderProfit($filter_data);
        $summary = $this->model_report_order_profit->getSummary($filter_data);

        $data['orders'] = [];

        foreach ($results as $result) {
            $revenue = (float)$result['revenue_products'];
            $cost    = (float)$result['cost_products'];
            $profit  = (float)$result['profit_products'];

            $margin = ($revenue > 0) ? ($profit / $revenue * 100) : 0;

            $data['orders'][] = [
                'order_id' => $result['order_id'],
                'date_added' => $result['date_added'],
                'customer' => trim($result['firstname'] . ' ' . $result['lastname']),
                'order_total' => $this->currency->format($result['order_total'], $this->config->get('config_currency')),
                'revenue_products' => $this->currency->format($revenue, $this->config->get('config_currency')),
                'cost_products' => $this->currency->format($cost, $this->config->get('config_currency')),
                'profit_products' => $this->currency->format($profit, $this->config->get('config_currency')),
                'margin' => number_format($margin, 2) . '%',
                'order_link' => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . (int)$result['order_id'], true)
            ];
        }

        $data['summary_revenue'] = $this->currency->format((float)($summary['revenue_products'] ?? 0), $this->config->get('config_currency'));
        $data['summary_cost']    = $this->currency->format((float)($summary['cost_products'] ?? 0), $this->config->get('config_currency'));
        $data['summary_profit']  = $this->currency->format((float)($summary['profit_products'] ?? 0), $this->config->get('config_currency'));

        // Фильтры
        $data['filter_date_start'] = $filter_date_start;
        $data['filter_date_end'] = $filter_date_end;
        $data['filter_order_status_id'] = (int)$filter_order_status_id;

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Ссылки сортировки
        $toggle_order = ($order == 'ASC') ? 'DESC' : 'ASC';
        $data['sort_order_id'] = $this->url->link('report/order_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=o.order_id&order=' . $toggle_order, true);
        $data['sort_date_added'] = $this->url->link('report/order_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=o.date_added&order=' . $toggle_order, true);
        $data['sort_revenue'] = $this->url->link('report/order_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=revenue_products&order=' . $toggle_order, true);
        $data['sort_cost'] = $this->url->link('report/order_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=cost_products&order=' . $toggle_order, true);
        $data['sort_profit'] = $this->url->link('report/order_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&sort=profit_products&order=' . $toggle_order, true);

        // Пагинация
        $this->load->model('tool/image'); // не обязателен, просто часто грузят что-то; можно убрать

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('report/order_profit', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'),
            ($total) ? (($page - 1) * $limit) + 1 : 0,
            ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit),
            $total,
            ceil($total / $limit)
        );

        // Хлебные крошки
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_reports'),
            'href' => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'], true)
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('report/order_profit', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('report/order_profit', $data));
    }
}
