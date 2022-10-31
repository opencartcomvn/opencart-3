<?php
class ControllerAccountTracking extends Controller {
    public function index(): object|null {
        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/tracking', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        if (!$this->config->get('config_affiliate_status')) {
            $this->response->redirect($this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true));
        }

        // Customers
        $this->load->model('account/customer');

        $affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());

        if ($affiliate_info) {
            $this->load->language('account/tracking');

            $this->document->setTitle($this->language->get('heading_title'));

            $data['breadcrumbs'] = [];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/home')
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('text_account'),
                'href' => $this->url->link('account/account', '', true)
            ];

            $data['breadcrumbs'][] = [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('account/tracking', '', true)
            ];

            $data['code'] = $affiliate_info['tracking'];
            $data['continue'] = $this->url->link('account/account', 'customer_token=' . $this->session->data['customer_token'], true);
            $data['customer_token'] = $this->session->data['customer_token'];
            $data['text_description'] = sprintf($this->language->get('text_description'), $this->config->get('config_name'));

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('account/tracking', $data));
        } else {
            return new \Action('error/not_found');
        }

        return null;
    }

    public function autocomplete(): void {
        $json = [];

        if (isset($this->request->get['filter_name'])) {
            $filter_name = $this->request->get['filter_name'];
        } else {
            $filter_name = '';
        }

        if (isset($this->request->get['tracking'])) {
            $tracking = $this->request->get['tracking'];
        } else {
            $tracking = '';
        }

        if (!$this->customer->isLogged() || (!isset($this->request->get['customer_token']) || !isset($this->session->data['customer_token']) || ($this->request->get['customer_token'] != $this->session->data['customer_token']))) {
            $this->session->data['redirect'] = $this->url->link('account/password', '', true);

            $json['redirect'] = $this->url->link('account/login', '', true);
        }

        if (!$json) {
            // Products
            $this->load->model('catalog/product');

            $filter_data = [
                'filter_name' => $this->request->get['filter_name'],
                'start'       => 0,
                'limit'       => 5
            ];

            $results = $this->model_catalog_product->getProducts($filter_data);

            foreach ($results as $result) {
                $json[] = [
                    'name' => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
                    'link' => str_replace('&amp;', '&', $this->url->link('product/product', 'extension_id=' . $result['extension_id'] . '&tracking=' . $tracking))
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}