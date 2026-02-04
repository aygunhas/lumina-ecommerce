<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Config\Database;
use PDO;

/**
 * Admin: Özellikler (Beden/Renk) ve değerleri CRUD (B18)
 */
class AttributesController extends AdminBaseController
{
    public function index(): void
    {
        $pdo = Database::getConnection();
        $attributes = $pdo->query('
            SELECT a.id, a.type, a.name, a.slug, a.sort_order,
                   (SELECT COUNT(*) FROM attribute_values av WHERE av.attribute_id = a.id) AS values_count
            FROM attributes a
            ORDER BY a.sort_order ASC, a.name ASC
        ')->fetchAll(PDO::FETCH_ASSOC);
        $baseUrl = $this->baseUrl();
        $this->render('admin/attributes/index', [
            'pageTitle' => 'Özellikler (Beden / Renk)',
            'baseUrl' => $baseUrl,
            'attributes' => $attributes,
        ]);
    }

    public function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }
        $baseUrl = $this->baseUrl();
        $errors = $_SESSION['attribute_errors'] ?? [];
        $old = $_SESSION['attribute_old'] ?? [];
        unset($_SESSION['attribute_errors'], $_SESSION['attribute_old']);
        $this->render('admin/attributes/form', [
            'pageTitle' => 'Yeni özellik',
            'baseUrl' => $baseUrl,
            'attribute' => null,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function store(): void
    {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'other';
        if (!in_array($type, ['size', 'color', 'other'], true)) {
            $type = 'other';
        }
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $slug = $this->slugFromName($name);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Ad zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['attribute_errors'] = $errors;
            $_SESSION['attribute_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/attributes/create');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM attributes WHERE slug = ? LIMIT 1');
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $_SESSION['attribute_errors'] = ['name' => 'Bu ada sahip özellik zaten var.'];
            $_SESSION['attribute_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/attributes/create');
            exit;
        }

        $pdo->prepare('INSERT INTO attributes (type, name, slug, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([$type, $name, $slug, $sortOrder]);
        header('Location: ' . $baseUrl . '/admin/attributes?created=1');
        exit;
    }

    public function edit(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/attributes');
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM attributes WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $attribute = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$attribute) {
            header('Location: ' . $baseUrl . '/admin/attributes');
            exit;
        }

        $values = $pdo->prepare('SELECT * FROM attribute_values WHERE attribute_id = ? ORDER BY sort_order ASC, value ASC');
        $values->execute([$id]);
        $attribute['values'] = $values->fetchAll(PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_form']) && $_POST['_form'] === 'attribute') {
            $this->update($id);
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_form']) && $_POST['_form'] === 'add_value') {
            $this->addValue($id);
            return;
        }

        $errors = $_SESSION['attribute_errors'] ?? [];
        $old = $_SESSION['attribute_old'] ?? [];
        unset($_SESSION['attribute_errors'], $_SESSION['attribute_old']);
        if (empty($old) && $attribute) {
            $old = [
                'name' => $attribute['name'],
                'type' => $attribute['type'],
                'sort_order' => (int) $attribute['sort_order'],
            ];
        }

        $this->render('admin/attributes/form', [
            'pageTitle' => 'Özellik düzenle',
            'baseUrl' => $baseUrl,
            'attribute' => $attribute,
            'errors' => $errors,
            'old' => $old,
        ]);
    }

    private function update(int $id): void
    {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'other';
        if (!in_array($type, ['size', 'color', 'other'], true)) {
            $type = 'other';
        }
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $slug = $this->slugFromName($name);

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Ad zorunludur.';
        }

        $baseUrl = $this->baseUrl();
        if (!empty($errors)) {
            $_SESSION['attribute_errors'] = $errors;
            $_SESSION['attribute_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/attributes/edit?id=' . $id);
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM attributes WHERE slug = ? AND id != ? LIMIT 1');
        $stmt->execute([$slug, $id]);
        if ($stmt->fetch()) {
            $_SESSION['attribute_errors'] = ['name' => 'Bu ada sahip özellik zaten var.'];
            $_SESSION['attribute_old'] = $_POST;
            header('Location: ' . $baseUrl . '/admin/attributes/edit?id=' . $id);
            exit;
        }

        $pdo->prepare('UPDATE attributes SET type = ?, name = ?, slug = ?, sort_order = ? WHERE id = ?')
            ->execute([$type, $name, $slug, $sortOrder, $id]);
        header('Location: ' . $baseUrl . '/admin/attributes/edit?id=' . $id . '&updated=1');
        exit;
    }

    private function addValue(int $attributeId): void
    {
        $value = trim($_POST['value'] ?? '');
        $colorHex = trim($_POST['color_hex'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);
        $baseUrl = $this->baseUrl();

        if ($value === '') {
            $_SESSION['attribute_errors'] = ['value' => 'Değer zorunludur.'];
            header('Location: ' . $baseUrl . '/admin/attributes/edit?id=' . $attributeId);
            exit;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id FROM attribute_values WHERE attribute_id = ? AND value = ? LIMIT 1');
        $stmt->execute([$attributeId, $value]);
        if ($stmt->fetch()) {
            $_SESSION['attribute_errors'] = ['value' => 'Bu değer zaten kayıtlı.'];
            header('Location: ' . $baseUrl . '/admin/attributes/edit?id=' . $attributeId);
            exit;
        }

        $pdo->prepare('INSERT INTO attribute_values (attribute_id, value, color_hex, sort_order) VALUES (?, ?, ?, ?)')
            ->execute([$attributeId, $value, $colorHex ?: null, $sortOrder]);
        header('Location: ' . $baseUrl . '/admin/attributes/edit?id=' . $attributeId . '&value_added=1');
        exit;
    }

    public function deleteValue(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        $attributeId = isset($_REQUEST['attribute_id']) ? (int) $_REQUEST['attribute_id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/attributes');
            exit;
        }
        $pdo = Database::getConnection();
        $pdo->prepare('DELETE FROM attribute_values WHERE id = ?')->execute([$id]);
        if ($attributeId > 0) {
            header('Location: ' . $baseUrl . '/admin/attributes/edit?id=' . $attributeId . '&value_deleted=1');
        } else {
            header('Location: ' . $baseUrl . '/admin/attributes');
        }
        exit;
    }

    public function delete(): void
    {
        $id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
        $baseUrl = $this->baseUrl();
        if ($id < 1) {
            header('Location: ' . $baseUrl . '/admin/attributes');
            exit;
        }
        $pdo = Database::getConnection();
        $pdo->prepare('DELETE FROM attribute_values WHERE attribute_id = ?')->execute([$id]);
        $pdo->prepare('DELETE FROM attributes WHERE id = ?')->execute([$id]);
        header('Location: ' . $baseUrl . '/admin/attributes?deleted=1');
        exit;
    }

    private function slugFromName(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $tr = ['ş' => 's', 'ğ' => 'g', 'ü' => 'u', 'ö' => 'o', 'ç' => 'c', 'ı' => 'i', 'İ' => 'i'];
        $slug = strtr($slug, $tr);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-') ?: 'ozellik';
    }
}
