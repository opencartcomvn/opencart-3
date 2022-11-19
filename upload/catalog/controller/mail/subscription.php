<?php
class ControllerMailSubscription extends Controller {
    public function index(string &$route, array &$args, &$output): void {
        if (isset($args[0])) {
            $subscription_id = $args[0];
        } else {
            $subscription_id = 0;
        }

        if (isset($args[1]['subscription'])) {
            $subscription = $args[1]['subscription'];
        } else {
            $subscription = [];
        }

        if (isset($args[2])) {
            $comment = $args[2];
        } else {
            $comment = '';
        }

        if (isset($args[3])) {
            $notify = $args[3];
        } else {
            $notify = '';
        }
        /*
        $subscription['order_product_id']
        $subscription['customer_id']
        $subscription['order_id']
        $subscription['subscription_plan_id']
        $subscription['customer_payment_id'],
        $subscription['name']
        $subscription['description']
        $subscription['trial_price']
        $subscription['trial_frequency']
        $subscription['trial_cycle']
        $subscription['trial_duration']
        $subscription['trial_remaining']
        $subscription['trial_status']
        $subscription['price']
        $subscription['frequency']
        $subscription['cycle']
        $subscription['duration']
        $subscription['remaining']
        $subscription['date_next']
        $subscription['status']
        */

        if ($subscription['trial_status'] && $subscription['trial_duration'] && $subscription['trial_remaining']) {
            $date_next = date('Y-m-d', strtotime('+' . $subscription['trial_cycle'] . ' ' . $subscription['trial_frequency']));
        } elseif ($subscription['duration'] && $subscription['remaining']) {
            $date_next = date('Y-m-d', strtotime('+' . $subscription['cycle'] . ' ' . $subscription['frequency']));
        }

        // Subscription
        $this->load->model('account/subscription');

        $filter_data = [
            'filter_subscription_id'        => $subscription_id,
            'filter_date_next'              => $date_next,
            'filter_subscription_status_id' => $this->config->get('config_subscription_active_status_id'),
            'start'                         => 0,
            'limit'                         => 1
        ];

        $subscriptions = $this->model_account_subscription->getSubscriptions($filter_data);

        if ($subscriptions) {
            $this->load->language('mail/subscription');

            foreach ($subscriptions as $value) {
                // Only match the latest order ID of the same customer ID
                // since new subscriptions cannot be re-added with the same
                // order ID; only as a new order ID added by an extension
                if ($value['customer_id'] == $subscription['customer_id'] && $value['order_id'] == $subscription['order_id']) {
                    // Payment Methods
                    $this->load->model('account/payment_method');

                    $payment_info = $this->model_account_payment_method->getPaymentMethod($value['customer_id'], $value['customer_payment_id']);

                    if ($payment_info) {
                        // Subscription
                        $this->load->model('checkout/subscription');

                        $subscription_order_product = $this->model_checkout_subscription->getSubscriptionByOrderProductId($value['order_product_id']);

                        if ($subscription_order_product) {
                            // Orders
                            $this->load->model('account/order');

                            // Order Products
                            $order_product = $this->model_account_order->getProduct($value['order_id'], $value['order_product_id']);

                            if ($order_product && $order_product['order_product_id'] == $subscription['order_product_id']) {
                                $products = $this->cart->getProducts();

                                $description = '';

                                foreach ($products as $product) {
                                    if ($product['product_id'] == $order_product['product_id']) {
                                        $trial_price = $this->currency->format($this->tax->calculate($value['trial_price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                                        $trial_cycle = $value['trial_cycle'];
                                        $trial_frequency = $this->language->get('text_' . $value['trial_frequency']);
                                        $trial_duration = $value['trial_duration'];

                                        if ($product['subscription']['trial_status']) {
                                            $description .= sprintf($this->language->get('text_subscription_trial'), $trial_price, $trial_cycle, $trial_frequency, $trial_duration);
                                        }

                                        $price = $this->currency->format($this->tax->calculate($value['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                                        $cycle = $value['cycle'];
                                        $frequency = $this->language->get('text_' . $value['frequency']);
                                        $duration = $value['duration'];

                                        if ($duration) {
                                            $description .= sprintf($this->language->get('text_subscription_duration'), $price, $cycle, $frequency, $duration);
                                        } else {
                                            $description .= sprintf($this->language->get('text_subscription_cancel'), $price, $cycle, $frequency);
                                        }
                                    }
                                }

                                // Both descriptions need to match to maintain the
                                // mutual agreement of the subscription in accordance
                                // with the service providers
                                if ($description && $description == $subscription['description']) {
                                    // Orders
                                    $this->load->model('checkout/order');

                                    $order_info = $this->model_checkout_order->getOrder($value['order_id']);

                                    if ($order_info) {
                                        // Stores
                                        $this->load->model('setting/store');

                                        // Settings
                                        $this->load->model('setting/setting');

                                        $store_info = $this->model_setting_store->getStore($order_info['store_id']);

                                        if ($store_info) {
                                            $store_logo = html_entity_decode($this->model_setting_setting->getValue('config_logo', $store_info['store_id']), ENT_QUOTES, 'UTF-8');
                                            $store_name = html_entity_decode($store_info['name'], ENT_QUOTES, 'UTF-8');

                                            $store_url = $store_info['url'];

                                            $from = html_entity_decode($store_info['config_email'], ENT_QUOTES, 'UTF-8');
                                        } else {
                                            $store_logo = html_entity_decode($this->config->get('config_logo'), ENT_QUOTES, 'UTF-8');
                                            $store_name = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

                                            $store_url = HTTP_SERVER;

                                            $from = html_entity_decode($this->config->get('config_email'), ENT_QUOTES, 'UTF-8');
                                        }

                                        // Subscription Status
                                        $subscription_status_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "subscription_status` WHERE `subscription_status_id` = '" . (int)$value['subscription_status_id'] . "' AND `language_id` = '" . (int)$order_info['language_id'] . "'");

                                        if ($subscription_status_query->num_rows) {
                                            $data['order_status'] = $subscription_status_query->row['name'];
                                        } else {
                                            $data['order_status'] = '';
                                        }

                                        // Languages
                                        $this->load->model('localisation/language');

                                        $language_info = $this->model_localisation_language->getLanguage($order_info['language_id']);

                                        // We need to compare both language IDs as they both need to match.
                                        if ($language_info) {
                                            $language_code = $language_info['code'];
                                        } else {
                                            $language_code = $this->config->get('config_language');
                                        }

                                        // Load the language for any mails using a different country code and prefixing it, so it does not pollute the main data pool.
                                        $language = new \Language($order_info['language_code']);
                                        $language->load($order_info['language_code']);
                                        $language->load('mail/subscription');

                                        // Image files
                                        $this->load->model('tool/image');

                                        if (is_file(DIR_IMAGE . $store_logo)) {
                                            $data['logo'] = $store_url . 'image/' . $store_logo;
                                        } else {
                                            $data['logo'] = '';
                                        }

                                        $data['text_greeting'] = sprintf($language->get('text_greeting'), $store_name);

                                        $data['store'] = $store_name;
                                        $data['store_url'] = $store_url;

                                        $data['customer_id'] = $order_info['customer_id'];

                                        // Subscription
                                        if ($comment && $notify) {
                                            $data['comment'] = nl2br($comment);
                                        } else {
                                            $data['comment'] = '';
                                        }

                                        $data['description'] = $value['description'];

                                        $subject = sprintf($language->get('text_subject'), $store_name, $order_info['order_id']);

                                        $data['title'] = sprintf($language->get('text_subject'), $store_name, $order_info['order_id']);
                                        $data['link'] = $store_url . 'index.php?route=account/subscription/info&subscription_id=' . $subscription_id;

                                        $data['order_id'] = $order_info['order_id'];
                                        $data['date_added'] = date($language->get('date_format_short'), strtotime($order_info['date_added']));
                                        $data['payment_method'] = $order_info['payment_method'];
                                        $data['email'] = $order_info['email'];
                                        $data['telephone'] = $order_info['telephone'];
                                        $data['ip'] = $order_info['ip'];

                                        // Order Totals
                                        $data['totals'] = [];

                                        $order_totals = $this->model_checkout_order->getTotals($order_info['order_id']);

                                        foreach ($order_totals as $order_total) {
                                            $data['totals'][] = [
                                                'title' => $order_total['title'],
                                                'text'  => $this->currency->format($order_total['value'], $order_info['currency_code'], $order_info['currency_value']),
                                            ];
                                        }

                                        // Products
                                        $data['name'] = $order_product['name'];
                                        $data['quantity'] = $order_product['quantity'];
                                        $data['price'] = $this->currency->format($order_product['price'], $order_info['currency_code'], $order_info['currency_value']);
                                        $data['total'] = $this->currency->format($order_product['total'], $order_info['currency_code'], $order_info['currency_value']);

                                        $data['order'] = $this->url->link('account/order/info', 'order_id=' . $order_info['order_id']);
                                        $data['product'] = $this->url->link('product/product', 'product_id=' . $order_product['product_id']);

                                        if ($this->config->get('payment_' . $payment_info['code'] . '_status')) {
                                            $this->load->model('extension/payment/' . $payment_info['code']);

                                            // Promotion
                                            if (property_exists($this->{'model_extension_payment_' . $payment_info['code']}, 'promotion')) {
                                                /*
                                                  * The extension must create a new order
                                                  * The trial status and the status must
                                                  * also be handled accordingly to complete
                                                  * this transaction. It must not be charged
                                                  * to the customer until the next billing cycle
                                                */
                                                $subscription_status_id = $this->{'model_extension_payment_' . $payment_info['code']}->promotion($value['subscription_id']);

                                                if ($store_info) {
                                                    $config_subscription_active_status_id = $this->model_setting_setting->getValue('config_subscription_active_status_id', $store_info['store_id']);
                                                } else {
                                                    $config_subscription_active_status_id = $this->config->get('config_subscription_active_status_id');
                                                }

                                                // Transaction
                                                if ($config_subscription_active_status_id == $subscription_status_id) {
                                                    $filter_data = [
                                                        'filter_subscription_status_id' => $subscription_status_id,
                                                        'start'                         => 0,
                                                        'limit'                         => 1
                                                    ];

                                                    $next_subscriptions = $this->model_account_subscription->getSubscriptions($filter_data);

                                                    if ($next_subscriptions) {
                                                        foreach ($next_subscriptions as $next_subscription) {
                                                            // Validate the latest subscription values with the ones edited
                                                            // by promotional extensions
                                                            if ($next_subscription['subscription_id'] != $value['subscription_id'] && $next_subscription['order_id'] != $value['order_id'] && $value['order_id'] != $subscription['order_id'] && $next_subscription['order_product_id'] != $value['order_product_id'] && $next_subscription['customer_id'] == $value['customer_id']) {
                                                                $this->load->model('account/customer');

                                                                $customer_info = $this->model_account_customer->getCustomer($next_subscription['customer_id']);

                                                                $frequencies = [
                                                                    'day',
                                                                    'week',
                                                                    'semi_month',
                                                                    'month',
                                                                    'year'
                                                                ];

                                                                // We need to validate frequencies in compliance of the admin subscription plans
                                                                // as with the use of the APIs
                                                                if ($customer_info && (int)$next_subscription['cycle'] >= 0 && in_array($next_subscription['frequency'], $frequencies)) {
                                                                    if ($next_subscription['frequency'] == 'semi_month') {
                                                                        $period = strtotime("2 weeks");
                                                                    } else {
                                                                        $period = strtotime($next_subscription['cycle'] . ' ' . $next_subscription['frequency']);
                                                                    }

                                                                    // New customer once the trial period has ended
                                                                    $customer_period = strtotime($customer_info['date_added']);

                                                                    $trial_period = 0;
                                                                    $trial_cycle = 0;

                                                                    // Trial
                                                                    if ($next_subscription['trial_status'] && (int)$next_subscription['trial_cycle'] >= 0 && in_array($next_subscription['trial_frequency'], $frequencies)) {
                                                                        if ($next_subscription['trial_frequency'] == 'semi_month') {
                                                                            $trial_period = strtotime("2 weeks");
                                                                        } else {
                                                                            $trial_period = strtotime($next_subscription['trial_cycle'] . ' ' . $next_subscription['trial_frequency']);
                                                                        }

                                                                        $trial_period = ($trial_period - $customer_period);
                                                                        $trial_cycle = round($trial_period / (60 * 60 * 24));
                                                                    }

                                                                    // Calculates the remaining days between the subscription
                                                                    // promotional period and the date added period
                                                                    $period = ($period - $customer_period);

                                                                    // Calculate remaining period of each features
                                                                    $cycle = round($period / (60 * 60 * 24));

                                                                    // Promotional subscription plans for full membership must be identical
                                                                    // until the time period has exceeded. Therefore, we need to match the
                                                                    // cycle period with the current time period; including pro rata
                                                                    if ($next_subscription['status'] && ($cycle >= 0 && $cycle <= $next_subscription['cycle']) && ($trial_cycle == 0 && !$next_subscription['trial_status']) && $next_subscription['subscription_plan_id'] == $value['subscription_plan_id'] && $value['subscription_plan_id'] == $subscription['subscription_plan_id']) {
                                                                        $subscription_id = $next_subscription['subscription_id'];

                                                                        // Order Products
                                                                        $order_product = $this->model_account_order->getProduct($next_subscription['order_id'], $next_subscription['order_product_id']);

                                                                        if ($order_product) {
                                                                            $order_info = $this->model_account_order->getOrder($next_subscription['order_id']);

                                                                            if ($order_info) {
                                                                                // Products
                                                                                $this->load->model('catalog/product');

                                                                                $product_subscription_info = $this->model_catalog_product->getSubscription($order_product['product_id'], $next_subscription['subscription_plan_id']);

                                                                                if ($product_subscription_info) {
                                                                                    // Adds the current amount in order for promotional subscription extensions
                                                                                    // to balance the new transaction amount to reflect the change on the next
                                                                                    // billing cycle
                                                                                    $transactions = $this->model_account_subscription->getTransactions($next_subscription['subscription_id']);

                                                                                    if ($transactions) {
                                                                                        if ($next_subscription['duration'] && $next_subscription['remaining']) {
                                                                                            $date_next = strtotime('+' . $next_subscription['cycle'] . ' ' . $next_subscription['frequency']);
                                                                                        }

                                                                                        $dates = array_column($transactions, 'date_added');

                                                                                        $date_added = max($dates);
                                                                                        $date_added = strtotime($date_added);

                                                                                        foreach ($transactions as $transaction) {
                                                                                            // If the date next don't match with the latest date added of the subscription,
                                                                                            // we add an amount value of 0. Store owners then need to review the orders
                                                                                            // that are related with these transactions
                                                                                            if (strtotime($transaction['date_added']) == $date_added && $date_added != $date_next && $transaction['payment_method'] == $order_info['payment_method'] && $transaction['payment_code'] == $order_info['payment_code']) {
                                                                                                $this->model_account_subscription->addTransaction($next_subscription['subscription_id'], $next_subscription['order_id'], $language->get('text_promotion'), 0, $transaction['type'], $transaction['payment_method'], $transaction['payment_code']);
                                                                                            }
                                                                                        }
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        // Mail
                                        if ($this->config->get('config_mail_engine')) {
                                            $mail = new \Mail($this->config->get('config_mail_engine'));
                                            $mail->parameter = $this->config->get('config_mail_parameter');
                                            $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                                            $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                                            $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                                            $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                                            $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                                            $mail->setTo($order_info['email']);
                                            $mail->setFrom($from);
                                            $mail->setSender($store_name);
                                            $mail->setSubject($subject);
                                            $mail->setHtml($this->load->view('mail/subscription', $data));
                                            $mail->send();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}