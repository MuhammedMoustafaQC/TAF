<?php
/**
 * إعدادات الموقع الأساسية
 * معظم بيانات الموقع (التليفون، العنوان، الشحن...) بتتعدل من لوحة التحكم ← الإعدادات.
 */
return [
    // مسار قاعدة البيانات (SQLite) — بيتعمل تلقائياً أول مرة
    'db_path' => __DIR__ . '/../storage/database.sqlite',

    // لو الموقع في فولدر فرعي اكتب المسار هنا (مثال: '/shop')، أو سيبه null للاكتشاف التلقائي
    'base_path' => null,

    // بيانات أول مدير للوحة التحكم (بيتعمل مرة واحدة بس عند إنشاء قاعدة البيانات)
    'default_admin' => [
        'name'     => 'مدير الموقع',
        'username' => 'admin',
        'password' => 'admin123',
    ],

    // رفع الصور
    'upload_dir'      => __DIR__ . '/../public/uploads',
    'upload_max_size' => 4 * 1024 * 1024, // 4MB

    'timezone' => 'Africa/Cairo',
    'debug'    => false,
];
