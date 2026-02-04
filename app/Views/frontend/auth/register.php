<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <span>Kayıt ol</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Kayıt ol</h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php $old = $old ?? []; ?>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/kayit" style="max-width: 400px;">
    <p style="margin-bottom: 0.5rem;"><label for="email">E-posta <span style="color: #c00;">*</span></label></p>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="password">Şifre <span style="color: #c00;">*</span></label></p>
    <input type="password" id="password" name="password" required minlength="6" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="first_name">Ad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($old['first_name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="last_name">Soyad <span style="color: #c00;">*</span></label></p>
    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($old['last_name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="phone">Telefon</label></p>
    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-top: 1.5rem;">
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Kayıt ol</button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/giris" style="margin-left: 1rem; color: #666;">Zaten üye misiniz? Giriş yapın</a>
    </p>
</form>
