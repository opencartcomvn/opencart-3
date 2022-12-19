<?php
class ControllerAccountRecurring extends Controller {
    public function index(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/recurring', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/recurring');

        $this->document->setTitle($this->language->get('heading_title'));

        $url = '';

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('account/recurring', 'customer_token=' . $this->session->data['customer_token'] . $url, true)
        ];

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['recurrings'] = [];

        // Recurring
        $this->load->model('account/recurring');

        $recurring_total = $this->model_account_recurring->getTotalRecurrings();

        $results = $this->model_account_recurring->getRecurrings(($page - 1) * 10, 10);

        foreach ($results as $result) {
            if ($result['status']) {
                $status = $this->language->get('text_status_' . $result['status']);
            } else {
                $status = '';
            }

            $data['recurrings'][] = [
                'order_recurring_id' => $result['order_recurring_id'],
                'product'            => $result['product_name'],
                'status'             => $status,
                'date_added'         => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'view'               => $this->url->link('account/recurring/info', 'customer_token=' . $this->session->data['customer_token'] . '&order_recurring_id=' . $result['order_recurring_id'], true),
            ];
        }

        $pagination = new \Pagination();
        $pagination->total = $recurring_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('account/recurring', 'customer_token=' . $this->session->data['customer_token'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['continue'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/recurring_list', $data));
    }

    public function info(): void {
        $this->load->language('account/recurring');

        if (isset($this->request->get['order_recurring_id'])) {
            $order_recurring_id = (int)$this->request->get['order_recurring_id'];
        } else {
            $order_recurring_id = 0;
        }

        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/recurring/info', 'order_recurring_id=' . $order_recurring_id, true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        // Recurring
        $this->load->model('account/recurring');

        $recurring_info = $this->model_account_recurring->getRecurring($order_recurring_id);

        if ($recurring_info) {
            $this->document->setTitle($this->language->get('text_recurring'));

            $url = '';

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home'),
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true),
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/recurring', 'customer_token=' . $this->session->data['customer_token'] . $url, true),
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_recurring'),
                'href' => $this->url->link('account/recurring/info', 'customer_token=' . $this->session->data['customer_token'] . '&order_recurring_id=' . $this->request->get['order_recurring_id'] . $url, true),
            ];

            $data['order_recurring_id'] = (int)$this->request->get['order_recurring_id'];

            $data['date_added'] = date($this->language->get('date_format_short'), strtotime($recurring_info['date_added']));

            if ($recurring_info['status']) {
                $data['status'] = $this->language->get('text_status_' . $recurring_info['status']);
            } else {
                $data['status'] = '';
            }

            $data['payment_method'] = $recurring_info['payment_method'];

            $data['order_id'] = $recurring_info['order_id'];
            $data['product_name'] = $recurring_info['product_name'];
            $data['product_quantity'] = $recurring_info['product_quantity'];
            $data['recurring_description'] = $recurring_info['recurring_description'];
            $data['reference'] = $recurring_info['reference'];

            // Transactions
            $data['transactions'] = [];

            $results = $this->model_account_recurring->getRecurringTransactions($this->request->get['order_recurring_id']);

            foreach ($results as $result) {
                $data['transactions'][] = [
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'type'       => $result['type'],
                    'amount'     => $this->currency->format($result['amount'], $recurring_info['currency_code'])
                ];
            }

            $data['order'] = $this->url->link('account/order/info', 'customer_token=' . $this->session->data['customer_token'] . '&order_id=' . $recurring_info['order_id'], true);
            $data['product'] = $this->url->link('product/product', 'customer_token=' . $this->session->data['customer_token'] . '&product_id=' . $recurring_info['product_id'], true);

            $data['recurring'] = $this->load->controller('extension/subscription/' . $recurring_info['payment_code']);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('account/recurring_info', $data));
        } else {
            $this->document->setTitle($this->language->get('text_recurring'));

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/recurring', 'customer_token=' . $this->session->data['customer_token'], true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_recurring'),
                'href' => $this->url->link('account/recurring/info', 'customer_token=' . $this->session->data['customer_token'] . '&order_recurring_id=' . $order_recurring_id, true)
            ];

            $data['continue'] = $this->url->link('account/recurring', 'customer_token=' . $this->session->data['customer_token'], true);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }
}