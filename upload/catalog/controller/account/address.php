<?php
class ControllerAccountAddress extends Controller {
    private array $error = [];

    public function index(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/address', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        // Addresses
        $this->load->model('account/address');

        $this->getList();
    }

    public function add(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/address', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');

        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        // Addresses
        $this->load->model('account/address');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_account_address->addAddress($this->customer->getId(), $this->request->post);

            $this->session->data['success'] = $this->language->get('text_add');

            $this->response->redirect($this->url->link('account/address', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->getForm();
    }

    public function edit(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/address', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');

        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        // Addresses
        $this->load->model('account/address');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_account_address->editAddress($this->request->get['address_id'], $this->request->post);

            // Default Shipping Address
            if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
                $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->request->get['address_id']);

                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
            }

            // Default Payment Address
            if (isset($this->session->data['payment_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address']['address_id'])) {
                $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->request->get['address_id']);

                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
            }

            $this->session->data['success'] = $this->language->get('text_edit');

            $this->response->redirect($this->url->link('account/address', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $json = [];

        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/address', '', true);

            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('account/login', '', true));
        }

        $this->load->language('account/address');

        $this->document->setTitle($this->language->get('heading_title'));

        // Addresses
        $this->load->model('account/address');

        if ($this->model_account_address->getTotalAddresses() == 1) {
            $json['warning'] = $this->language->get('error_delete');
        }

        if ($this->customer->getAddressId() == $this->request->get['address_id']) {
            $json['warning'] = $this->language->get('error_default');
        }

        if (!$json && isset($this->request->get['address_id'])) {
            $this->model_account_address->deleteAddress($this->request->get['address_id']);

            // Default Shipping Address
            if (isset($this->session->data['shipping_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['shipping_address']['address_id'])) {
                unset($this->session->data['shipping_address']);
                unset($this->session->data['shipping_method']);
                unset($this->session->data['shipping_methods']);
            }

            // Default Payment Address
            if (isset($this->session->data['payment_address']['address_id']) && ($this->request->get['address_id'] == $this->session->data['payment_address']['address_id'])) {
                unset($this->session->data['payment_address']);
                unset($this->session->data['payment_method']);
                unset($this->session->data['payment_methods']);
            }

            $this->session->data['success'] = $this->language->get('text_delete');

            $json['success'] = str_replace('&amp;', '&', $this->url->link('account/address', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function getList() {
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
            'href' => $this->url->link('account/address', 'customer_token=' . $this->session->data['customer_token'], true)
        ];

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['addresses'] = [];

        $results = $this->model_account_address->getAddresses();

        foreach ($results as $result) {
            $data['addresses'][] = [
                'address_id' => $result['address_id'],
                'address'    => $result['address_format'],
                'update'     => $this->url->link('account/address/edit', 'customer_token=' . $this->session->data['customer_token'] . '&address_id=' . $result['address_id'], true),
                'delete'     => $this->url->link('account/address/delete', 'customer_token=' . $this->session->data['customer_token'] . '&address_id=' . $result['address_id'], true)
            ];
        }

        $data['customer_token'] = $this->session->data['customer_token'];

        $data['add'] = $this->url->link('account/address/add', 'customer_token=' . $this->session->data['customer_token'], true);
        $data['back'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/address_list', $data));
    }

    protected function getForm() {
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
            'href' => $this->url->link('account/address', 'customer_token=' . $this->session->data['customer_token'], true)
        ];

        if (!isset($this->request->get['address_id'])) {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_address_add'),
                'href' => $this->url->link('account/address/add', 'customer_token=' . $this->session->data['customer_token'], true)
            ];
        } else {
            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_address_edit'),
                'href' => $this->url->link('account/address/edit', 'customer_token=' . $this->session->data['customer_token'] . '&address_id=' . $this->request->get['address_id'], true)
            ];
        }

        $data['text_address'] = !isset($this->request->get['address_id']) ? $this->language->get('text_address_add') : $this->language->get('text_address_edit');

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

        if (isset($this->error['address_1'])) {
            $data['error_address_1'] = $this->error['address_1'];
        } else {
            $data['error_address_1'] = '';
        }

        if (isset($this->error['city'])) {
            $data['error_city'] = $this->error['city'];
        } else {
            $data['error_city'] = '';
        }

        if (isset($this->error['postcode'])) {
            $data['error_postcode'] = $this->error['postcode'];
        } else {
            $data['error_postcode'] = '';
        }

        if (isset($this->error['country'])) {
            $data['error_country'] = $this->error['country'];
        } else {
            $data['error_country'] = '';
        }

        if (isset($this->error['zone'])) {
            $data['error_zone'] = $this->error['zone'];
        } else {
            $data['error_zone'] = '';
        }

        if (isset($this->error['custom_field'])) {
            $data['error_custom_field'] = $this->error['custom_field'];
        } else {
            $data['error_custom_field'] = [];
        }

        if (isset($this->error['default'])) {
            $data['error_default'] = $this->error['default'];
        } else {
            $data['error_default'] = '';
        }

        if (!isset($this->request->get['address_id'])) {
            $data['action'] = $this->url->link('account/address/add', 'customer_token=' . $this->session->data['customer_token'], true);
        } else {
            $data['action'] = $this->url->link('account/address/edit', 'customer_token=' . $this->session->data['customer_token'] . '&address_id=' . $this->request->get['address_id'], true);
        }

        if (isset($this->request->get['address_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $address_info = $this->model_account_address->getAddress($this->request->get['address_id']);
        }

        if (isset($this->request->post['firstname'])) {
            $data['firstname'] = $this->request->post['firstname'];
        } elseif (!empty($address_info)) {
            $data['firstname'] = $address_info['firstname'];
        } else {
            $data['firstname'] = '';
        }

        if (isset($this->request->post['lastname'])) {
            $data['lastname'] = $this->request->post['lastname'];
        } elseif (!empty($address_info)) {
            $data['lastname'] = $address_info['lastname'];
        } else {
            $data['lastname'] = '';
        }

        if (isset($this->request->post['company'])) {
            $data['company'] = $this->request->post['company'];
        } elseif (!empty($address_info)) {
            $data['company'] = $address_info['company'];
        } else {
            $data['company'] = '';
        }

        if (isset($this->request->post['address_1'])) {
            $data['address_1'] = $this->request->post['address_1'];
        } elseif (!empty($address_info)) {
            $data['address_1'] = $address_info['address_1'];
        } else {
            $data['address_1'] = '';
        }

        if (isset($this->request->post['address_2'])) {
            $data['address_2'] = $this->request->post['address_2'];
        } elseif (!empty($address_info)) {
            $data['address_2'] = $address_info['address_2'];
        } else {
            $data['address_2'] = '';
        }

        if (isset($this->request->post['postcode'])) {
            $data['postcode'] = $this->request->post['postcode'];
        } elseif (!empty($address_info)) {
            $data['postcode'] = $address_info['postcode'];
        } else {
            $data['postcode'] = '';
        }

        if (isset($this->request->post['city'])) {
            $data['city'] = $this->request->post['city'];
        } elseif (!empty($address_info)) {
            $data['city'] = $address_info['city'];
        } else {
            $data['city'] = '';
        }

        if (isset($this->request->post['country_id'])) {
            $data['country_id'] = (int)$this->request->post['country_id'];
        } elseif (!empty($address_info)) {
            $data['country_id'] = $address_info['country_id'];
        } else {
            $data['country_id'] = $this->config->get('config_country_id');
        }

        if (isset($this->request->post['zone_id'])) {
            $data['zone_id'] = (int)$this->request->post['zone_id'];
        } elseif (!empty($address_info)) {
            $data['zone_id'] = $address_info['zone_id'];
        } else {
            $data['zone_id'] = '';
        }

        if (isset($this->request->post['custom_field'])) {
            $data['address_custom_field'] = $this->request->post['custom_field'];
        } elseif (isset($address_info['custom_field'])) {
            $data['address_custom_field'] = $address_info['custom_field'];
        } else {
            $data['address_custom_field'] = [];
        }

        // Uploaded files
        $this->load->model('tool/upload');

        // Countries
        $this->load->model('localisation/country');

        $data['countries'] = $this->model_localisation_country->getCountries();

        $data['custom_fields'] = [];

        // Custom fields
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'address') {
                if ($custom_field['type'] == 'file' && isset($data['address_custom_field'][$custom_field['custom_field_id']])) {
                    $code = $data['address_custom_field'][$custom_field['custom_field_id']];

                    $upload_info = $this->model_tool_upload->getUploadByCode($code);

                    $data['address_custom_field'][$custom_field['custom_field_id']] = [];

                    if ($upload_info) {
                        $data['address_custom_field'][$custom_field['custom_field_id']]['name'] = $upload_info['name'];
                        $data['address_custom_field'][$custom_field['custom_field_id']]['code'] = $upload_info['code'];
                    } else {
                        $data['address_custom_field'][$custom_field['custom_field_id']]['name'] = '';
                        $data['address_custom_field'][$custom_field['custom_field_id']]['code'] = $code;
                    }

                    $data['custom_fields'][] = $custom_field;
                } else {
                    $data['custom_fields'][] = $custom_field;
                }
            }
        }

        if (isset($this->request->post['default'])) {
            $data['default'] = $this->request->post['default'];
        } elseif (isset($this->request->get['address_id'])) {
            $data['default'] = $this->customer->getAddressId() == $this->request->get['address_id'];
        } else {
            $data['default'] = false;
        }

        $data['back'] = $this->url->link('account/address', 'customer_token=' . $this->session->data['customer_token'], true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/address_form', $data));
    }

    protected function validateForm() {
        $keys = [
            'firstname',
            'lastname',
            'address_1',
            'address_2',
            'city',
            'postcode',
            'country_id',
            'zone_id'
        ];

        foreach ($keys as $key) {
            if (!isset($this->request->post[$key])) {
                $this->request->post[$key] = '';
            }
        }

        if ((oc_strlen($this->request->post['firstname']) < 1) || (oc_strlen($this->request->post['firstname']) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        }

        if ((oc_strlen($this->request->post['lastname']) < 1) || (oc_strlen($this->request->post['lastname']) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        }

        if ((oc_strlen($this->request->post['address_1']) < 3) || (oc_strlen($this->request->post['address_1']) > 128)) {
            $this->error['address_1'] = $this->language->get('error_address_1');
        }

        if ((oc_strlen($this->request->post['city']) < 2) || (oc_strlen($this->request->post['city']) > 128)) {
            $this->error['city'] = $this->language->get('error_city');
        }

        // Countries
        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->post['country_id']);

        if ($country_info && $country_info['postcode_required'] && (oc_strlen($this->request->post['postcode']) < 2 || oc_strlen($this->request->post['postcode']) > 10)) {
            $this->error['postcode'] = $this->language->get('error_postcode');
        }

        if ($this->request->post['country_id'] == '' || !is_numeric($this->request->post['country_id'])) {
            $this->error['country'] = $this->language->get('error_country');
        }

        if (!isset($this->request->post['zone_id']) || $this->request->post['zone_id'] == '' || !is_numeric($this->request->post['zone_id'])) {
            $this->error['zone'] = $this->language->get('error_zone');
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'address') {
                if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !preg_match(html_entity_decode($custom_field['validation'], ENT_QUOTES, 'UTF-8'), $this->request->post['custom_field'][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_regex'), $custom_field['name']);
                }
            }
        }

        if (isset($this->request->get['address_id']) && ($this->customer->getAddressId() == $this->request->get['address_id']) && !$this->request->post['default']) {
            $this->error['default'] = $this->language->get('error_default');
        }

        return !$this->error;
    }

    public function editAddress(): void {
        $json = [];

        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/address', '', true);

            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('account/login', '', true));
        }

        if (isset($this->request->get['address_id'])) {
            $json['redirect'] = str_replace('&amp;', '&', $this->url->link('account/address/edit', 'customer_token=' . $this->session->data['customer_token'] . '&address_id=' . $this->request->get['address_id'], true));
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}