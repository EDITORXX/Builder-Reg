# Hostinger Shared Hosting par Laravel Deploy (SSH se)

Yeh guide Hostinger shared hosting par is Laravel (Builder Platform) project ko SSH use karke deploy karne ke liye hai.

---

## 0. Subdomain + Install wizard (sabse aasan – non-coders ke liye)

Agar tum **subdomain** use kar rahe ho (jaise `app.yourdomain.com`) aur **coding nahi karna** chahte, ye steps follow karo:

1. **Hostinger me subdomain banao**  
   hPanel → Domains → Subdomains → subdomain add karo (e.g. `app` → `app.yourdomain.com`). Document root wahi set karo jahan Laravel ka **public** folder hoga (step 7 dekho).

2. **Git se code lao**  
   SSH se connect karke project folder me:  
   `git pull origin main`  
   (Pehli baar: `git clone https://github.com/EDITORXX/Builder-Reg.git builder-platform` aur `composer install --no-dev --optimize-autoloader`.)

3. **Document root** Laravel ke `public` par point karo (symlink ya copy – section 7 dekho).  
   `chmod -R 775 storage bootstrap/cache` chala lo.

4. **Browser me install page kholo**  
   Open: **https://app.yourdomain.com/install**

5. **Form bharo**  
   - **App:** App name, App URL (e.g. `https://app.yourdomain.com`)  
   - **Database:** Hostinger se MySQL DB banao, phir host, DB name, username, password daalo  
   - **Mail:** SMTP details (ya “Log” choose karo agar abhi email nahi bhejna)  
   - **Super Admin:** Apna email aur password daalo (ye hi se tum login karoge)

6. **Install** pe click karo.  
   App khud `.env` bana legi, database migrate karegi, plans seed karegi aur tumhara Super Admin account bana degi.

7. **Login**  
   Same URL se login page open hoga; wahi email/password se sign in karo. Uske baad `/install` band ho jayega – dobara access nahi hoga.

**Future updates:** Server pe `git pull origin main`, phir zarurat ho to `composer install --no-dev`, `php artisan migrate --force`, `php artisan config:cache`. `.env` phir se edit karne ki zarurat nahi (sirf naye env vars add karne ho to manually daal sakte ho).

---

## 1. Hostinger pe requirements check karo

- **SSH access** enabled ho (hPanel → Advanced → SSH Access).
- **PHP 8.1+** (Hostinger pe PHP version select kar sakte ho).
- **MySQL** database (hPanel se banao).
- **Composer** – shared hosting pe usually available hota hai; agar nahi to support se pucho.

---

## 2. SSH se server pe connect

```bash
ssh u123456789@yourdomain.com
# ya
ssh u123456789@server.hostinger.com
```

Username aur server address hPanel → SSH Access se milega.

---

## 3. Document root samajhna

Hostinger pe usually:
- **Document root:** `public_html` (yahi domain ka root hota hai)
- Laravel me **sirf `public` folder** web-accessible hona chahiye, poora project nahi.

**Option A (recommended):** Project `public_html` ke **bahar** rakhna, aur `public_html` ko Laravel ke `public` folder ki taraf point karna (symlink ya .htaccess se).

**Option B:** Project `public_html` ke andar rakhna aur subfolder use karna (e.g. `public_html/builder`), phir `public_html/builder/public` ko document root banana (agar Hostinger subdomain/folder document root allow karta ho).

Neeche Option A maan kar steps diye hain.

---

## 4. Project clone / upload

### Option 4a: Git se clone (agar server pe git hai)

```bash
cd ~
# ya jahan project rakhna hai (public_html ke bahar)
git clone https://github.com/EDITORXX/Builder-Reg.git builder-platform
cd builder-platform
```

Agar private repo ho to SSH key ya token use karo.

### Option 4b: Git nahi hai to ZIP upload

1. Local me: `git archive -o builder-platform.zip main` (ya project ko ZIP karo, `.env` exclude).
2. Hostinger File Manager se ZIP upload karo `public_html` ke **parent** folder me (e.g. `domains/yourdomain.com/`).
3. SSH se:
   ```bash
   cd ~/domains/yourdomain.com   # apna path adjust karo
   unzip builder-platform.zip -d builder-platform
   cd builder-platform
   ```

---

## 5. Composer install (production, no dev)

```bash
cd ~/builder-platform   # ya jahan project hai
php composer.phar install --no-dev --optimize-autoloader
```

Agar `composer` command direct chalta hai:
```bash
composer install --no-dev --optimize-autoloader
```

---

## 6. Environment file

**Option A – Install wizard (recommended):** Section 0 follow karo; `/install` page se saari details daalne par `.env` khud ban jati hai.

**Option B – Manual:**  
```bash
cp .env.example .env
php artisan key:generate
```

`.env` edit karo (nano/vi ya File Manager se):

```env
APP_NAME="Builder Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_dbname
DB_USERNAME=u123456789_dbuser
DB_PASSWORD=your_db_password
```

DB name/user/password Hostinger hPanel → Databases se lo.

---

## 7. Document root point karna (public folder)

Laravel me entry point `public/index.php` hona chahiye. Hostinger pe `public_html` hi domain root hota hai.

**Method 1: public_html me .htaccess se redirect (subfolder me project ho to)**

Agar project path hai: `~/domains/yourdomain.com/builder-platform`:

`public_html/.htaccess` me kuch aisa (path apne hisaab se):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ builder-platform/public/$1 [L]
</IfModule>
```

Ye tab use karo jab `public_html` ke andar hi project ho. Better approach: **Method 2**.

**Method 2: public_html ko replace karke Laravel public use karna (recommended)**

1. Purani `public_html` ka backup lo ya rename karo:
   ```bash
   cd ~/domains/yourdomain.com
   mv public_html public_html_backup
   ```
2. Laravel ke `public` folder ka symlink banao with name `public_html`:
   ```bash
   ln -s ~/domains/yourdomain.com/builder-platform/public public_html
   ```
   (Path apna actual path se replace karo.)

Agar symlink allow nahi hai (kuch shared hosts pe nahi hota), to:
- `public_html` me sirf Laravel ke `public` wale files copy karo, aur `index.php` me `require` paths adjust karo (Laravel docs “Deployment” me “Web Server Configuration” dekho).

---

## 8. Storage aur cache permissions

```bash
cd ~/builder-platform
chmod -R 775 storage bootstrap/cache
# Agar apache user different hai (e.g. nobody, www-data):
# chown -R u123456789:u123456789 storage bootstrap/cache
```

---

## 9. Database migrate aur seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 10. Cache aur config optimize

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 11. Storage link (optional, agar public uploads chahiye)

```bash
php artisan storage:link
```

Ye `public/storage` ko `storage/app/public` se link karega. Logo etc. ke liye zaroori ho sakta hai.

---

## 12. .env aur security

- `.env` kabhi bhi web-accessible nahi honi chahiye (sirf `public` document root me hai to safe).
- `APP_DEBUG=false` production me hamesha.
- `APP_KEY` generate zaroor kiya ho.

---

## 13. Future updates (Git se)

Jab bhi naya code push karo, SSH se:

```bash
cd ~/builder-platform
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 14. Agar kuch na chale

- **500 error:** `storage/logs/laravel.log` dekho; permissions `storage` aur `bootstrap/cache` check karo.
- **DB connect nahi:** `.env` me `DB_*` values aur Hostinger DB host (often `localhost`) verify karo.
- **CSS/JS load nahi:** `APP_URL` `.env` me sahi domain se set karo; `php artisan config:cache` phir se chalao.

---

## Short checklist

1. SSH se connect
2. Project clone/upload (public_html ke bahar)
3. `composer install --no-dev --optimize-autoloader`
4. `.env` banao, `APP_KEY` generate, DB details daalo
5. Document root = Laravel ka `public` (symlink/public_html replace)
6. `chmod -R 775 storage bootstrap/cache`
7. `php artisan migrate --force` (+ seed if needed)
8. `config:cache`, `route:cache`, `view:cache`
9. Browser me domain open karke test karo

Iske baad app Hostinger shared hosting par deploy ho chuki hogi.
