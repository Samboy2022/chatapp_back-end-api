# 🔐 Admin Credentials - Quick Reference

## 🌐 Admin Panel Access

**URL:** http://localhost:8000/admin/login

---

## 👤 Admin Accounts

### 1. Primary Admin (Recommended)
```
Email:    admin@chatapp.com
Password: admin123
Phone:    +1999000001
```

### 2. ChatWave Admin
```
Email:    admin@chatwave.com
Password: password
Phone:    +1999000002
```

### 3. Super Admin
```
Email:    superadmin@chatapp.com
Password: admin123
Phone:    +1999000003
```

---

## ⚠️ Security Notice

**IMPORTANT:** Change these default passwords immediately in production!

### Change Password via Tinker:
```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@chatapp.com')->first();
$admin->password = Hash::make('your_new_secure_password');
$admin->save();
```

---

## 🔄 Re-seed Admin Data

If you need to recreate admin accounts:

```bash
php artisan db:seed --class=AdminSeeder
```

---

## 📝 Notes

- All admin accounts have full access to the admin panel
- Admin authentication is session-based
- Admin emails are whitelisted in `AuthController.php`
- First user (ID: 1) automatically has admin access

---

**Last Updated:** October 8, 2025
