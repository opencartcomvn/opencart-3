<?php
class ControllerLocalisationLanguage extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));

        // Languages
        $this->load->model('localisation/language');

        $this->getList();
    }

    public function add(): void {
        $this->load->language('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));

        // Languages
        $this->load->model('localisation/language');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_language->addLanguage($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));

        // Languages
        $this->load->model('localisation/language');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
            $this->model_localisation_language->editLanguage($this->request->get['language_id'], $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('localisation/language');

        $this->document->setTitle($this->language->get('heading_title'));

        // Languages
        $this->load->model('localisation/language');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {
            foreach ((array)$this->request->post['selected'] as $language_id) {
                $this->model_localisation_language->deleteLanguage($language_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    protected function getList() {
        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'name';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

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
            'href' => $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        $data['add'] = $this->url->link('localisation/language/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('localisation/language/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['languages'] = [];

        $filter_data = [
            'sort'  => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        ];

        $language_total = $this->model_localisation_language->getTotalLanguages();

        $results = $this->model_localisation_language->getLanguages($filter_data);

        foreach ($results as $result) {
            $data['languages'][] = [
                'language_id' => $result['language_id'],
                'name'        => $result['name'] . (($result['code'] == $this->config->get('config_language')) ? $this->language->get('text_default') : null),
                'code'        => $result['code'],
                'sort_order'  => $result['sort_order'],
                'edit'        => $this->url->link('localisation/language/edit', 'user_token=' . $this->session->data['user_token'] . '&language_id=' . $result['language_id'] . $url, true)
            ];
        }

        if (isset($this->error['warning'])) {
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

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_name'] = $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
        $data['sort_code'] = $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url, true);
        $data['sort_sort_order'] = $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . '&sort=sort_order' . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new \Pagination();
        $pagination->total = $language_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($language_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($language_total - $this->config->get('config_limit_admin'))) ? $language_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $language_total, ceil($language_total / $this->config->get('config_limit_admin')));

        $data['sort'] = $sort;
        $data['order'] = $order;

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/language_list', $data));
    }

    protected function getForm() {
        $data['text_form'] = !isset($this->request->get['language_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['code'])) {
            $data['error_code'] = $this->error['code'];
        } else {
            $data['error_code'] = '';
        }

        if (isset($this->error['locale'])) {
            $data['error_locale'] = $this->error['locale'];
        } else {
            $data['error_locale'] = '';
        }

        $url = '';

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
            'href' => $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . $url, true)
        ];

        if (!isset($this->request->get['language_id'])) {
            $data['action'] = $this->url->link('localisation/language/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        } else {
            $data['action'] = $this->url->link('localisation/language/edit', 'user_token=' . $this->session->data['user_token'] . '&language_id=' . $this->request->get['language_id'] . $url, true);
        }

        $data['cancel'] = $this->url->link('localisation/language', 'user_token=' . $this->session->data['user_token'] . $url, true);

        if (isset($this->request->get['language_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $language_info = $this->model_localisation_language->getLanguage($this->request->get['language_id']);
        }

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($language_info)) {
            $data['name'] = $language_info['name'];
        } else {
            $data['name'] = '';
        }

        if (isset($this->request->post['code'])) {
            $data['code'] = $this->request->post['code'];
        } elseif (!empty($language_info)) {
            $data['code'] = $language_info['code'];
        } else {
            $data['code'] = '';
        }

        $data['languages'] = [];

        $folders = glob(DIR_LANGUAGE . '*', GLOB_ONLYDIR);

        foreach ($folders as $folder) {
            $data['languages'][] = basename($folder);
        }

        if (isset($this->request->post['locale'])) {
            $data['locale'] = $this->request->post['locale'];
        } elseif (!empty($language_info)) {
            $data['locale'] = $language_info['locale'];
        } else {
            $data['locale'] = '';
        }

        if (isset($this->request->post['sort_order'])) {
            $data['sort_order'] = $this->request->post['sort_order'];
        } elseif (!empty($language_info)) {
            $data['sort_order'] = $language_info['sort_order'];
        } else {
            $data['sort_order'] = 1;
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = (int)$this->request->post['status'];
        } elseif (!empty($language_info)) {
            $data['status'] = $language_info['status'];
        } else {
            $data['status'] = 1;
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('localisation/language_form', $data));
    }

    protected function validateForm() {
        if (!$this->user->hasPermission('modify', 'localisation/language')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((oc_strlen($this->request->post['name']) < 3) || (oc_strlen($this->request->post['name']) > 32)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (oc_strlen($this->request->post['code']) < 2) {
            $this->error['code'] = $this->language->get('error_code');
        }

        if (!$this->request->post['locale']) {
            $this->error['locale'] = $this->language->get('error_locale');
        }

        $language_info = $this->model_localisation_language->getLanguageByCode($this->request->post['code']);

        if (!isset($this->request->get['language_id'])) {
            if ($language_info) {
                $this->error['warning'] = $this->language->get('error_exists');
            }
        } else {
            if ($language_info && ($this->request->get['language_id'] != $language_info['language_id'])) {
                $this->error['warning'] = $this->language->get('error_exists');
            }
        }

        return !$this->error;
    }

    protected function validateDelete() {
        if (!$this->user->hasPermission('modify', 'localisation/language')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        // Orders
        $this->load->model('sale/order');

        // Stores
        $this->load->model('setting/store');

        foreach ((array)$this->request->post['selected'] as $language_id) {
            $language_info = $this->model_localisation_language->getLanguage($language_id);

            if ($language_info) {
                if ($this->config->get('config_language') == $language_info['code']) {
                    $this->error['warning'] = $this->language->get('error_default');
                }

                if ($this->config->get('config_admin_language') == $language_info['code']) {
                    $this->error['warning'] = $this->language->get('error_admin');
                }

                $store_total = $this->model_setting_store->getTotalStoresByLanguage($language_info['code']);

                if ($store_total) {
                    $this->error['warning'] = sprintf($this->language->get('error_store'), $store_total);
                }
            }

            $order_total = $this->model_sale_order->getTotalOrdersByLanguageId($language_id);

            if ($order_total) {
                $this->error['warning'] = sprintf($this->language->get('error_order'), $order_total);
            }
        }

        return !$this->error;
    }
}