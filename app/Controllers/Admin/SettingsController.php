<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use App\Helpers\Settings;

/**
 * Admin: Site, kargo ve ödeme ayarları (B35, B36, B37)
 */
class SettingsController extends AdminBaseController
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $general = Settings::getGroup('general');
        $shipping = Settings::getGroup('shipping');
        $payment = Settings::getGroup('payment');
        $baseUrl = $this->baseUrl();
        $this->render('admin/settings/index', [
            'pageTitle' => 'Ayarlar',
            'baseUrl' => $baseUrl,
            'general' => $general,
            'shipping' => $shipping,
            'payment' => $payment,
        ]);
    }

    private function store(): void
    {
        $baseUrl = $this->baseUrl();

        // Genel
        Settings::set('general', 'site_name', trim($_POST['site_name'] ?? '') ?: null);
        Settings::set('general', 'contact_email', trim($_POST['contact_email'] ?? '') ?: null);
        Settings::set('general', 'contact_phone', trim($_POST['contact_phone'] ?? '') ?: null);
        Settings::set('general', 'contact_address', trim($_POST['contact_address'] ?? '') ?: null);

        // Kargo
        $shippingCost = trim($_POST['shipping_cost'] ?? '');
        Settings::set('shipping', 'shipping_cost', $shippingCost !== '' ? $shippingCost : null);
        $freeShippingMin = trim($_POST['free_shipping_min'] ?? '');
        Settings::set('shipping', 'free_shipping_min', $freeShippingMin !== '' ? $freeShippingMin : null);

        // Ödeme
        Settings::set('payment', 'cod_enabled', isset($_POST['cod_enabled']) ? '1' : '0');
        Settings::set('payment', 'bank_transfer_enabled', isset($_POST['bank_transfer_enabled']) ? '1' : '0');
        Settings::set('payment', 'bank_name', trim($_POST['bank_name'] ?? '') ?: null);
        Settings::set('payment', 'bank_iban', trim($_POST['bank_iban'] ?? '') ?: null);
        Settings::set('payment', 'bank_account_name', trim($_POST['bank_account_name'] ?? '') ?: null);

        header('Location: ' . $baseUrl . '/admin/settings?updated=1');
        exit;
    }
}
