<?php
class ControllerCommonLogin extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('common/login');

        $this->document->setTitle($this->language->get('heading_title'));

        if ($this->user->isLogged() && isset($this->request->get['user_token']) && ($this->request->get['user_token'] == $this->session->data['user_token'])) {
            $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->session->data['user_token'] = oc_token(32);

            if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], HTTP_SERVER) === 0 || strpos($this->request->post['redirect'], HTTPS_SERVER) === 0)) {
                $this->response->redirect($this->request->post['redirect'] . '&user_token=' . $this->session->data['user_token']);
            } else {
                $this->response->redirect($this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true));
            }
        }

        if ((isset($this->session->data['user_token']) && !isset($this->request->get['user_token'])) || ((isset($this->request->get['user_token']) && (isset($this->session->data['user_token']) && ($this->request->get['user_token'] != $this->session->data['user_token']))))) {
            $this->error['warning'] = $this->language->get('error_token');
        }

        if (isset($this->error['error_attempts'])) {
            $data['error_warning'] = $this->error['error_attempts'];
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

        $data['action'] = $this->url->link('common/login', '', true);

        if (isset($this->request->post['username'])) {
            $data['username'] = $this->request->post['username'];
        } else {
            $data['username'] = '';
        }

        if (isset($this->request->post['password'])) {
            $data['password'] = $this->request->post['password'];
        } else {
            $data['password'] = '';
        }

        if (isset($this->request->get['route'])) {
            $route = $this->request->get['route'];

            unset($this->request->get['route']);
            unset($this->request->get['user_token']);

            $url = '';

            if ($this->request->get) {
                $url .= http_build_query($this->request->get);
            }

            $data['redirect'] = $this->url->link($route, $url, true);
        } else {
            $data['redirect'] = '';
        }

        if ($this->config->get('config_password')) {
            $data['forgotten'] = $this->url->link('common/forgotten', '', true);
        } else {
            $data['forgotten'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('common/login', $data));
    }

    protected function validate() {
        // Stop any undefined index messages.
        $keys = [
            'username',
            'password',
            'redirect'
        ];

        foreach ($keys as $key) {
            if (!isset($this->request->post[$key])) {
                $this->request->post[$key] = '';
            }
        }

        if (!$this->request->post['username'] || !$this->request->post['password']) {
            $this->error['warning'] = $this->language->get('error_login');
        } else {
            // Users
            $this->load->model('user/user');

            // Check how many login attempts have been made.
            $login_info = $this->model_user_user->getLogins($this->request->post['username']);

            if ($login_info && ($login_info['total'] >= $this->config->get('config_login_attempts')) && strtotime('-1 hour') < strtotime($login_info['date_modified'])) {
                $this->error['error_attempts'] = $this->language->get('error_attempts');
            }
        }

        if (!$this->error) {
            if (!$this->user->login($this->request->post['username'], html_entity_decode($this->request->post['password'], ENT_QUOTES, 'UTF-8'))) {
                $this->error['warning'] = $this->language->get('error_login');

                $this->model_user_user->addLogin($this->request->post['username']);

                unset($this->session->data['user_token']);
            } else {
                $this->model_user_user->deleteLoginAttempts($this->request->post['username']);
            }
        }

        return !$this->error;
    }
}