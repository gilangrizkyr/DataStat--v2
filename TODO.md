# Perbaikan Error 500 API Dataset Fields

## Masalah
Error 500 saat AJAX call ke `/api/dataset/fields/16` karena ketidakcocokan nama kolom.

## Penyebab
- Database menggunakan kolom `schema_config`
- Controller dan View menggunakan key yang salah

## Solusi

### 1. Fix DatasetApiController.php - SELESAI ✅
- [x] Ubah `$dataset['schema_definition']` menjadi `$dataset['schema_config']`
- [x] Update akses field name dari `name` menjadi `field_name`
- [x] Update akses nullable dari `nullable` menjadi `required`

### 2. Fix builder.php - SELESAI ✅
- [x] Ubah `$dataset['fields']` menjadi `$schema` (loop langsung pada array)
- [x] Update akses field name dari `field['name']` menjadi `$field['field_name'] ?? $field['name']`
- [x] Tampilkan `display_label` sebagai label

### Perubahan Kode

**DatasetApiController.php:**
```php
// Sebelum:
$schema = json_decode($dataset['schema_definition'], true);
foreach ($schema['fields'] as $field) {
    $name = $field['name'];
}

// Sesudah:
$schema = json_decode($dataset['schema_config'], true);
foreach ($schema as $field) {
    $name = $field['field_name'] ?? $field['name'];
}
```

**builder.php:**
```php
// Sebelum:
<?php if (isset($dataset['fields'])): ?>
    <?php foreach ($dataset['fields'] as $field): ?>
        <option value="<?= esc($field['name']) ?>">

// Sesudah:
<?php if (!empty($schema)): ?>
    <?php foreach ($schema as $field): ?>
        <?php $fieldName = $field['field_name'] ?? $field['name']; ?>
        <option value="<?= esc($fieldName) ?>">
            <?= esc($field['display_label'] ?? $fieldName) ?>
```

