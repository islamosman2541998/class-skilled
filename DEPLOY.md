# نشر موقع كلاس سكيلد على استضافة مشتركة / Deploying Class Skilled to shared hosting

> العربية أولًا، ثم English بالأسفل.
> The site needs **no cron, no queue worker, no Redis, no Node and no FFmpeg on the server**.

---

## العربية

### 1) المتطلبات على الاستضافة
- PHP **8.2 أو أحدث** (يفضَّل 8.3).
- الإضافات: `gd`, `fileinfo`, `mbstring`, `zip`, `pdo_mysql`, `openssl`, `curl`, `xml`, `json`، ويفضَّل `intl` و`exif`.
- قاعدة بيانات MySQL 8 / MariaDB 10.6 بترميز `utf8mb4_unicode_ci`.
- صندوق بريد على الاستضافة لإرسال الرسائل (SMTP).
- شهادة SSL مفعّلة للدومين.

### 2) التجهيز على جهازك (قبل الرفع)
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```
ثم جهّز حزمة الرفع **بدون**: `_design/` و`node_modules/` و`tests/` و`.git/` و`.env` المحلي و`storage/logs/*.log`.
تأكد أن مجلد `public/build` موجود داخل الحزمة.

### 3) ترتيب الملفات على الاستضافة
**الطريقة المفضلة:** اجعل جذر الدومين (Document Root) يشير إلى مجلد `public` داخل المشروع.

**إذا لم تسمح الاستضافة بذلك:**
1. ارفع المشروع كاملًا إلى مجلد خارج `public_html`، مثلًا `~/class-skilled`.
2. انقل **محتويات** `public/` (بما فيها `.htaccess` و`build/` و`images/`) إلى `public_html/`.
3. عدّل `public_html/index.php` ليشير إلى مسار المشروع:
```php
require __DIR__.'/../class-skilled/vendor/autoload.php';
$app = require_once __DIR__.'/../class-skilled/bootstrap/app.php';
```
وعدّل أيضًا سطر `maintenance.php` إلى `__DIR__.'/../class-skilled/storage/framework/maintenance.php'`.
4. أضِف في `class-skilled/bootstrap/app.php` بعد إنشاء التطبيق (أو في `AppServiceProvider::register`):
```php
$app->usePublicPath(dirname(__DIR__, 2).'/public_html');
```
حتى يعرف Laravel مكان مجلد public الجديد (مهم لـ Vite وخريطة الموقع والأيقونات).

### 4) ملف ‎.env للإنتاج
انسخ `.env.example` إلى `.env` وعدّل القيم:
```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com
APP_KEY=            # php artisan key:generate --show (مرة واحدة)
DB_HOST=localhost
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=smtp
MAIL_HOST=mail.example.com
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=no-reply@example.com
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=no-reply@example.com
ADMIN_PATH=cs-panel        # غيّره لمسار يصعب تخمينه
ADMIN_NAME="..."           # للتشغيل الأول فقط
ADMIN_EMAIL=...
ADMIN_PASSWORD=...
```
بعد إنشاء المدير الأول احذف قيم `ADMIN_*` من الملف.

### 5) أوامر أول نشر (من Terminal الاستضافة أو SSH)
```bash
php artisan migrate --force
php artisan db:seed --class=EssentialSeeder --force   # أول نشر فقط
php artisan storage:link
php artisan optimize
php artisan route:trans:cache
php artisan site:check
```
- **إذا لم تُتح الاستضافة الروابط الرمزية (symlink):** لا تشغّل `storage:link`. الموقع يكتشف غياب `public/storage` ويخدم الملفات تلقائيًا عبر المسار الاحتياطي `/storage/...` (صور وفيديو وPDF فقط، ولا يخدم أي سكربت). بعدها شغّل `php artisan optimize` من جديد.
- **إذا لم يتوفر Terminal:** نفّذ الأوامر من cPanel ← "Terminal" أو "Cron Jobs (Run once)"، أو افتح لوحة التحكم ← النظام ← فحص النظام لرؤية التقرير، وزر "تخزين الإعدادات والمسارات".

### 6) الصلاحيات
- `storage/` و`bootstrap/cache/` قابلة للكتابة (عادة 755 للمجلدات و644 للملفات).
- ملف `storage/app/public/.htaccess` يمنع تنفيذ أي سكربت داخل الملفات المرفوعة — لا تحذفه.

### 7) بعد النشر
- فعّل إعادة التوجيه إلى HTTPS: أزل التعليق عن سطرَي HTTPS في `public/.htaccess`.
- وحّد الدومين (www أو بدونه) من إعدادات الاستضافة أو بقاعدة إعادة توجيه.
- أضف الموقع إلى Google Search Console وأرسل `https://example.com/sitemap.xml`.
- ضع معرّفات GA4 / GTM / Meta من لوحة التحكم ← الإعدادات ← التتبع (تعمل في الإنتاج فقط).
- جرّب نموذج طلب عرض السعر والتواصل والتوظيف وتأكد من وصول البريد والرد الآلي.
- أنشئ نسخة احتياطية من لوحة التحكم ← النظام ← النسخ الاحتياطية، وحمّلها.
- راجع لوحة التحكم ← النظام ← فحص النظام (يجب ألا يظهر "فشل").
- شغّل Lighthouse (جوال) على الرئيسية وصفحة خدمة ومشروع ومقال وطلب عرض سعر وسجّل النتائج.

### 8) التحديثات اللاحقة
1. ابنِ محليًا (`composer install --no-dev -o` و`npm run build`) وارفع الملفات المتغيرة (مع `public/build`).
2. على الاستضافة:
```bash
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan route:trans:cache
```
3. الذاكرة المؤقتة للصفحات تُمسح تلقائيًا عند أي تعديل من لوحة التحكم.

---

## English

### 1) Host requirements
- PHP **8.2+** (8.3 recommended).
- Extensions: `gd`, `fileinfo`, `mbstring`, `zip`, `pdo_mysql`, `openssl`, `curl`, `xml`, `json`; recommended `intl`, `exif`.
- MySQL 8 / MariaDB 10.6, `utf8mb4_unicode_ci`.
- A hosting mailbox for SMTP. SSL enabled for the domain.

### 2) Build locally
```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```
Package **without** `_design/`, `node_modules/`, `tests/`, `.git/`, the local `.env` and log files. Keep `public/build`.

### 3) Layout on the host
**Preferred:** point the domain's document root to the project's `public` folder.

**Otherwise:**
1. Upload the project outside the web root, e.g. `~/class-skilled`.
2. Move the **contents** of `public/` (including `.htaccess`, `build/`, `images/`) into `public_html/`.
3. Edit `public_html/index.php` to require `../class-skilled/vendor/autoload.php`, `../class-skilled/bootstrap/app.php`
   and `../class-skilled/storage/framework/maintenance.php`.
4. Tell Laravel where the public folder is: `$app->usePublicPath(dirname(__DIR__, 2).'/public_html');`
   (in `bootstrap/app.php` after the application is created, or in `AppServiceProvider::register`).

### 4) Production `.env`
See the Arabic section above for the full block: `APP_ENV=production`, `APP_DEBUG=false`, `https` `APP_URL`,
DB credentials, `SESSION_SECURE_COOKIE=true`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`, SMTP mail, an unguessable
`ADMIN_PATH`, and `ADMIN_*` only for the first seed (remove afterwards).

### 5) First deploy commands
```bash
php artisan migrate --force
php artisan db:seed --class=EssentialSeeder --force   # first deploy only
php artisan storage:link
php artisan optimize
php artisan route:trans:cache
php artisan site:check
```
- **No symlinks allowed?** Skip `storage:link`. When `public/storage` does not exist, the app registers a fallback
  route that serves the public disk at `/storage/...` (images, video, PDF only — never scripts). Run `optimize` again.
- **No terminal?** Use cPanel Terminal / a one-off cron entry, or Dashboard → System → Health (same report as
  `site:check`, plus "cache config & routes" and "clear caches" buttons).

### 6) Permissions
`storage/` and `bootstrap/cache/` writable (755 dirs / 644 files). Keep `storage/app/public/.htaccess` (blocks script
execution in uploads).

### 7) After going live
- Enable the HTTPS redirect (uncomment the two lines in `public/.htaccess`); pick www or non-www and redirect the other.
- Google Search Console: verify (code in Dashboard → SEO) and submit `/sitemap.xml`.
- Tracking IDs in Dashboard → Settings → Tracking (printed in production only).
- Test the quote, contact and job application forms; confirm the team e-mail and the visitor auto-reply.
- Create and download a backup (Dashboard → System → Backups). Check System → Health shows no failures.
- Run Lighthouse (mobile) on Home, a service, a project, an article and the quote page; record the results.

### 8) Updates
Build locally, upload changed files (with `public/build`), then `migrate --force`, `optimize:clear`, `optimize`,
`route:trans:cache`. The page cache is flushed automatically on every dashboard change.
