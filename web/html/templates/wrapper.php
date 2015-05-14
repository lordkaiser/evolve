<!DOCTYPE html>
<html dir="ltr" lang="en-GB">
	<head>
		<title>ADAPT</title>
		<script src="https://evolvetest.cloudcontrolapp.com/assets/js/jquery.js"></script>
		<script src="https://evolvetest.cloudcontrolapp.com/assets/js/bootstrap.min.js"></script>
		<link href="https://evolvetest.cloudcontrolapp.com/assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://evolvetest.cloudcontrolapp.com/assets/css/adapt.css" rel="stylesheet">
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button class="navbar-toggle collapsed" aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" type="button">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="http://adapt.com/" style="color: white; font-size: 19pt;">
						<span class="mnl-true-blue" style="font-weight: bold;">ADAPT</span>
					</a>
				</div>
				<div id="navbar" class="collapse navbar-collapse pull-right">
					<ul class="nav navbar-nav">
						<li class="dropdown">
							<a class="dropdown-toggle" aria-expanded="false" role="button" data-toggle="dropdown" href="#">
							<span class="caret"></span>
							</a>
							<ul class="dropdown-menu" role="menu">
								<li>
									<a href="http://www.manylists.com/category">Category List</a>
								</li>
								<li>
									<a href="http://www.manylists.com/wishlist">Wishlist</a>
								</li>	
								<li class="divider"></li>
								<li>
									<a id="logout" class="mnl-clickable">Log out</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</nav>

		<div class="container" style="background-color: rgb(250, 250, 250); min-height: 627px;">
			<div style="clear: both; margin-top: 50px;">
				<div id="content">
					<?php echo $contents; ?>
				</div>
			</div>
		</div>

		<div style="min-height: 40px; width: 100%;">
			<div style="background-color: #292929; min-height: 40px; width: 100%;">
				<div class="container">
					<div class="a-footer-description" style="padding: 10px 0;">Copyright © 2015 adapt.com All rights reserved.</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			$('#searchbox').click( function() {
				if ($(this).val() == "search listing...") $(this).val('');
			});

			$('#searchbox').blur( function() {
				if ($(this).val().trim() == "") $(this).val('search listing...');
			});
		</script>
	</body>
</html>
