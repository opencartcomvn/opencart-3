<?php
class ControllerSaleReturns extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('sale/returns');

        $this->document->setTitle($this->language->get('heading_title'));

        // Returns
        $this->load->model('sale/returns');

        $this->getList();
    }

    public function add(): void {
        $this->load->language('sale/returns');

        $this->document->setTitle($this->language->get('heading_title'));

        // Returns
        $this->load->model('sale/returns');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_sale_returns->addReturn($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_return_id'])) {
                $url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
            }

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_product'])) {
                $url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_return_status_id'])) {
                $url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('sale/returns');

        $this->document->setTitle($this->language->get('heading_title'));

        // Returns
        $this->load->model('sale/returns');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_sale_returns->editReturn($this->request->get['return_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_return_id'])) {
                $url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
            }

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_product'])) {
                $url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_return_status_id'])) {
                $url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('sale/returns');

        $this->document->setTitle($this->language->get('heading_title'));

        // Returns
        $this->load->model('sale/returns');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ((array)$this->request->post['selected'] as $return_id) {
                $this->model_sale_returns->deleteReturn($return_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['filter_return_id'])) {
                $url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
            }

            if (isset($this->request->get['filter_order_id'])) {
                $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
            }

            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_product'])) {
                $url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_model'])) {
                $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_return_status_id'])) {
                $url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
            }

            if (isset($this->request->get['filter_date_added'])) {
                $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['filter_return_id'])) {
            $filter_return_id = (int)$this->request->get['filter_return_id'];
        } else {
            $filter_return_id = '';
        }

        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = '';
        }

        if (isset($this->request->get['filter_product'])) {
            $filter_product = $this->request->get['filter_product'];
        } else {
            $filter_product = '';
        }

        if (isset($this->request->get['filter_model'])) {
            $filter_model = $this->request->get['filter_model'];
        } else {
            $filter_model = '';
        }

        if (isset($this->request->get['filter_return_status_id'])) {
            $filter_return_status_id = (int)$this->request->get['filter_return_status_id'];
        } else {
            $filter_return_status_id = '';
        }

        if (isset($this->request->get['filter_date_added'])) {
            $filter_date_added = $this->request->get['filter_date_added'];
        } else {
            $filter_date_added = '';
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'r.return_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_return_id'])) {
            $url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_product'])) {
            $url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_return_status_id'])) {
            $url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['add'] = $this->url->link('sale/returns/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('sale/returns/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['returns'] = [];

        $filter_data = [
            'filter_return_id'        => $filter_return_id,
            'filter_order_id'         => $filter_order_id,
            'filter_customer'         => $filter_customer,
            'filter_product'          => $filter_product,
            'filter_model'            => $filter_model,
            'filter_return_status_id' => $filter_return_status_id,
            'filter_date_added'       => $filter_date_added,
            'filter_date_modified'    => $filter_date_modified,
            'sort'                    => $sort,
            'order'                   => $order,
            'start'                   => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit'                   => $this->config->get('config_limit_admin')
        ];

        $return_total = $this->model_sale_returns->getTotalReturns($filter_data);

        $results = $this->model_sale_returns->getReturns($filter_data);

        foreach ($results as $result) {
            $data['returns'][] = [
                'return_id'     => $result['return_id'],
                'order_id'      => $result['order_id'],
                'customer'      => $result['customer'],
                'product'       => $result['product'],
                'model'         => $result['model'],
                'return_status' => $result['return_status'],
                'date_added'    => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'date_modified' => date($this->language->get('date_format_short'), strtotime($result['date_modified'])),
                'edit'          => $this->url->link('sale/returns/edit', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $result['return_id'] . $url, true)
            ];
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];

            unset($this->session->data['error']);
        } elseif (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = [];
        }

        $url = '';

        if (isset($this->request->get['filter_return_id'])) {
            $url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_product'])) {
            $url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_return_status_id'])) {
            $url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        // Returns Status
        $this->load->model('localisation/returns_status');

        $data['return_statuses'] = $this->model_localisation_returns_status->getReturnStatuses();

        $data['sort_return_id'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=r.return_id' . $url, true);
        $data['sort_order_id'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=r.order_id' . $url, true);
        $data['sort_customer'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=customer' . $url, true);
        $data['sort_product'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=r.product' . $url, true);
        $data['sort_model'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=r.model' . $url, true);
        $data['sort_status'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
        $data['sort_date_added'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=r.date_added' . $url, true);
        $data['sort_date_modified'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . '&sort=r.date_modified' . $url, true);

        $pagination = new \Pagination();
        $pagination->total = $return_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($return_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($return_total - $this->config->get('config_limit_admin'))) ? $return_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $return_total, ceil($return_total / $this->config->get('config_limit_admin')));

        $data['filter_return_id'] = $filter_return_id;
        $data['filter_order_id'] = $filter_order_id;
        $data['filter_customer'] = $filter_customer;
        $data['filter_product'] = $filter_product;
        $data['filter_model'] = $filter_model;
        $data['filter_return_status_id'] = $filter_return_status_id;
        $data['filter_date_added'] = $filter_date_added;
        $data['filter_date_modified'] = $filter_date_modified;

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/returns_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['return_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['return_id'])) {
            $data['return_id'] = (int)$this->request->get['return_id'];
        } else {
            $data['return_id'] = 0;
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['order_id'])) {
            $data['error_order_id'] = $this->error['order_id'];
        } else {
            $data['error_order_id'] = '';
        }

        if (isset($this->error['firstname'])) {
            $data['error_firstname'] = $this->error['firstname'];
        } else {
            $data['error_firstname'] = '';
        }

        if (isset($this->error['lastname'])) {
            $data['error_lastname'] = $this->error['lastname'];
        } else {
            $data['error_lastname'] = '';
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }

        if (isset($this->error['telephone'])) {
            $data['error_telephone'] = $this->error['telephone'];
        } else {
            $data['error_telephone'] = '';
        }

        if (isset($this->error['product'])) {
            $data['error_product'] = $this->error['product'];
        } else {
            $data['error_product'] = '';
        }

        if (isset($this->error['model'])) {
            $data['error_model'] = $this->error['model'];
        } else {
            $data['error_model'] = '';
        }

        $url = '';

        if (isset($this->request->get['filter_return_id'])) {
            $url .= '&filter_return_id=' . $this->request->get['filter_return_id'];
        }

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_product'])) {
            $url .= '&filter_product=' . urlencode(html_entity_decode($this->request->get['filter_product'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_model'])) {
            $url .= '&filter_model=' . urlencode(html_entity_decode($this->request->get['filter_model'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_return_status_id'])) {
            $url .= '&filter_return_status_id=' . $this->request->get['filter_return_status_id'];
        }

        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        if (!isset($this->request->get['return_id'])) {
            $data['action'] = $this->url->link('sale/returns/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('sale/returns/edit', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $this->request->get['return_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('sale/returns', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['return_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $return_info = $this->model_sale_returns->getReturn($this->request->get['return_id']);
        }

        if (isset($this->request->get['return_id'])) {
            $data['return_id'] = (int)$this->request->get['return_id'];
        } else {
            $data['return_id'] = 0;
        }

        if (!empty($return_info)) {
            $data['order_id'] = $return_info['order_id'];
        } else {
            $data['order_id'] = '';
        }

        if (!empty($return_info)) {
            $data['date_ordered'] = ($return_info['date_ordered'] != '0000-00-00' ? $return_info['date_ordered'] : '');
        } else {
            $data['date_ordered'] = '';
        }

        if (!empty($return_info)) {
            $data['customer'] = $return_info['customer'];
        } else {
            $data['customer'] = '';
        }

        if (!empty($return_info)) {
            $data['customer_id'] = $return_info['customer_id'];
        } else {
            $data['customer_id'] = '';
        }

        if (!empty($return_info)) {
            $data['firstname'] = $return_info['firstname'];
        } else {
            $data['firstname'] = '';
        }

        if (!empty($return_info)) {
            $data['lastname'] = $return_info['lastname'];
        } else {
            $data['lastname'] = '';
        }

        if (!empty($return_info)) {
            $data['email'] = $return_info['email'];
        } else {
            $data['email'] = '';
        }

        if (!empty($return_info)) {
            $data['telephone'] = $return_info['telephone'];
        } else {
            $data['telephone'] = '';
        }

        if (!empty($return_info)) {
            $data['product'] = $return_info['product'];
        } else {
            $data['product'] = '';
        }

        if (!empty($return_info)) {
            $data['product_id'] = $return_info['product_id'];
        } else {
            $data['product_id'] = '';
        }

        if (!empty($return_info)) {
            $data['model'] = $return_info['model'];
        } else {
            $data['model'] = '';
        }

        if (!empty($return_info)) {
            $data['quantity'] = $return_info['quantity'];
        } else {
            $data['quantity'] = '';
        }

        if (!empty($return_info)) {
            $data['opened'] = $return_info['opened'];
        } else {
            $data['opened'] = '';
        }

        $this->load->model('localisation/returns_reason');

        $data['return_reasons'] = $this->model_localisation_returns_reason->getReturnReasons();

        if (!empty($return_info)) {
            $data['return_reason_id'] = $return_info['return_reason_id'];
        } else {
            $data['return_reason_id'] = 0;
        }

        $this->load->model('localisation/returns_action');

        $data['return_actions'] = $this->model_localisation_returns_action->getReturnActions();

        if (!empty($return_info)) {
            $data['return_action_id'] = $return_info['return_action_id'];
        } else {
            $data['return_action_id'] = 0;
        }

        if (!empty($return_info)) {
            $data['comment'] = $return_info['comment'];
        } else {
            $data['comment'] = '';
        }

        $this->load->model('localisation/returns_status');

        $data['return_statuses'] = $this->model_localisation_returns_status->getReturnStatuses();

        if (!empty($return_info)) {
            $data['return_status_id'] = $return_info['return_status_id'];
        } else {
            $data['return_status_id'] = 0;
        }

        $data['user_token'] = $this->session->data['user_token'];

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('sale/returns_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'sale/returns')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['order_id'])) {
            $this->error['order_id'] = $this->language->get('error_order_id');
        }

        if ((oc_strlen($this->request->post['firstname']) < 1) || (oc_strlen($this->request->post['firstname']) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if ((oc_strlen($this->request->post['lastname']) < 1) || (oc_strlen($this->request->post['lastname']) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if ((oc_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email');
        }

        if ((oc_strlen($this->request->post['telephone']) < 3) || (oc_strlen($this->request->post['telephone']) > 32)) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        }

        if ((oc_strlen($this->request->post['product']) < 1) || (oc_strlen($this->request->post['product']) > 255)) {
            $this->error['product'] = $this->language->get('error_product');
        }

        if ((oc_strlen($this->request->post['model']) < 1) || (oc_strlen($this->request->post['model']) > 64)) {
            $this->error['model'] = $this->language->get('error_model');
        }

        if (empty($this->request->post['return_reason_id'])) {
            $this->error['reason'] = $this->language->get('error_reason');
        }

        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'sale/returns')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function history(): void {
        $this->load->language('sale/returns');

        // Returns
        $this->load->model('sale/returns');

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['histories'] = [];

        $results = $this->model_sale_returns->getHistories($this->request->get['return_id'], ($page - 1) * 10, 10);

        foreach ($results as $result) {
            $data['histories'][] = [
                'notify'     => $result['notify'] ? $this->language->get('text_yes') : $this->language->get('text_no'),
                'status'     => $result['status'],
                'comment'    => nl2br($result['comment']),
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            ];
        }

        $history_total = $this->model_sale_returns->getTotalHistories($this->request->get['return_id']);

        $pagination = new \Pagination();
        $pagination->total = $history_total;
        $pagination->page = $page;
        $pagination->limit = 10;
        $pagination->url = $this->url->link('sale/returns/history', 'user_token=' . $this->session->data['user_token'] . '&return_id=' . $this->request->get['return_id'] . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

        $this->response->setOutput($this->load->view('sale/returns_history', $data));
    }

    public function addHistory(): void {
        $this->load->language('sale/returns');

        $json = [];

        if (!$this->user->hasPermission('modify', 'sale/returns')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            if (isset($this->request->get['return_id'])) {
                $return_id = (int)$this->request->get['return_id'];
            } else {
                $return_id = 0;
            }

            // Returns
            $this->load->model('sale/returns');

            $return_info = $this->model_sale_returns->getReturn($return_id);

            if (!$return_info) {
                $json['error'] = $this->language->get('error_return');
            }

            if (!$json) {
                $this->model_sale_returns->addHistory($this->request->get['return_id'], $this->request->post['return_status_id'], $this->request->post['comment'], $this->request->post['notify']);

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}