# Initial Setup Guide

## Step 1: Create Initial Tenant

Before you can log in, you need to create at least one tenant in the database.

### Option A: Via phpMyAdmin or MySQL Client

```sql
INSERT INTO tenants (name, region) VALUES ('My Company', 'us-east');
```

Note the `id` that gets created (usually `1` if this is the first tenant).

### Option B: Via SQL File

Create a file `setup-tenant.sql`:

```sql
INSERT INTO tenants (name, region) VALUES ('My Company', 'us-east');
```

Then import it:
```bash
mysql -u your_user -p your_database < setup-tenant.sql
```

## Step 2: Test Login

1. Go to your login page: `https://yourdomain.com/login`
2. You should see a "Development Mode" form
3. Enter:
   - **Email**: `admin@example.com` (or any email)
   - **Tenant ID**: `1` (or the ID you created in Step 1)
4. Click "Test Login"
5. You'll be logged in as an Administrator and redirected to the dashboard

## Step 3: Configure SSO (Optional - For Production)

When ready to use real SSO:

1. Set up your SSO provider (Microsoft Entra ID or Google Workspace)
2. Add to `.env`:
   ```
   SSO_ENTRA_ENABLED=true
   SSO_ENTRA_CLIENT_ID=your_client_id
   SSO_ENTRA_CLIENT_SECRET=your_client_secret
   SSO_ENTRA_TENANT_ID=your_tenant_id
   SSO_ENTRA_REDIRECT_URI=https://yourdomain.com/auth/callback
   ```
3. The development login form will disappear and SSO buttons will appear

## Notes

- The first user created for a tenant gets `Administrator` role by default
- Subsequent users get `Guest` role by default (can be changed in Admin panel)
- Development login is only shown when SSO is not configured
- In production, remove or disable development login for security

