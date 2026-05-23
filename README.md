# ClinicDesk — نظام إدارة عيادة طبية

---

## خطوات تشغيل المشروع

### الخطوة 1 — ضع المشروع في XAMPP

انسخ مجلد `clinicdesk` داخل:
```
C:\xampp\htdocs\
```
النتيجة النهائية:
```
C:\xampp\htdocs\clinicdesk\
```

---

### الخطوة 2 — نزّل AdminLTE 3

> ⚠️ بدون هذه الخطوة الموقع يظهر بدون أي تنسيق

1. اذهب إلى: `https://github.com/ColorlibHQ/AdminLTE/releases/tag/v3.2.0`
2. بعد التحميل افتح ملف ZIP وانسخ محتوياته :

```
clinicdesk/public/assets/adminlte/
```

---

### الخطوة 3 — أنشئ قاعدة البيانات

1. افتح `http://localhost/phpmyadmin`
2. اضغط **Import** من الشريط العلوي
3. اختر ملف `clinicdesk_db.sql` الموجود في المشروع
4. اضغط **Import**

> ✅ سيُنشئ تلقائياً: قاعدة البيانات + الجداول الخمسة + بيانات تجريبية

---

### الخطوة 4 — اضبط بيانات قاعدة البيانات

افتح `config/database.php` وعدّل:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'clinicdesk_db');
define('DB_USER', 'root');  // اسم مستخدم MySQL عندك
define('DB_PASS', '');      // كلمة المرور — فارغة في XAMPP الافتراضي
```

---

### الخطوة 5 — شغّل المشروع

افتح المتصفح واذهب إلى:
```
http://localhost/clinicdesk
```

---



## بيانات الدخول التجريبية

| الدور | البريد الإلكتروني | كلمة المرور |
|-------|--------------------|--------------|
| مدير (Admin) | admin@clinic.local | Admin@1234 |
| طبيب (Doctor) | sarah@clinic.local | Admin@1234 |
| مريض (Patient) | patient@clinic.local | Admin@1234 |

---

## هيكل المشروع

```
clinicdesk/
├── index.php              ← نقطة الدخول الوحيدة — كل الروابط تمر منه
├── .htaccess              ← يوجّه كل الطلبات لـ index.php
├── clinicdesk_db.sql      ← ملف قاعدة البيانات — شغّله مرة واحدة فقط
│
├── config/
│   ├── config.php         ← إعدادات عامة (BASE_URL، حجم الملفات...)
│   └── database.php       ← بيانات الاتصال بقاعدة البيانات
│
├── core/                  ← الكلاسات الأساسية
│   ├── Database.php       ← اتصال واحد بقاعدة البيانات (Singleton)
│   ├── Auth.php           ← تسجيل الدخول والصلاحيات
│   ├── CSRF.php           ← حماية النماذج من الهجمات
│   ├── Paginator.php      ← تقسيم النتائج لصفحات
│   └── helpers.php        ← دوال مساعدة مشتركة
│
├── models/                ← كلاس لكل جدول — كل SQL هنا فقط
├── controllers/           ← منطق العمل — يربط النماذج بالواجهات
├── views/                 ← صفحات HTML — لا يحتوي أي SQL
│
└── public/uploads/        ← ملفات المستخدمين
    ├── avatars/           ← صور المستخدمين
    ├── doctor_photos/     ← صور الأطباء
    └── prescriptions/     ← ملفات PDF للوصفات (محمية من الوصول المباشر)
```
