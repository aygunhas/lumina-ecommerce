<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="color: #666;">Hesabım</a>
    <span style="color: #999;"> / </span>
    <span>Bilgilerim</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Bilgilerim</h1>

<?php if (!empty($_GET['updated'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.5rem 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Bilgileriniz güncellendi.</p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php
$old = $old ?? [];
$firstName = $old['first_name'] ?? $user['first_name'] ?? '';
$lastName = $old['last_name'] ?? $user['last_name'] ?? '';
$phone = $old['phone'] ?? $user['phone'] ?? '';
?>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/hesabim/bilgilerim" style="max-width: 450px;">
    <p style="margin-bottom: 0.5rem;"><label for="email">E-posta</label></p>
    <input type="email" id="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px;">
    <p style="margin: -0.75rem 0 1rem; font-size: 0.85rem; color: #666;">E-posta değiştirilemez.</p>

    <p style="margin-bottom: 0.5rem;"><label for="first_name">Ad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($firstName) ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="last_name">Soyad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($lastName) ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="phone">Telefon</label></p>
    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <h2 style="margin: 1.5rem 0 1rem; font-size: 1.1rem;">Şifre değiştir (isteğe bağlı)</h2>
    <p style="margin-bottom: 0.5rem;"><label for="current_password">Mevcut şifre</label></p>
    <input type="password" id="current_password" name="current_password" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="new_password">Yeni şifre</label></p>
    <input type="password" id="new_password" name="new_password" minlength="6" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-top: 1.5rem;">
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Güncelle</button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/hesabim" style="margin-left: 1rem; color: #666;">İptal</a>
    </p>
</form>
