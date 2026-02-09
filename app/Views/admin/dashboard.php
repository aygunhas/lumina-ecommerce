<?php
ob_start();

$baseUrl = $baseUrl ?? '';
$stats = $stats ?? [];
$chartData = $chartData ?? [];
$topProducts = $topProducts ?? [];
$recentOrders = $recentOrders ?? [];
$lowStockVariants = $lowStockVariants ?? [];

// KPI verileri
$salesToday = (float) ($stats['sales_today'] ?? 0);
$salesYesterday = (float) ($stats['sales_yesterday'] ?? 0);
$salesChange = $salesYesterday > 0 ? (($salesToday - $salesYesterday) / $salesYesterday) * 100 : 0;
$pendingShipments = (int) ($stats['pending_shipments'] ?? 0);
$returnRate = (float) ($stats['return_rate'] ?? 0);
$avgOrderValue = (float) ($stats['avg_order_value'] ?? 0);
?>
<div class="space-y-8">
    <!-- Başlık -->
    <div>
        <h1 class="text-3xl font-light tracking-tight text-stone-800">Dashboard</h1>
        <p class="mt-1 text-sm text-stone-500">Genel bakış ve performans metrikleri</p>
    </div>

    <!-- KPI Kartları: 4'lü Grid -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Günlük Ciro -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm">
            <div class="p-6">
                <p class="text-xs font-medium uppercase tracking-wider text-stone-500">Günlük Ciro</p>
                <p class="mt-2 text-3xl font-light text-stone-800"><?= number_format($salesToday, 2, ',', '.') ?> ₺</p>
                <?php if ($salesChange != 0): ?>
                <div class="mt-2 flex items-center gap-1.5">
                    <span class="text-xs font-medium <?= $salesChange > 0 ? 'text-emerald-800' : 'text-rose-800' ?>">
                        <?= $salesChange > 0 ? '↑' : '↓' ?> <?= number_format(abs($salesChange), 1) ?>%
                    </span>
                    <span class="text-xs text-stone-500">dün ile</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bekleyen Sevkiyatlar -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm">
            <div class="p-6">
                <p class="text-xs font-medium uppercase tracking-wider text-stone-500">Bekleyen Sevkiyatlar</p>
                <p class="mt-2 text-3xl font-light text-stone-800"><?= $pendingShipments ?></p>
                <p class="mt-2 text-xs text-stone-500">Kargolanmayı bekleyen</p>
            </div>
        </div>

        <!-- İade Oranı -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm">
            <div class="p-6">
                <p class="text-xs font-medium uppercase tracking-wider text-stone-500">İade Oranı</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-light text-stone-800"><?= number_format($returnRate, 1) ?>%</p>
                    <?php if ($returnRate > 10): ?>
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    <?php elseif ($returnRate > 5): ?>
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    <?php endif; ?>
                </div>
                <p class="mt-2 text-xs text-stone-500">Toplam siparişlerin</p>
            </div>
        </div>

        <!-- Ortalama Sepet Tutarı -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm">
            <div class="p-6">
                <p class="text-xs font-medium uppercase tracking-wider text-stone-500">Ortalama Sepet Tutarı</p>
                <p class="mt-2 text-3xl font-light text-stone-800"><?= number_format($avgOrderValue, 2, ',', '.') ?> ₺</p>
                <p class="mt-2 text-xs text-stone-500">Müşteri başına</p>
            </div>
        </div>
    </div>

    <!-- Görsel Analiz Bölümü: 2'li Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Sol: Gelir Akışı Grafiği (Geniş) -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm lg:col-span-2">
            <div class="border-b border-stone-200 px-6 py-4">
                <h2 class="text-sm font-semibold text-stone-800">Gelir Akışı</h2>
                <p class="mt-0.5 text-xs text-stone-500">Son 30 gün</p>
            </div>
            <div class="p-6">
                <canvas id="revenueChart" height="120"></canvas>
            </div>
        </div>

        <!-- Sağ: En Çok Satanlar (Dar) -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm">
            <div class="border-b border-stone-200 px-6 py-4">
                <h2 class="text-sm font-semibold text-stone-800">En Çok Satanlar</h2>
            </div>
            <div class="divide-y divide-stone-200">
                <?php if (!empty($topProducts)): ?>
                    <?php foreach ($topProducts as $index => $product): ?>
                    <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) ($product['id'] ?? 0) ?>" 
                       class="flex items-center gap-4 p-4 hover:bg-stone-50 transition-colors">
                        <div class="relative h-16 w-16 shrink-0 overflow-hidden rounded-md bg-stone-100">
                            <?php if (!empty($product['image_path'])): ?>
                            <img src="<?= htmlspecialchars($baseUrl . $product['image_path']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                 class="h-full w-full object-cover"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'flex h-full w-full items-center justify-center text-xs text-stone-400\'>Görsel Yok</div>'">
                            <?php else: ?>
                            <div class="flex h-full w-full items-center justify-center text-xs text-stone-400">Görsel Yok</div>
                            <?php endif; ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-stone-800"><?= htmlspecialchars($product['name']) ?></p>
                            <p class="mt-0.5 text-xs text-stone-500"><?= (int) ($product['total_sold'] ?? 0) ?> adet satıldı</p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="p-6 text-center text-sm text-stone-500">Henüz satış verisi yok</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Operasyonel Alt Bölüm: Grid (Son Siparişler geniş, Stok dar) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <!-- Sol: Son Siparişler Tablosu (Geniş - 3/5) -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm lg:col-span-3">
            <div class="border-b border-stone-200 px-6 py-4">
                <h2 class="text-sm font-semibold text-stone-800">Son Siparişler</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Sipariş</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Müşteri</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-stone-500">Tutar</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-500">Durum</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-stone-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 bg-[#FAFAF9]">
                        <?php if (!empty($recentOrders)): ?>
                            <?php
                            $statusConfig = [
                                'pending' => ['label' => 'Beklemede', 'class' => 'bg-amber-100 text-amber-800 border border-amber-200'],
                                'confirmed' => ['label' => 'Onaylandı', 'class' => 'bg-blue-50 text-blue-800 border border-blue-100'],
                                'processing' => ['label' => 'Hazırlanıyor', 'class' => 'bg-indigo-50 text-indigo-800 border border-indigo-100'],
                                'shipped' => ['label' => 'Kargoda', 'class' => 'bg-purple-50 text-purple-800 border border-purple-100'],
                                'delivered' => ['label' => 'Teslim Edildi', 'class' => 'bg-emerald-100 text-emerald-800 border border-emerald-200'],
                                'cancelled' => ['label' => 'İptal', 'class' => 'bg-rose-100 text-rose-800 border border-rose-200'],
                                'refunded' => ['label' => 'İade', 'class' => 'bg-stone-100 text-stone-700 border border-stone-200'],
                            ];
                            foreach (array_slice($recentOrders, 0, 5) as $o):
                                $customer = trim(($o['guest_first_name'] ?? '') . ' ' . ($o['guest_last_name'] ?? ''));
                                if ($customer === '') {
                                    $customer = $o['guest_email'] ?? '—';
                                }
                                $status = $o['status'] ?? 'pending';
                                $statusInfo = $statusConfig[$status] ?? ['label' => $status, 'class' => 'bg-stone-100 text-stone-700 border border-stone-200'];
                            ?>
                                <tr class="hover:bg-stone-50/50">
                                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-stone-800"><?= htmlspecialchars($o['order_number'] ?? '—') ?></td>
                                    <td class="px-4 py-3 text-sm text-stone-600"><?= htmlspecialchars($customer) ?></td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-stone-800"><?= number_format((float) ($o['total'] ?? 0), 2, ',', '.') ?> ₺</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium <?= $statusInfo['class'] ?>">
                                            <?= htmlspecialchars($statusInfo['label']) ?>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm">
                                        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders/show?id=<?= (int) ($o['id'] ?? 0) ?>" 
                                           class="font-medium text-stone-600 hover:text-stone-800">Görüntüle</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-stone-500">Henüz sipariş yok</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="border-t border-stone-200 px-6 py-3">
                <a href="<?= htmlspecialchars($baseUrl) ?>/admin/orders" class="text-xs font-medium text-stone-600 hover:text-stone-800">Tüm siparişler →</a>
            </div>
        </div>

        <!-- Sağ: Stok Alarmları (Dar - 2/5) -->
        <div class="overflow-hidden rounded-xl border border-stone-200 bg-[#FAFAF9] shadow-sm lg:col-span-2">
            <div class="border-b border-stone-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-semibold text-stone-800">Stok Alarmları</h2>
                        <p class="mt-0.5 text-xs text-stone-500">Tükenme riski olan ürünler</p>
                    </div>
                    <?php if (!empty($lowStockVariants)): ?>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800 border border-rose-200">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                        <?= count($lowStockVariants) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="max-h-[400px] overflow-y-auto stock-alerts-scroll">
                <?php if (!empty($lowStockVariants)): ?>
                    <div class="divide-y divide-stone-200">
                        <?php foreach ($lowStockVariants as $variant): 
                            $stock = (int) ($variant['stock'] ?? 0);
                            $threshold = !empty($variant['low_stock_threshold']) ? (int) $variant['low_stock_threshold'] : 5;
                            $stockPercent = $threshold > 0 ? min(100, ($stock / $threshold) * 100) : 0;
                            
                            // Aciliyet seviyesi belirleme
                            if ($stock === 0) {
                                $urgencyLevel = 'critical';
                                $urgencyColor = 'bg-rose-500';
                                $urgencyBgColor = 'bg-rose-100';
                                $urgencyIcon = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z';
                            } elseif ($stockPercent <= 20) {
                                $urgencyLevel = 'high';
                                $urgencyColor = 'bg-amber-500';
                                $urgencyBgColor = 'bg-amber-100';
                                $urgencyIcon = 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z';
                            } else {
                                $urgencyLevel = 'medium';
                                $urgencyColor = 'bg-amber-400';
                                $urgencyBgColor = 'bg-amber-50';
                                $urgencyIcon = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
                            }
                        ?>
                        <a href="<?= htmlspecialchars($baseUrl) ?>/admin/products/edit?id=<?= (int) ($variant['product_id'] ?? 0) ?>" 
                           class="group block p-5 transition-all hover:bg-stone-50">
                            <div class="flex items-start gap-4">
                                <!-- İkon -->
                                <div class="mt-0.5 shrink-0">
                                    <div class="rounded-full <?= $urgencyBgColor ?> p-2">
                                        <svg class="h-4 w-4 text-stone-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $urgencyIcon ?>"/>
                                        </svg>
                                    </div>
                                </div>
                                
                                <!-- İçerik -->
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-stone-800 group-hover:text-stone-700"><?= htmlspecialchars($variant['product_name'] ?? '—') ?></p>
                                    <?php if (!empty($variant['attributes_summary'])): ?>
                                    <p class="mt-1 text-xs font-light text-stone-500"><?= htmlspecialchars($variant['attributes_summary']) ?></p>
                                    <?php endif; ?>
                                    
                                    <!-- Progress Bar -->
                                    <div class="mt-3">
                                        <div class="mb-1.5 flex items-center justify-between">
                                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-stone-700">
                                                <?php if ($stock === 0): ?>
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                                <?php elseif ($stockPercent <= 20): ?>
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                                <?php else: ?>
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                                                <?php endif; ?>
                                                <?= $stock ?> adet
                                            </span>
                                            <span class="text-xs text-stone-400">
                                                Eşik: <?= $threshold ?>
                                            </span>
                                        </div>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-stone-100">
                                            <div class="h-full <?= $urgencyColor ?> transition-all duration-300" 
                                                 style="width: <?= $stockPercent ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Ok İkonu -->
                                <div class="mt-0.5 shrink-0 opacity-0 transition-opacity group-hover:opacity-100">
                                    <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-12 px-6">
                        <svg class="h-12 w-12 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-3 text-sm font-medium text-stone-500">Tüm stoklar yeterli</p>
                        <p class="mt-1 text-xs text-stone-400">Stok uyarısı bulunmuyor</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Custom Styles -->
<style>
.stock-alerts-scroll::-webkit-scrollbar {
    width: 6px;
}
.stock-alerts-scroll::-webkit-scrollbar-track {
    background: #f5f5f4;
    border-radius: 3px;
}
.stock-alerts-scroll::-webkit-scrollbar-thumb {
    background: #d6d3d1;
    border-radius: 3px;
}
.stock-alerts-scroll::-webkit-scrollbar-thumb:hover {
    background: #a8a29e;
}
</style>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    const chartData = <?= json_encode($chartData, JSON_UNESCAPED_UNICODE) ?>;
    const labels = chartData.map(item => {
        const date = new Date(item.day);
        return date.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit' });
    });
    const revenues = chartData.map(item => parseFloat(item.total || 0));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Gelir (₺)',
                data: revenues,
                borderColor: '#44403C',
                backgroundColor: 'rgba(68, 64, 60, 0.05)',
                borderWidth: 1.5,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: '#44403C',
                pointBorderColor: '#FAFAF9',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(41, 37, 36, 0.9)',
                    padding: 12,
                    titleFont: { size: 12, weight: 'normal' },
                    bodyFont: { size: 12 },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return number_format(context.parsed.y, 2, ',', '.') + ' ₺';
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11 },
                        color: '#78716c'
                    }
                },
                y: {
                    display: true,
                    grid: {
                        color: 'rgba(68, 64, 60, 0.08)'
                    },
                    ticks: {
                        font: { size: 11 },
                        color: '#78716c',
                        callback: function(value) {
                            return value.toLocaleString('tr-TR') + ' ₺';
                        }
                    }
                }
            }
        }
    });
});

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    const n = !isFinite(+number) ? 0 : +number;
    const prec = !isFinite(+decimals) ? 0 : Math.abs(decimals);
    const sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep;
    const dec = (typeof dec_point === 'undefined') ? '.' : dec_point;
    const s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function toFixedFix(n, prec) {
    const k = Math.pow(10, prec);
    return '' + Math.round(n * k) / k;
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layouts/main.php';
