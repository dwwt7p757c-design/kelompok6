<?php
require_once __DIR__.'/../config.php'; admin_required();
$uploadDir=__DIR__.'/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
$msg='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 csrf_check(); $action=$_POST['action']??'';
 if ($action==='delete') {
   $id=(int)$_POST['id']; $q=db()->prepare("SELECT image FROM products WHERE id=?"); $q->execute([$id]); $old=$q->fetchColumn();
   db()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
   if($old && is_file(__DIR__.'/../'.$old)) @unlink(__DIR__.'/../'.$old);
   $msg='Produk dihapus.';
 } else {
   $id=(int)($_POST['id']??0); $name=trim($_POST['name']??''); $price=(float)($_POST['price']??0);
   $sale=($_POST['sale_price']??'')!==''?(float)$_POST['sale_price']:null;
   $slug=strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/','-', $name),'-')).'-'.substr(bin2hex(random_bytes(3)),0,6);
   $image=$_POST['old_image']??'';
   if (!empty($_FILES['image']['name'])) {
     $f=$_FILES['image']; $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
     $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
     if (!isset($allowed[$mime]) || $f['size']>5*1024*1024) { $msg='Foto harus JPG, PNG, atau WEBP dan maksimal 5 MB.'; }
     else {
       $filename='product_'.bin2hex(random_bytes(8)).'.'.$allowed[$mime];
       if(move_uploaded_file($f['tmp_name'],$uploadDir.$filename)) $image='uploads/'.$filename;
     }
   }
   if (!$msg && $name!=='' && $price>=0) {
     if($id) {
       db()->prepare("UPDATE products SET name=?,description=?,price=?,sale_price=?,category=?,image=?,stock=?,featured=?,active=? WHERE id=?")
       ->execute([$name,trim($_POST['description']??''),$price,$sale,trim($_POST['category']??'New Arrivals'),$image,(int)($_POST['stock']??0),isset($_POST['featured'])?1:0,isset($_POST['active'])?1:0,$id]);
       db()->prepare("DELETE FROM product_sizes WHERE product_id=?")->execute([$id]);
     } else {
       db()->prepare("INSERT INTO products(name,slug,description,price,sale_price,category,image,stock,featured,active) VALUES(?,?,?,?,?,?,?,?,?,?)")
       ->execute([$name,$slug,trim($_POST['description']??''),$price,$sale,trim($_POST['category']??'New Arrivals'),$image,(int)($_POST['stock']??0),isset($_POST['featured'])?1:0,isset($_POST['active'])?1:0]);
       $id=(int)db()->lastInsertId();
     }
     foreach (['XS','S','M','L','XL','XXL'] as $size) {
       $qty=(int)($_POST['sizes'][$size]??0);
       if($qty>0) db()->prepare("INSERT INTO product_sizes(product_id,size_name,stock) VALUES(?,?,?)")->execute([$id,$size,$qty]);
     }
     $msg='Produk berhasil disimpan.';
   }
 }
}
$edit=null;
if(isset($_GET['edit'])) { $q=db()->prepare("SELECT * FROM products WHERE id=?"); $q->execute([(int)$_GET['edit']]); $edit=$q->fetch(); }
$rows=db()->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();
$sizes=[]; if($edit){$q=db()->prepare("SELECT size_name,stock FROM product_sizes WHERE product_id=?");$q->execute([$edit['id']]);foreach($q as $x)$sizes[$x['size_name']]=$x['stock'];}
?><!doctype html><html lang="id"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Kelola Produk</title><link rel="stylesheet" href="../assets/style.css"><body class="admin-body"><header class="admin-top"><a class="brand" href="../index.php"><?=e(setting('store_name','ATELIER'))?></a><span>PRODUCT MANAGEMENT</span><a href="logout.php">Keluar</a></header><main class="admin-wrap"><p class="eyebrow">INVENTORY</p><h1>Kelola produk</h1><nav class="admin-nav"><a href="dashboard.php">Ringkasan</a><a class="active" href="products.php">Produk</a><a href="orders.php">Pesanan</a><a href="settings.php">Pengaturan toko</a></nav>
<?php if($msg):?><p class="notice"><?=e($msg)?></p><?php endif;?>
<section class="panel"><h2><?=$edit?'Edit produk':'Tambah produk baru'?></h2><form method="post" enctype="multipart/form-data" class="admin-form"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=e($edit['id']??0)?>"><input type="hidden" name="old_image" value="<?=e($edit['image']??'')?>">
<label>Nama produk<input name="name" required value="<?=e($edit['name']??'')?>"></label><label>Kategori<input name="category" value="<?=e($edit['category']??'New Arrivals')?>"></label>
<div class="two-fields"><label>Harga normal (Rp)<input type="number" name="price" min="0" step="1000" required value="<?=e($edit['price']??'')?>"></label><label>Harga diskon (opsional)<input type="number" name="sale_price" min="0" step="1000" value="<?=e($edit['sale_price']??'')?>"></label></div>
<label>Deskripsi<textarea name="description" rows="4"><?=e($edit['description']??'')?></textarea></label><label>Foto produk (JPG/PNG/WEBP, maks. 5 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
<?php if(!empty($edit['image'])):?><img class="preview" src="../<?=e($edit['image'])?>" alt="Foto produk saat ini"><?php endif;?>
<label>Total stok<input type="number" name="stock" min="0" value="<?=e($edit['stock']??0)?>"></label><p class="muted small">Stok per ukuran (opsional)</p><div class="size-stock"><?php foreach(['XS','S','M','L','XL','XXL'] as $sz):?><label><?=$sz?><input type="number" min="0" name="sizes[<?=$sz?>]" value="<?=e($sizes[$sz]??0)?>"></label><?php endforeach;?></div>
<label class="check"><input type="checkbox" name="featured" <?=!empty($edit['featured'])?'checked':''?>> Produk unggulan</label><label class="check"><input type="checkbox" name="active" <?=!isset($edit['active'])||$edit['active']?'checked':''?>> Tampilkan di toko</label><button class="btn dark">Simpan produk</button><?php if($edit):?><a class="btn light inline" href="products.php">Batal edit</a><?php endif;?></form></section>
<section class="panel"><h2>Daftar produk</h2><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php foreach($rows as $p):?><tr><td><div class="product-cell"><?php if($p['image']):?><img src="../<?=e($p['image'])?>" alt=""><?php endif;?><span><?=e($p['name'])?></span></div></td><td><?=rupiah($p['sale_price']?:$p['price'])?></td><td><?=$p['stock']?></td><td><?=$p['active']?'Aktif':'Disembunyikan'?></td><td><a href="?edit=<?=$p['id']?>">Edit</a><form method="post" class="inline-form" onsubmit="return confirm('Hapus produk ini?')"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$p['id']?>"><button class="text-danger">Hapus</button></form></td></tr><?php endforeach;?><?php if(!$rows):?><tr><td colspan="5">Belum ada produk.</td></tr><?php endif;?></tbody></table></div></section></main></body></html>
