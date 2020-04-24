<?php
/* @var $this SiteController */
/* @var $model ContactForm */
/* @var $form CActiveForm */

$url = 'https://id.twitch.tv/oauth2/authorize';
$url .= '?client_id=06c0gb66eru1mxcecox3wpwfrf2g5e';
$url .= '&redirect_uri=' . Yii::app()->createAbsoluteUrl('site/token');
$url .= '&response_type=code';
$url .= '&scope=user_read';

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_POST, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
$response = curl_exec($curl);
?>

<div class="text-center container pt-5 mt-5">
    <h1 class="mb-5">KhaosGG</h1>
    <?= $response ?>
</div>

<script>
    $('a').addClass('btn btn-lg btn-primary').html('Login <i class="fab fa-twitch"></i>');
    
</script>