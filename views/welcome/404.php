<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo $title; ?></title>
	<?php echo Asset::css('bootstrap.css'); ?>
	<style>
		#logo{
			width: 179px;
			height: 50px;
			position: relative;
			top: 15px;
		}
		header{
			background-color: #f75533;
			height: 75px;
			width: 100%;
			margin-bottom: 40px;
		}
		a{
			color: #883ced;
		}
		a:hover{
			color: #af4cf0;
		}
		.btn.primary{color:#ffffff!important;background-color:#883ced;background-repeat:repeat-x;background-image:-khtml-gradient(linear, left top, left bottom, from(#fd6ef7), to(#883ced));background-image:-moz-linear-gradient(top, #fd6ef7, #883ced);background-image:-ms-linear-gradient(top, #fd6ef7, #883ced);background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0%, #fd6ef7), color-stop(100%, #883ced));background-image:-webkit-linear-gradient(top, #fd6ef7, #883ced);background-image:-o-linear-gradient(top, #fd6ef7, #883ced);background-image:linear-gradient(top, #fd6ef7, #883ced);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fd6ef7', endColorstr='#883ced', GradientType=0);text-shadow:0 -1px 0 rgba(0, 0, 0, 0.25);border-color:#883ced #883ced #003f81;border-color:rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);}
		body { margin: 0px 0px 40px 0px; }
	</style>
</head>
<body>
	<header>
		<div class="container">
			<div id="logo"><?php echo Asset::img('logo.png', array('width'=>'178','height'=>'55','alt'=>'ロゴ')) ?></div>
		</div>
	</header>
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<!--
				<h1><?php echo $title; ?> <small>We can't find that!</small></h1>
				<hr>
				<p>The controller generating this page is found at <code>APPPATH/classes/controller/welcome.php</code>.</p>
				<p>This view is located at <code>APPPATH/views/welcome/404.php</code>.</p>
			-->
			<h1>このページは存在しません。</h1>
			</div>
		</div>
		<footer>
		</footer>
	</div>
</body>
</html>
