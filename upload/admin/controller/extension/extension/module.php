<?php
class ControllerExtensionExtensionModule extends Controller {
    private array $error = [];

    public function index(): void {
        $this->load->language('extension/extension/module');

        // Modules
        $this->load->model('setting/module');

        // Extensions
        $this->load->model('setting/extension');

        $this->getList();
    }

    public function install(): void {
        $this->load->language('extension/extension/module');

        // Modules
        $this->load->model('setting/module');

        // Extensions
        $this->load->model('setting/extension');

        if ($this->validate()) {
            $this->model_setting_extension->install('module', $this->request->get['extension']);

            // User Groups
            $this->load->model('user/user_group');

            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/' . $this->request->get['extension']);
            $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/' . $this->request->get['extension']);

            // Call install method if it exsits
            $this->load->controller('extension/module/' . $this->request->get['extension'] . '/install');

            $this->session->data['success'] = $this->language->get('text_success');
        } else {
            $this->session->data['error'] = $this->error['warning'];
        }

        $this->getList();
    }

    public function uninstall(): void {
        $this->load->language('extension/extension/module');

        // Modules
        $this->load->model('setting/module');

        // Extensions
        $this->load->model('setting/extension');

        if ($this->validate()) {
            $this->model_setting_extension->uninstall('module', $this->request->get['extension']);

            $this->model_setting_module->deleteModulesByCode($this->request->get['extension']);

            // Call uninstall method if it exsits
            $this->load->controller('extension/module/' . $this->request->get['extension'] . '/uninstall');

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    public function add(): void {
        $this->load->language('extension/extension/module');

        // Modules
        $this->load->model('setting/module');

        // Extensions
        $this->load->model('setting/extension');

        if ($this->validate()) {
            $this->load->language('module' . '/' . $this->request->get['extension']);

            $this->model_setting_module->addModule($this->request->get['extension'], $this->language->get('heading_title'));

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    public function delete(): void {
        $this->load->language('extension/extension/module');

        // Modules
        $this->load->model('setting/module');

        // Extensions
        $this->load->model('setting/extension');

        if (isset($this->request->get['module_id']) && $this->validate()) {
            $this->model_setting_module->deleteModule($this->request->get['module_id']);

            $this->session->data['success'] = $this->language->get('text_success');
        }

        $this->getList();
    }

    protected function getList() {
        $data['text_layout'] = sprintf($this->language->get('text_layout'), $this->url->link('design/layout', 'user_token=' . $this->session->data['user_token'], true));

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

        $extensions = $this->model_setting_extension->getExtensionsByType('module');

        foreach ($extensions as $key => $value) {
            if (!is_file(DIR_APPLICATION . 'controller/extension/module/' . $value . '.php') && !is_file(DIR_APPLICATION . 'controller/module/' . $value . '.php')) {
                $this->model_setting_extension->uninstall('module', $value);

                unset($extensions[$key]);

                $this->model_setting_module->deleteModulesByCode($value);
            }
        }

        $data['extensions'] = [];

        // Create a new language container so we don't pollute the current one
        $language = new \Language($this->config->get('config_language'));

        // Compatibility code for old extension folders
        $files = glob(DIR_APPLICATION . 'controller/extension/module/*.php');

        if ($files) {
            foreach ($files as $file) {
                $extension = basename($file, '.php');

                $this->load->language('extension/module/' . $extension, 'extension');

                $module_data = [];

                $modules = $this->model_setting_module->getModulesByCode($extension);

                foreach ($modules as $module) {
                    if ($module['setting']) {
                        $setting_info = json_decode($module['setting'], true);
                    } else {
                        $setting_info = [];
                    }

                    $module_data[] = [
                        'module_id' => $module['module_id'],
                        'name'      => $module['name'],
                        'status'    => isset($setting_info['status']) && $setting_info['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                        'edit'      => $this->url->link('extension/module/' . $extension, 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id'], true),
                        'delete'    => $this->url->link('extension/extension/module/delete', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id'], true)
                    ];
                }

                $data['extensions'][] = [
                    'name'      => $this->language->get('extension')->get('heading_title'),
                    'status'    => $this->config->get('module_' . $extension . '_status') ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
                    'module'    => $module_data,
                    'install'   => $this->url->link('extension/extension/module/install', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
                    'uninstall' => $this->url->link('extension/extension/module/uninstall', 'user_token=' . $this->session->data['user_token'] . '&extension=' . $extension, true),
                    'installed' => in_array($extension, $extensions),
                    'edit'      => $this->url->link('extension/module/' . $extension, 'user_token=' . $this->session->data['user_token'], true)
                ];
            }
        }

        $sort_order = [];

        foreach ($data['extensions'] as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $data['extensions']);

        $data['promotion'] = $this->load->controller('extension/extension/promotion');

        $this->response->setOutput($this->load->view('extension/extension/module', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/extension/module')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!isset($this->error['warning']) && isset($this->request->get['extension']) && ((oc_strlen($this->request->get['extension']) < 3) || (oc_strlen($this->request->get['extension']) > 32))) {
            $this->error['warning'] = $this->language->get('error_code_name');
        }

        return !$this->error;
    }
}