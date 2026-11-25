<form method="post" class="settings-form">
    <?= csrf_field() ?>
    <div class="cards-grid cards-grid-2">
        <?php foreach ($groups as $group => $fields): ?>
        <section class="panel">
            <h2><?= e($group) ?></h2>
            <?php foreach ($fields as $key => $f): [$label, $type] = $f; $v = $values[$key] ?? ''; ?>
                <?php if ($type === 'select'): ?>
                    <label class="field"><span><?= e($label) ?></span>
                        <select name="<?= e($key) ?>">
                            <option value="">— بدون —</option>
                            <?php foreach ($f[2] as $ov => $ol): ?><option value="<?= e($ov) ?>" <?= (string)$v === (string)$ov ? 'selected' : '' ?>><?= e($ol) ?></option><?php endforeach; ?>
                        </select>
                    </label>
                <?php elseif ($type === 'checkbox'): ?>
                    <label class="check"><input type="checkbox" name="<?= e($key) ?>" value="1" <?= $v === '1' ? 'checked' : '' ?>> <?= e($label) ?></label>
                <?php elseif ($type === 'textarea'): ?>
                    <label class="field"><span><?= e($label) ?></span><textarea name="<?= e($key) ?>" rows="4"><?= e($v) ?></textarea></label>
                <?php else: ?>
                    <label class="field"><span><?= e($label) ?></span><input type="<?= $type === 'number' ? 'number' : 'text' ?>" <?= $type === 'number' ? 'step="any" min="0"' : '' ?> name="<?= e($key) ?>" value="<?= e($v) ?>" <?= in_array($type, ['url', 'number'], true) || in_array($key, ['phone', 'phone2', 'whatsapp', 'email', 'wallet_number'], true) ? 'dir="ltr"' : '' ?>></label>
                <?php endif; ?>
            <?php endforeach; ?>
        </section>
        <?php endforeach; ?>
    </div>
    <div class="save-bar">
        <button class="btn btn-primary btn-lg"><?= icon('check', 18) ?> حفظ الإعدادات</button>
    </div>
</form>
