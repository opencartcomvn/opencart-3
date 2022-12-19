<?php
class ControllerCommonFileManager extends Controller {
    public function index(): void {
        $this->load->language('common/filemanager');

        // Find which protocol to use to pass the full image link back
        if ($this->request->server['HTTPS']) {
            $server = HTTPS_CATALOG;
        } else {
            $server = HTTP_CATALOG;
        }

        if (isset($this->request->get['filter_name'])) {
            $filter_name = rtrim(str_replace(['*', '/', '\\'], '', $this->request->get['filter_name']), '/');
        } else {
            $filter_name = '';
        }

        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . 'catalog/' . str_replace('*', '', $this->request->get['directory']), '/');
        } else {
            $directory = DIR_IMAGE . 'catalog';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $directories = [];
        $files = [];
        $data['images'] = [];

        // Image files
        $this->load->model('tool/image');

        if (substr(str_replace('\\', '/', realpath($directory) . '/' . $filter_name), 0, strlen(DIR_IMAGE . 'catalog')) == str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
            // Get directories
            $directories = glob($directory . '/' . $filter_name . '*', GLOB_ONLYDIR);

            if (!$directories) {
                $directories = [];
            }

            // Get files
            $files = glob($directory . $filter_name . '*{/,.ico,.jpg,.jpeg,.png,.gif,.webp,.JPG,.JPEG,.PNG,.GIF}', GLOB_BRACE);

            if (!$files) {
                $files = [];
            }
        }

        // Merge directories and files
        $images = array_merge((array)$directories, (array)$files);

        // Get total number of files and directories
        $image_total = count($images);

        // Split the array based on current page number and max number of items per page of 10
        $images = array_splice($images, ($page - 1) * 16, 16);

        foreach ($images as $image) {
            $name = str_split(basename($image), 14);

            if (is_dir($image)) {
                $url = '';

                if (isset($this->request->get['target'])) {
                    $url .= '&target=' . $this->request->get['target'];
                }

                if (isset($this->request->get['thumb'])) {
                    $url .= '&thumb=' . $this->request->get['thumb'];
                }

                $data['images'][] = [
                    'thumb' => '',
                    'name'  => implode(' ', $name),
                    'type'  => 'directory',
                    'path'  => oc_substr($image, oc_strlen(DIR_IMAGE)),
                    'href'  => $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . '&directory=' . urlencode(oc_substr($image, oc_strlen(DIR_IMAGE . 'catalog/'))) . $url, true)
                ];
            } elseif (is_file($image)) {
                $data['images'][] = [
                    'thumb' => $this->model_tool_image->resize(oc_substr($image, oc_strlen(DIR_IMAGE)), 100, 100),
                    'name'  => implode(' ', $name),
                    'type'  => 'image',
                    'path'  => oc_substr($image, oc_strlen(DIR_IMAGE)),
                    'href'  => $server . 'image/' . oc_substr($image, oc_strlen(DIR_IMAGE))
                ];
            }
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->request->get['directory'])) {
            $data['directory'] = urlencode($this->request->get['directory']);
        } else {
            $data['directory'] = '';
        }

        if (isset($this->request->get['filter_name'])) {
            $data['filter_name'] = $this->request->get['filter_name'];
        } else {
            $data['filter_name'] = '';
        }

        // Return the target ID for the file manager to set the value
        if (isset($this->request->get['target'])) {
            $data['target'] = $this->request->get['target'];
        } else {
            $data['target'] = '';
        }

        // Return the thumbnail for the file manager to show a thumbnail
        if (isset($this->request->get['thumb'])) {
            $data['thumb'] = $this->request->get['thumb'];
        } else {
            $data['thumb'] = '';
        }

        // Parent
        $url = '';

        if (isset($this->request->get['directory'])) {
            $pos = strrpos($this->request->get['directory'], '/');

            if ($pos) {
                $url .= '&directory=' . urlencode(substr($this->request->get['directory'], 0, $pos));
            }
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        $data['parent'] = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url, true);

        // Refresh
        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode($this->request->get['directory']);
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        $data['refresh'] = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $url = '';

        if (isset($this->request->get['directory'])) {
            $url .= '&directory=' . urlencode(html_entity_decode($this->request->get['directory'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_name'])) {
            $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['target'])) {
            $url .= '&target=' . $this->request->get['target'];
        }

        if (isset($this->request->get['thumb'])) {
            $url .= '&thumb=' . $this->request->get['thumb'];
        }

        $pagination = new \Pagination();
        $pagination->total = $image_total;
        $pagination->page = $page;
        $pagination->limit = 16;
        $pagination->url = $this->url->link('common/filemanager', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $this->response->setOutput($this->load->view('common/filemanager', $data));
    }

    public function upload(): void {
        $this->load->language('common/filemanager');

        $json = [];

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . 'catalog/' . $this->request->get['directory'], '/');
        } else {
            $directory = DIR_IMAGE . 'catalog';
        }

        // Check its a directory
        if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE . 'catalog')) != str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
            $json['error'] = $this->language->get('error_directory');
        }

        if (!$json) {
            // Check if multiple files are uploaded or just one
            $files = [];

            if (!empty($this->request->files['file']['name']) && is_array($this->request->files['file']['name'])) {
                foreach (array_keys($this->request->files['file']['name']) as $key) {
                    $files[] = [
                        'name' => $this->request->files['file']['name'][$key],
                        'type' => $this->request->files['file']['type'][$key],
                        'tmp_name' => $this->request->files['file']['tmp_name'][$key],
                        'error' => $this->request->files['file']['error'][$key],
                        'size' => $this->request->files['file']['size'][$key]
                    ];
                }
            }

            foreach ($files as $file) {
                if (is_file($file['tmp_name'])) {
                    // Sanitize the filename
                    $filename = basename(html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8'));

                    // Validate the filename length
                    if ((oc_strlen($filename) < 3) || (oc_strlen($filename) > 255)) {
                        $json['error'] = $this->language->get('error_filename');
                    }

                    // Allowed file extension types
                    // Allowed file extension types
                    $allowed = [
                        'ico',
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'webp',
                        'JPG',
                        'JPEG',
                        'PNG',
                        'GIF'
                    ];

                    if (!in_array(oc_strtolower(oc_substr(strrchr($filename, '.'), 1)), $allowed)) {
                        $json['error'] = $this->language->get('error_filetype');
                    }

                    // Allowed file mime types
                    $allowed = [
                        'image/x-icon',
                        'image/jpeg',
                        'image/pjpeg',
                        'image/png',
                        'image/x-png',
                        'image/gif',
                        'image/webp'
                    ];

                    if (!in_array($file['type'], $allowed)) {
                        $json['error'] = $this->language->get('error_filetype');
                    }

                    if ($file['size'] > $this->config->get('config_file_max_size')) {
                        $json['error'] = $this->language->get('error_filesize');
                    }

                    // Return any upload error
                    if ($file['error'] != UPLOAD_ERR_OK) {
                        $json['error'] = $this->language->get('error_upload_' . $file['error']);
                    }
                } else {
                    $json['error'] = $this->language->get('error_upload');
                }

                if (!$json) {
                    move_uploaded_file($file['tmp_name'], $directory . '/' . $filename);
                }
            }
        }

        if (!$json) {
            $json['success'] = $this->language->get('text_uploaded');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function folder(): void {
        $this->load->language('common/filemanager');

        $json = [];

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        // Make sure we have the correct directory
        if (isset($this->request->get['directory'])) {
            $directory = rtrim(DIR_IMAGE . 'catalog/' . $this->request->get['directory'], '/');
        } else {
            $directory = DIR_IMAGE . 'catalog';
        }

        // Check its a directory
        if (!is_dir($directory) || substr(str_replace('\\', '/', realpath($directory)), 0, strlen(DIR_IMAGE . 'catalog')) != str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
            $json['error'] = $this->language->get('error_directory');
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            // Sanitize the folder name
            $folder = basename(html_entity_decode($this->request->post['folder'], ENT_QUOTES, 'UTF-8'));

            // Validate the filename length
            if ((oc_strlen($folder) < 3) || (oc_strlen($folder) > 128)) {
                $json['error'] = $this->language->get('error_folder');
            }

            // Check if directory already exists or not
            if (is_dir($directory . '/' . $folder)) {
                $json['error'] = $this->language->get('error_exists');
            }
        }

        if (!$json) {
            mkdir($directory . '/' . $folder, 0777);
            chmod($directory . '/' . $folder, 0777);
            @touch($directory . '/' . $folder . '/' . 'index.html');

            $json['success'] = $this->language->get('text_directory');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function delete(): void {
        $this->load->language('common/filemanager');

        $json = [];

        // Check user has permission
        if (!$this->user->hasPermission('modify', 'common/filemanager')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (isset($this->request->post['path'])) {
            $paths = $this->request->post['path'];
        } else {
            $paths = [];
        }

        // Loop through each path to run validations
        foreach ($paths as $path) {
            // Check path exists
            if ($path == DIR_IMAGE . 'catalog' || substr(str_replace('\\', '/', realpath(DIR_IMAGE . $path)), 0, strlen(DIR_IMAGE . 'catalog')) != str_replace('\\', '/', DIR_IMAGE . 'catalog')) {
                $json['error'] = $this->language->get('error_delete');
                break;
            }
        }

        if (!$json) {
            // Loop through each path
            foreach ($paths as $path) {
                $path = rtrim(DIR_IMAGE . $path, '/');

                // If path is just a file delete it
                if (is_file($path)) {
                    unlink($path);
                    // If path is a directory beging deleting each file and sub folder
                } elseif (is_dir($path)) {
                    $files = [];

                    // Make path into an array
                    $path = [$path];

                    // While the path array is still populated keep looping through
                    while (count($path) != 0) {
                        $next = array_shift($path);

                        if (is_dir($next)) {
                            foreach (glob(trim($next, '/') . '/{*,.[!.]*,..?*}', GLOB_BRACE) as $file) {
                                // If directory add to path array
                                if (is_dir($file)) {
                                    $path[] = $file . '/*';
                                }

                                // Add the file to the files to be deleted array
                                $files[] = $file;
                            }
                        }
                    }

                    // Reverse sort the file array
                    rsort($files);

                    foreach ($files as $file) {
                        // If file just delete
                        if (is_file($file)) {
                            unlink($file);
                            // If directory use the remove directory function
                        } elseif (is_dir($file)) {
                            rmdir($file);
                        }
                    }
                }
            }

            $json['success'] = $this->language->get('text_delete');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}