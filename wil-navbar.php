<!DOCTYPE html>
<html>
    <head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>WIL-Connect | Navbar</title>
		<style>
			* {
				margin: 0;
				padding: 0;
				box-sizing: border-box;
			}

			body {
				font-family: "Inter", sans-serif;
				color: #48557a;
				background: #f4f8fb;
				line-height: 1.6;
			}

			header {
				/*padding: 17px 48px;*/
				padding: 11px 38px 0px;
				font-family: "IBM Plex Sans", sans-serif;
				line-height: 1.5;
				position: sticky;
				top: 0;
				z-index: 20;
				background: #0b1f4d;
				display: flex;
			}
			
		    .head {
				display: flex;
				margin: 0;
				margin-bottom: 30px;
				
			}
			
			img {
				margin-top: -10px;
			}

			.slogan {
				font-size: 8px;
				color: #cfdcef;
				margin: 0;
				line-height: 1.1;
				
			}

			.system_name {
				margin: 0;
				color: #fff;
				line-height: 1.1;
				padding-top: 10px;
				font-size: 18px;
				font-weight: 700;
				letter-spacing: 0.5px;
			}

			.name h4 {
				margin: 16px;
				padding-left: 16px;
				padding-top: 10px;
			}

			.navbars {
				margin-left: 14em;
				margin-top: 6px;
			}

			nav {
				display: flex;
			}

			nav a {
				color: #ffffff;
				font-weight: 200;
				font-size: 16px;
				transition: color 0.2s ease, transform 0.2s ease;
				text-decoration: none;
				padding: 0 10px;
			}

			nav a:hover {
				color: #116ad8;
				transform: scale(1.2);
			}
			
			.log-btn {
				margin: 7px 0 0 13em;
			}


			.signin-btn {
				border: 1px solid #f5a623; /*#116ad8;*/
				padding: 6px 14px;
				font-size: 16px;
				border-radius: 30px;
				box-shadow: 0 2px 5px #f3b859;
				color: #fff;
			}
			
			.signin-btn:hover {
				background: #c27f14;
				border: 1px solid #c27f14;
				color: #ffffff;
			}

			.signin-btn:active {
				background: rgba(46, 139, 255, 0.35);
			}
		</style>
	</head>
	<body>
		<header>
			<div class="head">
				<img src="uploads/wil_logo.png" width="60" height="50"/>
				<div class="system-cont">
					<h3 class="system_name">WIL-Connect</h3>
					<p class="slogan">UNLOCKING OPPORTUNITIES</p>
				</div>
			</div>
						
			<nav>
				<div class="navbars">
				  <a href="#">Home</a>
				  <a href="#">About</a>
				  <a href="#">How it works</a>
				  <a href="#">Partners</a>
				  <a href="#">Jobs</a>
				</div>
				<div class="log-btn">
				  <a href="register.php" onclick="openUserType(); return false;" class="signin-btn">Get Started</a>
				</div>
			</nav>
		</header>
	</body>
</html>
