<?php
require_once __DIR__.'/config.php';
$items=[];$total=0;
foreach($_SESSION['cart']??[] as $key=>$qty){[$id,$size]=array_pad(explode('_',$key,2),2,'');$q=db()->prepare("SELECT * FROM products WHERE id=? AND active=1");$q->execute([(int)$id]);if($p=$q->fetch()){$price=(float)($p['sale_price']?:$p['price']);$items[]=['p'=>$p,'size'=>$size,'qty'=>$qty,'price'=>$price];$total+=$price*$qty;}}
if(!$items) {header('Location: cart.php');exit;}
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 $name=trim($_POST['name']??'');$email=trim($_POST['email']??'');$phone=trim($_POST['phone']??'');$address=trim($_POST['address']??'');
 if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||!$phone||!$address){$error='Lengkapi nama, email valid, nomor telepon, dan alamat.';}
 else {
  $pdo=db();$pdo->beginTransaction();
  try{$code='ORD'.date('ymd').strtoupper(bin2hex(random_bytes(3)));
   $pdo->prepare("INSERT INTO orders(order_code,customer_name,email,phone,address,notes,total) VALUES(?,?,?,?,?,?,?)")->execute([$code,$name,$email,$phone,$address,trim($_POST['notes']??''),$total]);$oid=(int)$pdo->lastInsertId();
   foreach($items as $it){$pdo->prepare("INSERT INTO order_items(order_id,product_id,product_name,size_name,quantity,price) VALUES(?,?,?,?,?,?)")->execute([$oid,$it['p']['id'],$it['p']['name'],$it['size'],$it['qty'],$it['price']]);}
   $pdo->commit();$_SESSION['cart']=[];$_SESSION['last_order']=$code;header('Location: order_success.php');exit;
  }catch(Throwable $e){$pdo->rollBack();$error='Pesanan belum berhasil disimpan. Coba lagi.';}
 }
}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title>Checkout</title><link rel="stylesheet" href="assets/style.css"></head><body><header class="site-header"><a class="back-mark" href="cart.php">←</a><a class="wordmark" href="index.php"><?=e(setting('store_name','ATELIER'))?></a><span>CHECKOUT</span></header><main class="checkout-layout"><section><p class="eyebrow">DELIVERY DETAILS</p><h1>Checkout</h1><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?><form method="post" class="checkout-form"><label>Nama lengkap<input name="name" required></label><label>Email<input type="email" name="email" required></label><label>Nomor WhatsApp<input name="phone" required></label><label>Alamat lengkap<textarea name="address" rows="4" required></textarea></label><label>Catatan (opsional)<textarea name="notes" rows="2"></textarea></label><button class="btn dark full">BUAT PESANAN</button></form></section><aside class="summary"><p class="eyebrow">ORDER SUMMARY</p><?php foreach($items as $it):?><p><?=e($it['p']['name'])?> · <?=e($it['size'])?> × <?=$it['qty']?> <strong><?=rupiah($it['price']*$it['qty'])?></strong></p><?php endforeach;?><hr><h2>Total <strong><?=rupiah($total)?></strong></h2><p class="muted small">Ini adalah pencatatan pesanan. Pembayaran online belum diaktifkan.</p></aside></main></body></html>
