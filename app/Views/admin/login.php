<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yönetim paneli giriş - Lumina Boutique</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f0f0f0; }
        .box { background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); width: 100%; max-width: 360px; }
        h1 { margin: 0 0 1.5rem; font-size: 1.25rem; color: #333; }
        label { display: block; margin-bottom: 0.25rem; font-size: 0.9rem; color: #555; }
        input[type=email], input[type=password] { width: 100%; padding: 0.6rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem; }
        button { width: 100%; padding: 0.75rem; background: #333; color: #fff; border: none; border-radius: 4px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #555; }
        .error { background: #fee; color: #c00; padding: 0.5rem; margin-bottom: 1rem; border-radius: 4px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Lumina Boutique – Yönetim girişi</h1>
        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="<?= htmlspecialchars($baseUrl) ?>/admin/login">
            <label for="email">E-posta</label>
            <input type="email" id="email" name="email" required autocomplete="email">
            <label for="password">Şifre</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
            <button type="submit">Giriş yap</button>
        </form>
    </div>
</body>
</html>
