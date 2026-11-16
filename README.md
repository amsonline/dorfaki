# محمدرضا درفکی - وب‌سایت مشاور املاک

وب‌سایت حرفه‌ای مشاور املاک با استفاده از **PHP**، **TailwindCSS** و **jQuery**.

## 📋 فناوری‌های استفاده شده

- **PHP 7.4+** — سرور وب بک‌اند (بدون وابستگی خارجی، فقط از توابع نیتیو PHP استفاده می‌کند)
- **Apache** (با `.htaccess`) یا **PHP Built-in Server** — وب‌سرور
- **TailwindCSS v4** — فریم‌ورک CSS برای رابط کاربری زیبا
- **jQuery** — کتابخانهٔ جاوااسکریپت برای تعاملات فرانت‌اند
- **Vazirmatn** — فونت اصلی فارسی
- **RTL** — پشتیبانی کامل از متن‌های راست‌به‌چپ

## 🚀 شروع سریع

### پیش‌نیازها

- **PHP 7.4+** (با پشتیبانی از `mod_rewrite` در Apache یا `php -S` برای توسعه)
- **Node.js + npm** (فقط برای ساخت CSS — در production مورد نیاز نیست)

### نصب وابستی‌ها (فقط برای ساخت CSS)

```bash
npm install
```

### ساخت CSS (یک بار)

برای ساخت فایل CSS استفاده شده در وب‌سایت:

```bash
npm run build:css
```

### حالت توسعه

سرور PHP با حالت گواه‌نگاری CSS را اجرا کنید (CSS در حالت watch بازسازی می‌شود):

```bash
npm run dev
```

سپس وب‌سایت را در مرورگر خود باز کنید:
[http://localhost:3000](http://localhost:3000)

### اجرا در محیط Production

فقط کافی است فایل `index.php` و `.htaccess` را در ریشهٔ وب‌سرور خود آپلود کنید. فایل CSS (`public/css/output.css`) از پیش ساخته شده است و نیازی به Node.js ندارید.

اگر از سرور مشترک (shared hosting) با Apache استفاده می‌کنید، کافی است:

1. پوشهٔ پروژه را در ریشهٔ وب‌سرور (document root) آپلود کنید.
2. مطمئن شوید `.htaccess` فعال است (معمولاً توسط میزبان فعال است).
3. دسترسی‌های پوشهٔ `public/` و `src/views/` را بررسی کنید.

برای تست محلی بدون Apache:

```bash
php -S localhost:3000 index.php
```

## 📁 ساختار پروژه

```
dorfaki/
├── index.php              # سرور PHP (Front Controller) — جایگزین server.js
├── .htaccess              # پیکربندی Apache برای URLهای زیبا
├── package.json           # فایل پیکربندی npm (فقط برای ساخت CSS)
├── tailwind.config.js     # پیکربندی TailwindCSS
├── src/
│   ├── input.css          # ورودی TailwindCSS (کامپایل می‌شود به output.css)
│   └── views/
│       └── index.html     # صفحهٔ اصلی وب‌سایت (به زبان فارسی، RTL)
├── public/
│   ├── css/
│   │   └── output.css     # CSS کامپایل شده توسط Tailwind
│   ├── js/
│   │   ├── jquery.min.js  # jQuery (از node_modules کپی شده)
│   │   └── app.js         # منطق جاوااسکریپت برنامه
│   └── fonts/             # فونت‌های محلی (در صورت نیاز)
└── .gitignore
```

## 🎨 شخصی‌سازی

### رنگ‌ها

رنگ‌های سفارشی در `tailwind.config.js` قابل ویرایش هستند:

- `primary` — رنگ اصلی آبی
- `secondary` — رنگ ثانویهٔ قرمز

### فونت

فونت Vazirmatn از طریق Google Fonts بارگذاری می‌شود و در `src/input.css` به عنوان فونت پیش‌فرض تنظیم شده است.

### محتوا

تمام متن‌های صفحهٔ اصلی در `src/views/index.html` به زبان فارسی نوشته شده‌اند و شامل بخش‌های زیر هستند:

- بخش هدر با منوی ناوبری
- اسکشن معرفی (Hero)
- بخش درباره‌ما
- بخش خدمات (فروش خرید، اجاره، ارزیابی ارزش)
- بخش نظرات مشتریان
- فرم تماس با ما
- فوتر وب‌سایت

## 📱 واکنش‌گرا بودن

وب‌سایت کاملاً واکنش‌گرا (Responsive) است و با استفاده از کلاس‌های واکنش‌گرای TailwindCSS (sm, md, lg) در تمام دستگاه‌ها به درستی نمایش داده می‌شود.

## 🔧 اسکریپت‌های npm

| اسکریپت | توضیح |
|--------|-------|
| `npm run build:css` | ساخت CSS حالت production (minified) |
| `npm run watch:css` | نظارت بر تغییرات CSS در حالت توسعه |
| `npm run dev` | اجرای حالت توسعه (CSS watch + PHP server) |
| `npm start` | اجرای سرور PHP برای تست محلی |

> ⚠️ **نکته:** npm/Node.js فقط برای ساخت CSS مورد نیاز است. در محیط Production فقط PHP کافی است.

## 📡 API endpoints

| مسیر | روش | توضیح |
|------|-----|-------|
| `/` | GET | صفحهٔ اصلی وب‌سایت |
| `/health` | GET | بررسی سلامت سرور |
| `/api/contact` | POST | دریافت پیام از فرم تماس |
| `/css/output.css` | GET | فایل CSS |
| `/js/app.js` | GET | فایل JavaScript اصلی |
| `/js/jquery.min.js` | GET | کتابخانه jQuery |

## 🛡️ امنیت

- **Content-Security-Policy (CSP):** محدود کنندهٔ بارگذاری منابع
- **X-Content-Type-Options:** جلوگیری از MIME sniffing
- **X-Frame-Options:** جلوگیری از clickjacking
- **Rate Limiting:** حداکثر ۱۰۰ درخواست در ۱۵ دقیقهٔ هر IP
- **Path Traversal Protection:** جلوگیری از دسترسی به فایل‌های خارج از `public/`

## 🌐 منابع فونت

- [Vazirmatn در Google Fonts](https://fonts.google.com/specimen/Vazirmatn)
- [Vazirmatn در گیت‌هاب](https://github.com/38leeds/Vazirmatn)

## 📄 لایسنس

ISC
