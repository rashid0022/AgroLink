# AgroLink Admin Setup Guide

## Quick Start - Setting Up Admin Account

### Step 1: Access Setup Page
After your database is created and `database.sql` has been executed:

1. **First Time Setup**:
   - Navigate to: `http://localhost/agro%20link/setup.php`
   - This page will check if your database is properly configured
   - It will automatically create a default admin account

2. **Or Create Admin Manually**:
   - Navigate to: `http://localhost/agro%20link/register_admin.php`
   - Create an admin account with your credentials
   - Only the first admin can be created this way (prevents unauthorized admin creation)

### Step 2: Login to Admin Account

**Default Admin Credentials** (if using setup.php):
```
Email:    admin@agrolink.local
Password: admin123
```

**Login Page**: `http://localhost/agro%20link/login.php`

After login, you'll be redirected to: **Admin Dashboard** (`dashboard/admin.php`)

### Step 3: First Admin Actions

1. **Change Default Password**
   - Click on your profile
   - Update password immediately for security

2. **Verify Initial Farmers**
   - Go to: Dashboard → Manage Farmers
   - Review farmer registrations
   - Verify documents and approve farming applications

3. **Review Products**
   - Go to: Dashboard → Manage Products
   - Approve products before they appear in marketplace
   - Reject low-quality or policy-violating products

4. **Monitor Orders**
   - Go to: Dashboard → Manage Orders
   - Track order flow and delivery status
   - Ensure customer satisfaction

5. **Manage Payments**
   - Go to: Dashboard → Manage Payments
   - Release payments to farmers on delivery
   - Handle refunds if necessary

6. **Handle Reports**
   - Go to: Dashboard → Manage Reports
   - Review customer complaints and fraud reports
   - Resolution and follow-up

7. **User Management**
   - Go to: Dashboard → Manage Users
   - Block problematic users if needed
   - Monitor account status

## Admin Capabilities

Once logged in as admin, you have access to:

✅ **Farmer Management**
- View all farmers
- Verify farmer applications
- Suspend accounts
- View farm details

✅ **Product Management**
- Review pending products
- Approve/Reject products
- Monitor inventory levels

✅ **Order Management**
- View all orders
- Track delivery status
- Monitor transaction flow

✅ **Payment Management**
- Release payments to farmers (escrow system)
- Process refunds
- View payment history

✅ **Report Management**
- Handle customer complaints
- Review fraud reports
- Resolve disputes

✅ **User Management**
- Block/Enable user accounts
- View user roles and status
- Monitor registrations

## File Structure

```
/agro link/
├── setup.php                 ← Run this first-time setup
├── register_admin.php        ← Create additional admin accounts
├── login.php                 ← Login for all users
├── dashboard/
│   ├── admin.php            ← Admin main dashboard
│   ├── manage_farmers.php   ← Verify farmers
│   ├── manage_products.php  ← Approve products
│   ├── manage_orders.php    ← View orders
│   ├── manage_payments.php  ← Release payments
│   ├── manage_reports.php   ← Handle complaints
│   └── manage_users.php     ← Block/enable users
├── db.php                   ← Database configuration
└── functions.php            ← Core business logic
```

## Setup Workflow

```
1. Create database: agrolink_db
2. Import database.sql (creates tables)
3. Access setup.php (creates default admin)
4. Login at login.php
5. Start managing farmers and products
```

## Security Notes

⚠️ **IMPORTANT**:
- Change default admin password on first login
- Only first admin account can be created via register_admin.php
- Subsequent admins must be created by existing admin (feature to be added)
- All passwords are hashed with bcrypt
- Use HTTPS in production

## Troubleshooting

### "Database tables not found"
- Make sure database.sql was executed
- Check database name matches in db.php

### "Admin account already exists"
- You can only have one initial admin
- Login with existing admin credentials

### "Connection failed"
- Check db.php credentials
- Verify MySQL is running
- Ensure database user has permissions

## Next: Farmer Registration

Farmers can self-register at:
- `http://localhost/agro%20link/register_farmer.php`

Customers can register at:
- `http://localhost/agro%20link/register_customer.php`

---

**Questions?** Check functions.php for all available functions and business logic.
