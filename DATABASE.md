# 🗄️ Database ও ছবি Sync করার নিয়ম

এই repo-তে পুরো database আর admin থেকে upload করা সব ছবি রাখা আছে। তাই যেকোনো computer-এ clone করলেই আসল data সহ site চালানো যায়।

| কী | কোথায় |
|---|---|
| Database (সব table + data) | [`database/dump/gadget_website.sql`](database/dump/gadget_website.sql) |
| Upload করা ছবি (product, banner, settings) | `storage/app/public/` |

> ⚠️ **Repo সবসময় private রাখুন।** Dump-এ user-দের email, password hash আর order-এর তথ্য আছে।
>
> Login session (`sessions`) আর `cache` table খালি করে dump বানানো হয়, শুধু table-গুলো তৈরি হয়।

নিচের command-গুলো **XAMPP (Windows)** ধরে লেখা। অন্য জায়গায় MySQL থাকলে `C:/xampp/mysql/bin/` অংশটা বাদ দিয়ে শুধু `mysql` / `mysqldump` লিখুন।

---

## ১. নতুন computer-এ প্রথমবার চালানো

```bash
# Code নামান
git clone https://github.com/SourovKarmokar/GadGet.git
cd GadGet

# Package install
composer install
npm install
npm run build

# Environment
cp .env.example .env
php artisan key:generate
```

`.env` খুলে database-এর নাম দিন:

```env
DB_DATABASE=gadget_website
DB_USERNAME=root
DB_PASSWORD=
```

তারপর database তৈরি করে dump import করুন:

```bash
C:/xampp/mysql/bin/mysql.exe -uroot -e "CREATE DATABASE gadget_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
C:/xampp/mysql/bin/mysql.exe -uroot gadget_website -e "source database/dump/gadget_website.sql"

php artisan migrate        # dump-এর পরে নতুন migration থাকলে সেগুলো চালাবে
php artisan storage:link   # ছবি দেখানোর জন্য দরকার
composer run dev
```

Admin login dump-এর user দিয়েই হবে (যে email/password আগের computer-এ ছিল)।

> ⚠️ **`migrate --seed` চালাবেন না।** এতে dump-এর data-র উপর demo data বসে যাবে।

---

## ২. Local-এ data বদলানোর পর GitHub-এ পাঠানো

Admin panel-এ product, ছবি, settings যা-ই বদলান, এভাবে GitHub-এ পাঠান:

```bash
# Login session আর cache খালি করুন (এগুলো repo-তে যাওয়ার দরকার নেই)
C:/xampp/mysql/bin/mysql.exe -uroot "gadget website" -e "TRUNCATE sessions; TRUNCATE cache; TRUNCATE cache_locks;"

# Dump বানান
C:/xampp/mysql/bin/mysqldump.exe -uroot --single-transaction --routines --triggers --add-drop-table --default-character-set=utf8mb4 --skip-dump-date --result-file=database/dump/gadget_website.sql "gadget website"

git add -A
git commit -m "Update database dump"
git push
```

> `"gadget website"` হলো এই computer-এর database-এর নাম। অন্য computer-এ নাম আলাদা হলে (যেমন `gadget_website`) দুই জায়গাতেই নামটা বদলে দিন।
>
> প্রথম command-এর পর admin panel থেকে logout হয়ে যাবেন, আবার login করলেই হবে।

নতুন ছবি `storage/app/public/`-এ থাকে, তাই `git add -A` করলে সেগুলোও চলে যায়।

---

## ৩. অন্য computer-এ নতুন data আনা

```bash
git pull
C:/xampp/mysql/bin/mysql.exe -uroot gadget_website -e "source database/dump/gadget_website.sql"
php artisan migrate
php artisan optimize:clear
```

> ⚠️ Import করলে ওই computer-এর database-এর **পুরনো data মুছে** dump-এর data বসবে। ওখানে নতুন কিছু থাকলে আগে সেটা export করে push করুন (ধাপ ২)।

---

## ⚠️ সতর্কতা

- **দুই জায়গায় একসাথে data বদলাবেন না।** Database merge হয় না, যে পরে push করবে তার dump-টাই থাকবে। একজন কাজ করে push করুন, অন্যজন pull + import করে তারপর কাজ শুরু করুন।
- **PowerShell-এ `>` বা `<` দিয়ে dump বানাবেন/import করবেন না।** PowerShell 5.1 file-টা UTF-16-এ লেখে, ফলে dump নষ্ট হয়। উপরের `--result-file` আর `source` দিয়ে করলে সব terminal-এ ঠিক থাকে।
- **Live server-এর database এভাবে ওভাররাইট করবেন না।** এটা local development-এর জন্য। Live-এ import করলে customer-দের নতুন order মুছে যাবে।

---

## 🛠️ সমস্যা হলে

| সমস্যা | সমাধান |
|---|---|
| ছবি দেখাচ্ছে না | `php artisan storage:link` চালান |
| `Unknown database` | ধাপ ১-এর `CREATE DATABASE` command চালান, আর `.env`-এর `DB_DATABASE` মিলিয়ে দেখুন |
| `Table 'sessions' doesn't exist` | Dump আবার import করুন (ধাপ ৩)। এরপরও না হলে ধাপ ২-এর মতো নতুন dump বানিয়ে আনুন |
| বাংলা লেখা `???` দেখাচ্ছে | database `utf8mb4` দিয়ে তৈরি করুন (ধাপ ১) |
| `MySQL server has gone away` | XAMPP-এর `my.ini`-তে `max_allowed_packet=64M` দিয়ে MySQL restart করুন |
| পুরনো page/settings দেখাচ্ছে | `php artisan optimize:clear` |
