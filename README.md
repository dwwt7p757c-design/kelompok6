# Luxury E-commerce Store (PHP + MySQL)

Starter project toko online mobile-first dengan storefront, cart, checkout pencatatan pesanan, dan dashboard admin.

## Kebutuhan
- PHP 8.0+ (disarankan 8.1+)
- MySQL 5.7+ / MariaDB
- Ekstensi PDO MySQL, Fileinfo
- Hosting yang mendukung PHP dan MySQL

## Instalasi di hosting
1. Buat database MySQL melalui cPanel.
2. Import `database.sql` melalui phpMyAdmin.
3. Edit `config.php`: isi DB_HOST, DB_NAME, DB_USER, DB_PASS.
4. Upload semua file/folder ke `public_html` (atau folder domain).
5. Pastikan folder `uploads/` bisa ditulis oleh PHP (umumnya permission 755; ikuti aturan hosting).
6. Buka `domainkamu.com/admin/create_admin.php` untuk membuat akun admin.
7. Setelah admin berhasil dibuat, HAPUS `admin/create_admin.php` dari hosting.
8. Login melalui `domainkamu.com/admin/login.php`.

## Fitur
- Upload gambar produk JPG/PNG/WEBP maksimal 5 MB.
- Tambah, edit, hapus produk.
- Harga normal dan harga diskon.
- Kategori, stok, ukuran XS–XXL, featured, aktif/nonaktif.
- Storefront responsif dengan grid dua kolom di layar ponsel.
- Detail produk, pilihan ukuran, keranjang, checkout, dan pencatatan pesanan.
- Admin dapat mengubah status pesanan.

## Catatan penting sebelum dipakai sungguhan
- Checkout saat ini hanya mencatat pesanan. Belum ada payment gateway, ongkir otomatis, email/WhatsApp otomatis, atau akun pelanggan.
- Jangan gunakan untuk menerima pembayaran sebelum menambahkan payment gateway resmi, validasi stok saat checkout, proteksi spam/rate limit, HTTPS, backup, dan kebijakan privasi.
- Uji di staging terlebih dahulu. Gunakan password admin yang kuat.
- Untuk upload, server harus mengaktifkan Fileinfo dan izin tulis folder `uploads/`.
