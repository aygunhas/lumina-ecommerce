<h1 style="margin: 0 0 1.5rem; font-size: 1.5rem;">İletişim</h1>

<?php if (!empty($success) || !empty($_GET['sent'])): ?>
    <p style="margin-bottom: 1rem; padding: 0.75rem; background: #e8f5e9; color: #2e7d32; border-radius: 4px;">Mesajınız alındı. En kısa sürede size dönüş yapacağız.</p>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <div>
        <h2 style="margin: 0 0 0.75rem; font-size: 1.1rem; color: #333;">İletişim bilgileri</h2>
        <p style="margin: 0 0 0.5rem;"><strong>E-posta:</strong> info@luminaboutique.com</p>
        <p style="margin: 0 0 0.5rem;"><strong>Telefon:</strong> +90 (212) 000 00 00</p>
        <p style="margin: 0;"><strong>Adres:</strong> Örnek Mah. Örnek Sok. No: 1, İstanbul</p>
    </div>
    <div>
        <h2 style="margin: 0 0 0.75rem; font-size: 1.1rem; color: #333;">Bize yazın</h2>
        <?php if (!empty($errors)): ?>
            <ul style="margin: 0 0 1rem; padding-left: 1.25rem; color: #c00;">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <form method="post" action="<?= $baseUrl ?? '' ?>/iletisim" style="max-width: 400px;">
            <p style="margin-bottom: 0.5rem;"><label for="name">Ad soyad <span style="color: #c00;">*</span></label></p>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

            <p style="margin-bottom: 0.5rem;"><label for="email">E-posta <span style="color: #c00;">*</span></label></p>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

            <p style="margin-bottom: 0.5rem;"><label for="phone">Telefon</label></p>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($old['phone'] ?? '') ?>" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

            <p style="margin-bottom: 0.5rem;"><label for="subject">Konu</label></p>
            <input type="text" id="subject" name="subject" value="<?= htmlspecialchars($old['subject'] ?? '') ?>" placeholder="Örn: Sipariş hakkında" style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;">

            <p style="margin-bottom: 0.5rem;"><label for="message">Mesaj <span style="color: #c00;">*</span></label></p>
            <textarea id="message" name="message" rows="4" required style="width: 100%; padding: 0.5rem; margin-bottom: 1rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($old['message'] ?? '') ?></textarea>

            <button type="submit" style="padding: 0.5rem 1.5rem; background: #333; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Gönder</button>
        </form>
    </div>
</div>
