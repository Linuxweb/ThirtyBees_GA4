<?php
/* analytics4.php
 *
 * Copyright (c) 2026 LinuxISP (Pty) Ltd
 * You (being anyone who is not LinuxISP (Pty) Ltd may download and use this plugin / code in your own website in conjunction with a registered and active Google account. If your Google account is terminated for any reason, you may not use this plugin / code or part thereof.
 * Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.
 * Grant: “Licensor hereby grants Licensee a non-exclusive, non-transferable, revocable right to use, run, and modify the Object Code and Source Code solely for Licensee’s internal purposes. Licensee may not distribute, transmit, publish, sublicense, rent, lease, sell, or otherwise transfer the Object Code or Source Code or Derivative Works to any third party.”
 * 
 * 
 * @author     Ruben Venter (ruben@linuxweb.co.za)
 * @version    1.0
 * @date       01/27/2026
 *
 * @link       https://github.com/Linuxweb/ThirtyBees_GA4/
 */

if (!defined('_TB_VERSION_')) {
    exit;
}

class Analytics4 extends Module
{
    public function __construct()
    {
        $this->name = 'analytics4';
        $this->tab = 'analytics_stats';
        $this->version = '1.0';
        $this->author = 'Linuxweb';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Google Analytics 4');
        $this->description = $this->l('This module connects your ThirtyBees e-commerce store to Google Analytics 4 (GA4) so Google can track what happens on your website.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->registerHook('displayOrderConfirmation')
            && Configuration::updateValue('ANALYTICS4_MEASUREMENT_ID', '');
    }

    public function uninstall()
    {
        return parent::uninstall()
            && Configuration::deleteByName('ANALYTICS4_MEASUREMENT_ID');
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submitAnalytics4')) {
            $measurementId = Tools::getValue('ANALYTICS4_MEASUREMENT_ID');
            Configuration::updateValue('ANALYTICS4_MEASUREMENT_ID', $measurementId);
            $output .= $this->displayConfirmation($this->l('Settings saved'));
        }

        $this->context->smarty->assign([
            'measurement_id' => Configuration::get('ANALYTICS4_MEASUREMENT_ID'),
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/config.tpl');
    }

    /**
     * Hook to load the remote Gtag library in the <head>
     */
    public function hookDisplayHeader($params)
    {
        $id = Configuration::get('ANALYTICS4_MEASUREMENT_ID');
        if (!$id) return '';

        return '
        <script async src="https://www.googletagmanager.com/gtag/js?id='.$id.'"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag("js", new Date());
          gtag("config", "'.$id.'", { "debug_mode": false });
        </script>';
    }

    /**
     * Hook to track the Purchase event
     */
    public function hookDisplayOrderConfirmation($params)
    {
        error_log('GA4: hookDisplayOrderConfirmation fired');

        $id = Configuration::get('ANALYTICS4_MEASUREMENT_ID');
        if (!$id) {
            return;
        }

        // 1. Robust Order Detection
        $order = null;
        if (isset($params['objOrder'])) {
            $order = $params['objOrder'];
        } elseif (isset($params['order'])) {
            $order = $params['order'];
        } elseif (Tools::getValue('id_order')) {
            $order = new Order((int)Tools::getValue('id_order'));
        }

        if (!Validate::isLoadedObject($order)) {
            return;
        }

        // 2. Prepare Data
        $currency = new Currency((int)$order->id_currency);
        $products = $order->getProducts();
        $items = [];

        foreach ($products as $p) {
            $items[] = [
                'item_id'   => $p['product_id'],
                'item_name' => $p['product_name'],
                'price'     => (float)$p['unit_price_tax_incl'],
                'quantity'  => (int)$p['product_quantity'],
            ];
        }

        // 3. Send to Template
        $this->context->smarty->assign([
            'order_id' => $order->reference,
            'currency' => $currency->iso_code,
            'value'    => (float)$order->total_paid_tax_incl,
            'items'    => json_encode($items),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/orderConfirmation.tpl');
    }
}
