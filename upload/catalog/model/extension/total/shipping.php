<?php
class ModelExtensionTotalShipping extends Model {
    public function getTotal(array &$totals): void {
        if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
            $totals['totals'][] = [
                'code'       => 'shipping',
                'title'      => $this->session->data['shipping_method']['title'],
                'value'      => $this->session->data['shipping_method']['cost'],
                'sort_order' => $this->config->get('total_shipping_sort_order')
            ];

            if ($this->session->data['shipping_method']['tax_class_id']) {
                $tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

                foreach ($tax_rates as $tax_rate) {
                    if (!isset($totals['taxes'][$tax_rate['tax_rate_id']])) {
                        $totals['taxes'][$tax_rate['tax_rate_id']] = $tax_rate['amount'];
                    } else {
                        $totals['taxes'][$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                    }
                }
            }

            $totals['total'] += $this->session->data['shipping_method']['cost'];
        }
    }
}