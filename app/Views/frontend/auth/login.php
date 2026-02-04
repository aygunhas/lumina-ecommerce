<nav style="margin-bottom: 1rem; font-size: 0.9rem;">
    <a href="<?= htmlspecialchars($baseUrl) ?>/" style="color: #666;">Anasayfa</a>
    <span style="color: #999;"> / </span>
    <span>Giriş yap</span>
</nav>

<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">Giriş yap</h1>

<?php if (!empty($errors)): ?>
    <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
        <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars(is_string($err) ? $err : implode(' ', $err)) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php $old = $old ?? []; ?>
<form method="post" action="<?= htmlspecialchars($baseUrl) ?>/giris" style="max-width: 400px;">
    <?php if (!empty($redirect)): ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
    <?php endif; ?>
    <p style="margin-bottom: 0.5rem;"><label for="email">E-posta <span style="color: #c00;">*</span></label></p>
    <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-bottom: 0.5rem;"><label for="password">Şifre <span style="color: #c00;">*</span></label></p>
    <input type="password" id="password" name="password" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

    <p style="margin-top: 1.5rem;">
        <button type="submit" style="padding: 0.5rem 1.25rem; background: #2c3e50; color: #fff; border: none; border-radius: 6px; cursor: pointer;">Giriş yap</button>
        <a href="<?= htmlspecialchars($baseUrl) ?>/kayit" style="margin-left: 1rem; color: #666;">Üye değil misiniz? Kayıt olun</a>
    </p>
</form>
