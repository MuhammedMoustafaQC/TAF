<?php $me = admin(); $isPw = ($_POST['action'] ?? '') === 'password'; $isNew = ($_POST['action'] ?? '') === 'create'; ?>
<?php if ($default_pw): ?>
<div class="alert alert-warning"><?= icon('shield', 18) ?> كلمة المرور الحالية هي الافتراضية (admin123) — غيّرها دلوقتي.</div>
<?php endif; ?>

<div class="cards-grid cards-grid-2">
    <section class="panel">
        <h2>حسابي وتغيير كلمة المرور</h2>
        <form method="post" class="form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="password">
            <label class="field"><span>الاسم</span><input type="text" name="name" value="<?= e($me['name']) ?>"></label>
            <label class="field"><span>كلمة المرور الحالية</span><input type="password" name="current_password" required autocomplete="current-password" class="<?= $isPw ? inv($errors, 'current_password') : '' ?>"><?= $isPw ? err($errors, 'current_password') : '' ?></label>
            <label class="field"><span>كلمة المرور الجديدة</span><input type="password" name="new_password" required minlength="8" autocomplete="new-password" class="<?= $isPw ? inv($errors, 'new_password') : '' ?>"><?= $isPw ? err($errors, 'new_password') : '' ?></label>
            <label class="field"><span>تأكيد كلمة المرور</span><input type="password" name="confirm_password" required autocomplete="new-password" class="<?= $isPw ? inv($errors, 'confirm_password') : '' ?>"><?= $isPw ? err($errors, 'confirm_password') : '' ?></label>
            <button class="btn btn-primary"><?= icon('check', 18) ?> حفظ</button>
        </form>
    </section>

    <div class="stack">
        <section class="panel panel-flush">
            <div class="panel-head pad"><h2>المديرين</h2></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>الاسم</th><th>اسم المستخدم</th><th>آخر دخول</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><b><?= e($u['name']) ?></b><?php if ($u['id'] == $me['id']): ?> <span class="badge badge-info">انت</span><?php endif; ?></td>
                            <td dir="ltr"><?= e($u['username']) ?></td>
                            <td><small><?= e($u['last_login'] ? ar_date($u['last_login']) : '—') ?></small></td>
                            <td class="actions">
                                <?php if ($u['id'] != $me['id']): ?>
                                <form action="<?= url('/admin/users/' . $u['id'] . '/delete') ?>" method="post" data-confirm="حذف المدير ده؟">
                                    <?= csrf_field() ?><button class="icon-btn icon-btn-danger" title="حذف"><?= icon('trash', 18) ?></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <section class="panel">
            <h2>إضافة مدير جديد</h2>
            <form method="post" class="form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create">
                <label class="field"><span>الاسم</span><input type="text" name="name" value="<?= $isNew ? e($_POST['name'] ?? '') : '' ?>" required class="<?= $isNew ? inv($errors, 'name') : '' ?>"><?= $isNew ? err($errors, 'name') : '' ?></label>
                <label class="field"><span>اسم المستخدم (إنجليزي)</span><input type="text" name="username" value="<?= $isNew ? e($_POST['username'] ?? '') : '' ?>" required dir="ltr" autocomplete="off" class="<?= $isNew ? inv($errors, 'username') : '' ?>"><?= $isNew ? err($errors, 'username') : '' ?></label>
                <label class="field"><span>كلمة المرور</span><input type="password" name="password" required minlength="8" autocomplete="new-password" class="<?= $isNew ? inv($errors, 'password') : '' ?>"><?= $isNew ? err($errors, 'password') : '' ?></label>
                <button class="btn btn-primary"><?= icon('plus', 18) ?> إضافة</button>
            </form>
        </section>
    </div>
</div>
