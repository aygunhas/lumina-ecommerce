<?php
$dataFile = dirname(__DIR__) . '/data/announcements.json';
$config = ['active' => false, 'interval' => 4000, 'messages' => []];
if (is_file($dataFile)) {
    $json = file_get_contents($dataFile);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $config = array_merge($config, $decoded);
    }
}
if (empty($config['active']) || empty($config['messages'])) {
    return;
}
$messages = array_values($config['messages']);
$interval = (int) ($config['interval'] ?? 4000);
$interval = max(1000, min(30000, $interval));
$messagesJson = htmlspecialchars(json_encode($messages), ENT_QUOTES, 'UTF-8');
?>
<div class="bg-black text-white text-[10px] font-bold tracking-[0.2em] uppercase relative z-50 h-10 overflow-hidden flex items-center justify-center"
     data-messages="<?= $messagesJson ?>"
     data-interval="<?= (int) $interval ?>"
     x-data="{ index: -1, messages: [] }"
     x-init="
         messages = JSON.parse($el.dataset.messages || '[]');
         setInterval(() => { if (messages.length) index = (index + 1) % messages.length }, parseInt($el.dataset.interval) || 4000);
         $nextTick(() => { index = 0 });
     ">
    <div class="max-w-[1400px] mx-auto px-6 w-full h-full relative overflow-hidden flex items-center justify-center">
        <template x-for="(msg, i) in messages" :key="i">
            <div x-show="i === index"
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="translate-y-full opacity-0"
                 x-transition:enter-end="translate-y-0 opacity-100"
                 x-transition:leave="transition ease-in duration-1000"
                 x-transition:leave-start="translate-y-0 opacity-100"
                 x-transition:leave-end="-translate-y-full opacity-0"
                 class="absolute inset-0 w-full h-full flex items-center justify-center text-center"
                 x-text="msg"></div>
        </template>
    </div>
</div>
