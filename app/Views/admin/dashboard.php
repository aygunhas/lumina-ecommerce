<?php
ob_start();

$baseUrl = $baseUrl ?? '';
$stats = $stats ?? [];
$salesToday = $stats['sales_today'] ?? 0;
$ordersPending = $stats['orders_pending'] ?? 0;
$productsTotal = $stats['products_total'] ?? 0;
$usersTotal = $stats['users_total'] ?? 0;

// Son siparişler: gerçek veri varsa kullan, yoksa placeholder
$recentOrders = $recentOrders ?? [];
?>
<h1 class="mb-6 text-2xl font-bold text-gray-900">Genel Bakış</h1>

<!-- Özet kartları: 4'lü grid -->
<div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Toplam Satış (Bugün)</p>
        <p class="mt-1 text-2xl font-bold text-gray-900"><?= number_format((float) $salesToday, 2, ',', '.') ?> ₺</p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Bekleyen Siparişler</p>
        <p class="mt-1 text-2xl font-bold text-gray-900"><?= (int) $ordersPending ?></p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Toplam Ürün</p>
        <p class="mt-1 text-2xl font-bold text-gray-900"><?= (int) $productsTotal ?></p>
    </div>
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-gray-500">Toplam Müşteri</p>
        <p class="mt-1 text-2xl font-bold text-gray-900"><?= (int) $usersTotal ?></p>
    </div>
</div>

<!-- Son Siparişler tablosu (placeholder/statik veya gerçek veri) -->
<section>
    <h2 class="mb-4 text-lg font-semibold text-gray-900">Son Siparişler</h2>
    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sipariş no</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Müşteri</th>
                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Toplam</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Durum</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tarih</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">İşlem</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    <?php if (!empty($recentOrders)): ?>
                        <?php
                        $statusLabels = ['pending' => 'Beklemede', 'confirmed' => 'Onaylandı', 'processing' => 'Hazırlanıyor', 'shipped' => 'Kargoda', 'delivered' => 'Teslim edildi', 'cancelled' => 'İptal', 'refunded' => 'İade'];
                        foreach ($recentOrders as $o):
                            $customer = trim(($o['guest_first_name'] ?? '') . ' ' . ($o['guest_last_name'] ?? ''));
                            if ($customer === '') {
                                $customer = $o['guest_email'] ?? '—';
                            }
                        ?>
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900"><?= htmlspecialchars($o['order_number']) ?></td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($customer) ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900"><?= number_format((float) ($o['total'] ?? 0), 2, ',', '.') ?> ₺</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600"><?= $statusLabels[$o['status'] ?? ''] ?? ($o['status'] ?? '—') ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500"><?= isset($o['created_at']) ? date('d.m.Y H:i', strtotime($o['created_at'])) : '—' ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders/show?id=<?= (int) ($o['id'] ?? 0) ?>" class="text-gray-600 hover:text-gray-900">Detay</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Placeholder / statik satırlar -->
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">ORD-001</td>
                            <td class="px-4 py-3 text-sm text-gray-500">Örnek Müşteri</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-500">299,00 ₺</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">Beklemede</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">—</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-400">—</td>
                        </tr>
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">—</td>
                            <td class="px-4 py-3 text-sm text-gray-400">Henüz sipariş yok</td>
                            <td colspan="4" class="px-4 py-3 text-sm text-gray-400">Siparişler burada listelenecek.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <p class="mt-3"><a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders" class="text-sm font-medium text-gray-600 hover:text-gray-900">Tüm siparişler →</a></p>
</section>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main.php';
