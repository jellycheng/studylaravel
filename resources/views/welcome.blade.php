<html>
	<head>
		<title>Laravel</title>
		<style>
			body {
				margin: 0;
				padding: 0;
				width: 100%;
				height: 100%;
				color: #B0BEC5;
				display: table;
				font-weight: 100;
				font-family: 'Lato';
			}

			.container {
				text-align: center;
				display: table-cell;
				vertical-align: middle;
			}

			.content {
				text-align: center;
				display: inline-block;
			}

			.title {
				font-size: 96px;
				margin-bottom: 40px;
			}

			.quote {
				font-size: 24px;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="content">
				<div class="title">Laravel 5</div>
				<div class="quote">{{ Inspiring::quote() }}</div>
				<div class="quote">调用的是Illuminate\Foundation\Inspiring类的quote()方法,随机取一个单元值</div>
				<div class="quote">在config/app.php中配置了别名:'Inspiring' => 'Illuminate\Foundation\Inspiring'</div>
			</div>
		</div>
	</body>
</html>
