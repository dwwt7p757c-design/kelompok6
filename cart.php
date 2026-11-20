<?php
require_once __DIR__.'/config.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(($_POST['action']??'')==='add'){
  $id=(int)$_POST['product_id'];$size=trim($_POST['size']??'');
  $q=db()->prepare("SELECT id,stock FROM products WHERE id=? AND active=1");$q->execute([$id]);$p=$q->fetch();
  if($p){$key=$id.'_'.$size;$_SESSION['cart'][$key]=min(99,(int)($_SESSION['cart'][$key]??0)+1);}
  header('Location: cart.php');exit;
 }
 if(($_POST['action']??'')==='update'){foreach(($_POST['qty']??[]) as $k=>$v){$v=max(0,min(99,(int)$v));if($v===0)unset($_SESSION['cart'][$k]);else $_SESSION['cart'][$k]=$v;}header('Location: cart.php');exit;}
}
$items=[];$total=0;
foreach($_SESSION['cart']??[] as $key=>$qty){[$id,$size]=array_pad(explode('_',$key,2),2,'');$q=db()->prepare("SELECT * FROM products WHERE id=? AND active=1");$q->execute([(int)$id]);if($p=$q->fetch()){$price=(float)($p['sale_price']?:$p['price']);$items[]=['key'=>$key,'p'=>$p,'size'=>$size,'qty'=>$qty,'price'=>$price];$total+=$price*$qty;}}
?><!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"><title>Your Bag</title><link rel="stylesheet" href="assets/style.css"></head><body><header class="site-header"><a class="back-mark" href="index.php">←</a><a class="wordmark" href="index.php"><?=e(setting('store_name','ATELIER'))?></a><nav><a href="index.php">CONTINUE SHOPPING</a></nav></header><main class="cart-main"><p class="eyebrow">YOUR SELECTION</p><h1>Your bag <span class="muted">(<?=cart_count()?>)</span></h1><?php if(!$items):?><div class="empty-state"><h2>Your bag is empty.</h2><a class="btn dark inline" href="index.php">Explore collection</a></div><?php else:?><form method="post"><input type="hidden" name="action" value="update"><?php foreach($items as $it):?><article class="cart-item"><?php if($it['p']['image']):?><img src="<?=e($it['p']['image'])?>" alt=""><?php endif;?><div><h3><?=e($it['p']['name'])?></h3><p class="muted">Size: <?=e($it['size'])?></p><strong><?=rupiah($it['price'])?></strong><label class="qty-label">Qty <input type="number" min="0" max="99" name="qty[<?=e($it['key'])?>]" value="<?=$it['qty']?>"></label></div></article><?php endforeach;?><button class="btn light">Update bag</button></form><div class="cart-total"><p>Subtotal <strong><?=rupiah($total)?></strong></p><p class="muted small">Ongkir dihitung terpisah oleh penjual.</p><a class="btn dark full" href="checkout.php">PROCEED TO CHECKOUT</a></div><?php endif;?></main></body></html>
