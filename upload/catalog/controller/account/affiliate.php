<?php
class ControllerAccountAffiliate extends Controller {
    private array $error = [];

    public function add(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/affiliate', 'customer_token=' . $this->session->data['customer_token'], true);

            $this->response->redirect($this->url->link('affiliate/login', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        if (!$this->config->get('config_affiliate_status')) {
            $this->response->redirect($this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->load->language('account/affiliate');

        $this->document->setTitle($this->language->get('heading_title'));

        // Customers
        $this->load->model('account/customer');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_account_customer->addAffiliate($this->customer->getId(), $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->getForm();
    }

    public function edit(): void {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/affiliate', 'customer_token=' . $this->session->data['customer_token'], true);

            $this->response->redirect($this->url->link('affiliate/login', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->load->language('account/affiliate');

        $this->document->setTitle($this->language->get('heading_title'));

        // Customers
        $this->load->model('account/customer');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_account_customer->editAffiliate($this->customer->getId(), $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->getForm();
    }

    public function getForm(): void {
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true)
        ];

        if ($this->request->get['route'] == 'account/affiliate/add') {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_affiliate'),
                'href' => $this->url->link('account/affiliate/add', 'customer_token=' . $this->session->data['customer_token'], true)
            ];
        } else {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_affiliate'),
                'href' => $this->url->link('account/affiliate/edit', 'customer_token=' . $this->session->data['customer_token'], true)
            ];
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['cheque'])) {
            $data['error_cheque'] = $this->error['cheque'];
        } else {
            $data['error_cheque'] = '';
        }

        if (isset($this->error['paypal'])) {
            $data['error_paypal'] = $this->error['paypal'];
        } else {
            $data['error_paypal'] = '';
        }

        if (isset($this->error['bank_account_name'])) {
            $data['error_bank_account_name'] = $this->error['bank_account_name'];
        } else {
            $data['error_bank_account_name'] = '';
        }

        if (isset($this->error['bank_account_number'])) {
            $data['error_bank_account_number'] = $this->error['bank_account_number'];
        } else {
            $data['error_bank_account_number'] = '';
        }

        if (isset($this->error['custom_field'])) {
            $data['error_custom_field'] = $this->error['custom_field'];
        } else {
            $data['error_custom_field'] = [];
        }

        if ($this->request->get['route'] == 'account/affiliate/edit' && $this->request->server['REQUEST_METHOD'] != 'POST') {
            $affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());
        }

        if (isset($this->request->post['company'])) {
            $data['company'] = $this->request->post['company'];
        } elseif (!empty($affiliate_info)) {
            $data['company'] = $affiliate_info['company'];
        } else {
            $data['company'] = '';
        }

        if (isset($this->request->post['website'])) {
            $data['website'] = $this->request->post['website'];
        } elseif (!empty($affiliate_info)) {
            $data['website'] = $affiliate_info['website'];
        } else {
            $data['website'] = '';
        }

        if (isset($this->request->post['tax'])) {
            $data['tax'] = $this->request->post['tax'];
        } elseif (!empty($affiliate_info)) {
            $data['tax'] = $affiliate_info['tax'];
        } else {
            $data['tax'] = '';
        }

        if (isset($this->request->post['payment'])) {
            $data['payment'] = $this->request->post['payment'];
        } elseif (!empty($affiliate_info)) {
            $data['payment'] = $affiliate_info['payment'];
        } else {
            $data['payment'] = 'cheque';
        }

        if (isset($this->request->post['cheque'])) {
            $data['cheque'] = $this->request->post['cheque'];
        } elseif (!empty($affiliate_info)) {
            $data['cheque'] = $affiliate_info['cheque'];
        } else {
            $data['cheque'] = '';
        }

        if (isset($this->request->post['paypal'])) {
            $data['paypal'] = $this->request->post['paypal'];
        } elseif (!empty($affiliate_info)) {
            $data['paypal'] = $affiliate_info['paypal'];
        } else {
            $data['paypal'] = '';
        }

        if (isset($this->request->post['bank_name'])) {
            $data['bank_name'] = $this->request->post['bank_name'];
        } elseif (!empty($affiliate_info)) {
            $data['bank_name'] = $affiliate_info['bank_name'];
        } else {
            $data['bank_name'] = '';
        }

        if (isset($this->request->post['bank_branch_number'])) {
            $data['bank_branch_number'] = $this->request->post['bank_branch_number'];
        } elseif (!empty($affiliate_info)) {
            $data['bank_branch_number'] = $affiliate_info['bank_branch_number'];
        } else {
            $data['bank_branch_number'] = '';
        }

        if (isset($this->request->post['bank_swift_code'])) {
            $data['bank_swift_code'] = $this->request->post['bank_swift_code'];
        } elseif (!empty($affiliate_info)) {
            $data['bank_swift_code'] = $affiliate_info['bank_swift_code'];
        } else {
            $data['bank_swift_code'] = '';
        }

        if (isset($this->request->post['bank_account_name'])) {
            $data['bank_account_name'] = $this->request->post['bank_account_name'];
        } elseif (!empty($affiliate_info)) {
            $data['bank_account_name'] = $affiliate_info['bank_account_name'];
        } else {
            $data['bank_account_name'] = '';
        }

        if (isset($this->request->post['bank_account_number'])) {
            $data['bank_account_number'] = $this->request->post['bank_account_number'];
        } elseif (!empty($affiliate_info)) {
            $data['bank_account_number'] = $affiliate_info['bank_account_number'];
        } else {
            $data['bank_account_number'] = '';
        }

        $data['custom_fields'] = [];

        // Custom Fields
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->customer->getGroupId());

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'affiliate') {
                $data['custom_fields'][] = $custom_field;
            }
        }

        if (!empty($affiliate_info)) {
            $data['affiliate_custom_field'] = json_decode($affiliate_info['custom_field'], true);
        } else {
            $data['affiliate_custom_field'] = [];
        }

        $affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());

        if (!$affiliate_info && $this->config->get('config_affiliate_id')) {
            // Information
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_affiliate_id'));

            if ($information_info) {
                $data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/agree', 'information_id=' . $this->config->get('config_affiliate_id'), true), $information_info['title']);
            } else {
                $data['text_agree'] = '';
            }
        } else {
            $data['text_agree'] = '';
        }

        if (isset($this->request->post['agree'])) {
            $data['agree'] = $this->request->post['agree'];
        } else {
            $data['agree'] = false;
        }

        $data['action'] = $this->url->link($this->request->get['route'], 'customer_token=' . $this->session->data['customer_token'], true);
        $data['back'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/affiliate', $data));
    }

    protected function validate() {
        $keys = [
            'payment',
            'cheque',
            'paypal',
            'bank_account_name',
            'bank_account_number',
            'agree'
        ];

        foreach ($keys as $key) {
            if (!isset($this->request->post[$key])) {
                $this->request->post[$key] = '';
            }
        }

        if ($this->request->post['payment'] == 'cheque' && !$this->request->post['cheque']) {
            $this->error['cheque'] = $this->language->get('error_cheque');
        } elseif (($this->request->post['payment'] == 'paypal') && ((oc_strlen($this->request->post['paypal']) > 96) || !filter_var($this->request->post['paypal'], FILTER_VALIDATE_EMAIL))) {
            $this->error['paypal'] = $this->language->get('error_paypal');
        } elseif ($this->request->post['payment'] == 'bank') {
            if ($this->request->post['bank_account_name'] == '') {
                $this->error['bank_account_name'] = $this->language->get('error_bank_account_name');
            }

            if ($this->request->post['bank_account_number'] == '') {
                $this->error['bank_account_number'] = $this->language->get('error_bank_account_number');
            }
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'affiliate') {
                if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !preg_match(html_entity_decode($custom_field['validation'], ENT_QUOTES, 'UTF-8'), $this->request->post['custom_field'][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
                }
            }
        }

        // Validate agree only if customer not already an affiliate
        $affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());

        if (!$affiliate_info && $this->config->get('config_affiliate_id')) {
            // Information
            $this->load->model('catalog/information');

            $information_info = $this->model_catalog_information->getInformation($this->config->get('config_affiliate_id'));

            if ($information_info && !isset($this->request->post['agree'])) {
                $this->error['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
            }
        }

        return !$this->error;
    }
}