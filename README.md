# KELOMPOK 6 — curated e-commerce starter

A PHP 8 / MySQL storefront with a product catalog, category and price filters, session shopping bag, inventory-aware checkout, order tracking confirmation, newsletter signup, and administrator dashboard.

## Requirements

- PHP 8.0+ (PHP 8.1+ recommended), with PDO MySQL enabled
- MySQL 5.7+ or a compatible MariaDB server
- A web server with PHP support (Apache, Nginx/PHP-FPM, or PHP's development server)

## Local setup

1. Create a MySQL database and import `database.sql`. On shared hosting, create the database using the hosting control panel and adjust/remove `CREATE DATABASE` and `USE` in the SQL file if your host provides a prefixed name.
2. Create a private `/.env.php` file in the project root (do not commit it):

   ```php
   <?php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database');
   define('DB_USER', 'your_user');
   define('DB_PASS', 'your_password');
   ```

3. Set the web document root to the project directory. For local testing, run `php -S 127.0.0.1:8000` from the project directory and open `http://127.0.0.1:8000`.
4. Create the first admin from a trusted terminal: `php admin/create_admin.php yourusername you@example.com`. Enter a unique password of at least 12 characters. The setup script only works in CLI and refuses to create another admin once an account exists.
5. Sign in at `/admin/login.php`, add categories (insert them in the database for now), products and stock; use Settings to customize store content and contact information.

## Important production notes

- Keep `.env.php` private and ensure it is not publicly downloadable. The included `.htaccess` denies access to it on Apache; configure equivalent rules on other servers.
- Use HTTPS. Configure the web server to disable directory listing and to route PHP securely. Do not rely on `.htaccess` under Nginx.
- Configure and test payment verification, shipping, refunds, tax, email delivery, backups, and legal/privacy policies before taking real orders. Checkout records an order as awaiting payment; it does not collect or verify payment.
- Contact form is an informational demo and does not send/store submissions. Configure a trusted mail service before representing it as functional.
- Product image values should point only to trusted, publicly readable image paths/URLs. This starter does not provide an upload manager.
- Review PHP error logs and keep display_errors disabled in production.

## Project paths

- `index.php`, `shop.php`, `product.php`: storefront and catalog
- `cart.php`, `checkout.php`, `order_success.php`: basket and order flow
- `admin/`: protected dashboard, product/order management, store settings
- `includes/`: shared storefront components
- `assets/css/style.css`, `assets/js/app.js`: responsive UI styles and menu behavior
- `database.sql`: database schema and initial settings

## Notes

The project is a starter and should be security-reviewed and tested with the actual PHP/MySQL version and hosting environment before production use.
