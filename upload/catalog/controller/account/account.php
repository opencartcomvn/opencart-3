<?php
class ControllerAccountAccount extends Controller {
    public function index(): void {
        if (!$this->customer->isLogged() || (!isset($this->request->get['member_token']) || !isset($this->session->data['member_token']) || ($this->request->get['member_token'] != $this->session->data['member_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/account', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/account');

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', 'member_token=' . $this->session->data['member_token'], true)
        ];

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['credit_cards'] = [];

        $files = glob(DIR_APPLICATION . 'controller/extension/credit_card/*.php');

        foreach ($files as $file) {
            $code = basename($file, '.php');

            if ($this->config->get('payment_' . $code . '_status') && $this->config->get('payment_' . $code . '_card')) {
                $this->load->language('extension/credit_card/' . $code, 'extension');

                $data['credit_cards'][] = [
                    'name' => $this->language->get('extension')->get('heading_title'),
                    'href' => $this->url->link('extension/credit_card/' . $code, '', true)
                ];
            }
        }

        if ($this->config->get('total_reward_status')) {
            $data['reward'] = $this->url->link('account/reward', 'member_token=' . $this->session->data['member_token'], true);
        } else {
            $data['reward'] = '';
        }

        // Affiliate
        if ($this->config->get('config_affiliate_status')) {
            $data['affiliate'] = $this->url->link('account/affiliate', 'member_token=' . $this->session->data['member_token'], true);

            // Customers
            $this->load->model('account/customer');

            $affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());

            if ($affiliate_info) {
                $data['tracking'] = $this->url->link('account/tracking', 'member_token=' . $this->session->data['member_token'], true);
            } else {
                $data['tracking'] = '';
            }
        } else {
            $data['affiliate'] = '';
        }

        $data['edit'] = $this->url->link('account/edit', 'member_token=' . $this->session->data['member_token'], true);
        $data['password'] = $this->url->link('account/password', 'member_token=' . $this->session->data['member_token'], true);
        $data['address'] = $this->url->link('account/address', 'member_token=' . $this->session->data['member_token'], true);
        $data['payment_method'] = $this->url->link('account/payment_method', 'member_token=' . $this->session->data['member_token'], true);
        $data['wishlist'] = $this->url->link('account/wishlist', 'member_token=' . $this->session->data['member_token']);
        $data['order'] = $this->url->link('account/order', 'member_token=' . $this->session->data['member_token'], true);
        $data['recurring'] = $this->url->link('account/recurring', 'member_token=' . $this->session->data['member_token'], true);
        $data['subscription'] = $this->url->link('account/subscription', 'member_token=' . $this->session->data['member_token'], true);
        $data['download'] = $this->url->link('account/download', 'member_token=' . $this->session->data['member_token'], true);
        $data['returns'] = $this->url->link('account/returns', 'member_token=' . $this->session->data['member_token'], true);
        $data['transaction'] = $this->url->link('account/transaction', 'member_token=' . $this->session->data['member_token'], true);
        $data['newsletter'] = $this->url->link('account/newsletter', 'member_token=' . $this->session->data['member_token'], true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/account', $data));
    }

    public function country(): void {
        $json = [];

        // Countries
        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        if ($country_info) {
            // Zones
            $this->load->model('localisation/zone');

            $json = [
                'country_id'        => $country_info['country_id'],
                'name'              => $country_info['name'],
                'iso_code_2'        => $country_info['iso_code_2'],
                'iso_code_3'        => $country_info['iso_code_3'],
                'address_format'    => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone'              => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status'            => $country_info['status']
            ];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}