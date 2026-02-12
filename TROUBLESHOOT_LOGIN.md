# 🔧 AgroLink - Admin Login Troubleshooting Guide

## Kwa nini Admin Haikubali Kuingia? (Why Admin Won't Login?)

### ⚡ Quick Fixes (Haraka)

**Option 1: Run Debug Page**
```
Open: http://localhost/agro%20link/debug-login.php
```
This will:
- ✓ Check database connection
- ✓ Verify users table exists
- ✓ List all users
- ✓ Test login function
- ✓ Auto-create admin if missing

---

## 🚨 Common Problems & Solutions

### Problem 1: "Database Connection Failed"
**Sababu:** Database hakuconnect correctly

**Fix:**
1. Check `db.php` credentials:
   - Host: `localhost`
   - Database: `agrolink_db`
   - User: `root`
   - Password: Check your MySQL password

2. Verify MySQL is running
3. Check database name is correct

```php
// Check in db.php (line 7-10)
define('DB_HOST', 'localhost');
define('DB_NAME', 'agrolink_db');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

---

### Problem 2: "Invalid Email or Password"
**Sababu:** Admin account either:
- Doesn't exist
- Password hash is wrong
- Account is blocked

**Fix:**
1. Run debug page to see if admin exists
2. If admin exists, verify account_status:
   ```sql
   SELECT user_id, email, role, account_status FROM users WHERE email='admin@agrolink.local';
   ```
3. If blocked, unblock:
   ```sql
   UPDATE users SET account_status='Active' WHERE email='admin@agrolink.local';
   ```

---

### Problem 3: Admin Account Doesn't Exist
**Sababu:** Admin account was never created

**Fix Option A: Use Debug Page**
- Go to: `http://localhost/agro%20link/debug-login.php`
- Scroll to section 5️⃣
- It will auto-create admin

**Fix Option B: Manual SQL**
```sql
INSERT INTO users (full_name, email, password, role, account_status, registration_date)
VALUES (
    'System Administrator',
    'admin@agrolink.local',
    '$2y$10$XK7w3rbNJ3/u.pqLnrwPh.fozrnHuPjVGdx1aMvdJNxVt5f7f6w1m',
    'Admin',
    'Active',
    NOW()
);
```

**Fix Option C: Use setup_admin.php**
- Go to: `http://localhost/agro%20link/setup_admin.php`
- It will create admin for you

---

### Problem 4: Session Not Starting
**Sababu:** Session start issue or headers already sent

**Fix:**
1. Make sure no whitespace before `<?php` in login.php
2. Verify session_start() is first line after `<?php`
3. Check no `echo` or HTML before PHP code

---

## ✅ Complete Setup Process

### Step 1: Reset Everything
```sql
-- Open phpMyAdmin or MySQL client
DROP DATABASE agrolink_db;
```

### Step 2: Create Fresh Database
```sql
-- Execute all of database.sql
CREATE DATABASE IF NOT EXISTS agrolink_db;
USE agrolink_db;
-- ... (rest of database.sql)
```

### Step 3: Create Admin Account
Choose ONE option:

**Option A (Auto):**
```
http://localhost/agro%20link/debug-login.php
```
Scroll to section 5️⃣ and let it create admin

**Option B (Manual):**
```
http://localhost/agro%20link/setup_admin.php
```

**Option C (SQL):**
```sql
INSERT INTO users (full_name, email, password, role, account_status, registration_date)
VALUES (
    'System Administrator',
    'admin@agrolink.local',
    '$2y$10$XK7w3rbNJ3/u.pqLnrwPh.fozrnHuPjVGdx1aMvdJNxVt5f7f6w1m',
    'Admin',
    'Active',
    NOW()
);
```

### Step 4: Login
Go to: `http://localhost/agro%20link/login.php`

**Credentials:**
- Email: `admin@agrolink.local`
- Password: `admin123`

---

## 🔍 Verification Checklist

- [ ] Database `agrolink_db` exists
- [ ] `users` table exists with all columns
- [ ] Admin user exists in database:
  ```sql
  SELECT * FROM users WHERE email='admin@agrolink.local';
  ```
- [ ] Admin account_status is 'Active'
- [ ] Password is for `admin123` (hashed with bcrypt)
- [ ] Role is 'Admin'
- [ ] `db.php` has correct credentials
- [ ] `functions.php` loads correctly
- [ ] `login.php` can reach `functions.php`

---

## 🆘 Last Resort: Full System Check

Run this command to verify everything:

```bash
# Check if PHP can connect to database
php -r "
require_once 'db.php';
\$stmt = \$GLOBALS['conn']->query('SELECT * FROM users WHERE email=\"admin@agrolink.local\"');
\$user = \$stmt->fetch();
if (\$user) {
    echo 'Admin exists: ' . print_r(\$user, true);
} else {
    echo 'Admin not found';
}
"
```

---

## 📞 Still Having Issues?

1. **Check Apache/PHP logs:**
   ```
   C:\xampp\apache\logs\error.log
   C:\xampp\php\logs\php_errors.log
   ```

2. **Check file permissions:**
   - All .php files should be readable

3. **Check PORT:**
   - Default: `http://localhost`
   - If different: `http://localhost:PORT`

4. **Restart Services:**
   ```bash
   # Windows
   net stop Apache2.4
   net start Apache2.4
   ```

---

## ✨ Files That Help Debug

| File | Purpose |
|------|---------|
| `debug-login.php` | Interactive debug tool |
| `setup_admin.php` | Create admin account |
| `database.sql` | Fresh database setup |
| `db.php` | Database config (verify credentials) |
| `functions.php` | Check loginUser() function |

**Test in this order:**
1. debug-login.php ← Start here
2. setup_admin.php ← If admin missing
3. login.php ← Try login

---

**Nakushangilia! (Good luck!)** 🌾
