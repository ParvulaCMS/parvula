<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title></title>
		<link rel="stylesheet" href="//cdn.jsdelivr.net/highlight.js/9.3.0/styles/default.min.css">
		<link rel="stylesheet" href="//cdn.jsdelivr.net/highlight.js/9.3.0/styles/foundation.min.css">
		<script src="//cdn.jsdelivr.net/highlight.js/9.3.0/highlight.min.js"></script>

<style>

body {
	height: 100%;
	width: 100%;
}

.booboo-container {
	background: #fff;
	color: #333;
	height: 100%;
	width: 100%;
	position: fixed;
	margin: 0;
	left: 0;
	top: 0;
	z-index: 99999;
	font-size: 12pt;
}

.booboo-container * {
	box-sizing: border-box;
}

.booboo-container .left-panel, .booboo-container .main-panel {
	overflow: auto;
	height: 100%;
	font-family: "Helvetica Neue",Helvetica,Arial,sans-serif
}

.booboo-container .left-panel {
	width: 28%;
	float: left;
	border-right: 1px solid #B0BEC5;
}

.booboo-container .left-panel .item {
	padding: 0.5rem 1rem;
	display: block;
	border-bottom: 1px solid #CFD8DC;
	text-decoration: none;

	transition: all ease-in-out 0.1s;
}

.booboo-container .left-panel .item:hover {
	background-color: rgba(0,0,0,.025);
}

.booboo-container .left-panel .item-stack {
	cursor: pointer;
}

.booboo-container .left-panel .item-head {
	background: #03A9F4;
	padding: 1rem;
	color: #fff;
}

.booboo-container .left-panel .item .label {
	float: right;
	font-size: 10pt;
	color: #fff;
	background: #03A9F4;
	padding: 1px 6px;
	line-height: 1;
	border-radius: 10px;
}

.booboo-container .left-panel .item.active {
	border-left: 6px solid #03A9F4;
}

.booboo-container .left-panel .item h4 {
	font-size: 12pt;
	margin: 15px 0;
	color: #444;
	margin-top: 0;
	padding-top: 5px;
}
.booboo-container .left-panel .item p {
	margin: 10px 0;
	line-height: 1.2;
	font-size: 11pt;
	color: #666;
}
.booboo-container .left-panel .item .line-number {
	font-weight: bold;
}

.booboo-container .main-panel {
	overflow-y: auto;
	float: right;
	width: 72%;
	padding: 1rem 1.2rem;
}

.booboo-container .main-panel .system-info {
	margin-top: 2rem;
	margin-bottom: 4rem;
}

.booboo-container .main-panel .system-info h2 {
	border-bottom: 1px solid #CFD8DC;
}

.booboo-container .main-panel .system-info h3 {
	font-size: 12pt;
	margin-top: 20px;
	margin-bottom: 8px;
}

.booboo-container .main-panel .system-info table {
	font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
}

.booboo-container pre {
	white-space: pre-wrap;
	font-size: 11pt;
}
.booboo-container pre.highlight, .booboo-container code {
	font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
	font-size: 11pt;
	line-height: 1.3;
	font-size: 90%;
}

.booboo-container .error-detail h2 {
	color: #444;
}
.booboo-container .error-detail .filename {
	color: #555;
	display: block;
	margin-bottom: 20px;
}
.booboo-container .error-detail.hide {
	display: none;
}

.booboo-container table td {
	font-size: 10pt;
	min-width: 220px;
}
</style>
	</head>
	<body>

		<div class="booboo-container">

			<div class="left-panel">
				<div class="stack">
					<span class="item item-head">Stack frames</span>
					<a class="item item-stack active">
						<span class="label">0</span>
						<h4><?= $errorType ?></h4>
						<p><?= $filename ?>:<span class="line-number" data-frame-item-number="0"><?= $line ?></span></p>
					</a>
					<?php
					foreach ($frames as $k => $frame):
						list($tfunction, $tfilename, $tline) = $this->processFrame($frame);
					?>
					<a class="item item-stack">
						<span class="label"><?= $k + 1 ?></span>
						<h4><?= $tfunction ?></h4>
						<p><?= $tfilename ?>:<span class="line-number" data-frame-item-number="<?= $k + 1 ?>"><?= $tline ?></span></p>
					</a>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="main-panel">
				<div class="error-detail" data-frame-number="0">
					<h2>
						<?= $errorType ?>:<?= $message ?>
					</h2>
					<span class="filename"><?= $filename ?>:<?= $line ?></span>
					<pre class="highlight"><code class="php"><?= htmlspecialchars($code) ?></code></pre>
				</div>

				<?php
				foreach ($frames as $k => $frame):
					list($function, $filename, $line, $code) = $this->processFrame($frame);
				?>
				<div class="error-detail hide" data-frame-number="<?= $k + 1 ?>">
					<h2>
						<?= $function ?>
					</h2>
					<span class="filename"><?= $filename ?>:<?= $line ?></span>
					<pre class="highlight"><code class="php"><?= htmlspecialchars($code) ?></code></pre>
				</div>
				<?php endforeach; ?>

				<div class="system-info">
					<h2>System information</h2>

					<h3>$_GET</h3>
					<?= $this->printArray($_GET) ?>

					<h3>$_POST</h3>
					<?= $this->printArray($_POST) ?>

					<?php if (isset($_SESSION)) : ?>
						<h3>$_SESSION</h3>
						<?= $this->printArray($_SESSION) ?>
					<?php endif; ?>

					<h3>$_COOKIE</h3>
					<?= $this->printArray($_COOKIE) ?>

					<h3>$_SERVER</h3>
					<?= $this->printArray($_SERVER) ?>

					<h3>$_ENV</h3>
					<?= $this->printArray($_ENV) ?>
				</div>
			</div>

		</div>

		<script>
		var elsItem = document.querySelectorAll('.item-stack')
		var elsError = document.querySelectorAll('[data-frame-number]')

		function showErrorsCode(n) {
			for (var el in elsError) {
				if (elsError.hasOwnProperty(el)) {
					elsError[el].style.display = 'none'
				}
			}

			var el = elsError[n];
			if (el) {
				el.style.display = 'block'
			}
		}

		function removeClass(el, className) {
			if (el.classList) {
				el.classList.remove(className)
			} else {
				el.className = el.className.replace(new RegExp('(^|\\b)' + className.split(' ').join('|') + '(\\b|$)', 'gi'), ' ')
			}
		}

		function addClass(el, className) {
			if (el.classList) {
				el.classList.add(className)
			} else {
				el.className += ' ' + className
			}
		}

		function activeFrame(n) {
			for (var el in elsItem) {
				if (elsItem.hasOwnProperty(el)) {
					removeClass(elsItem[el], 'active')
				}
			}

			var el = elsItem[n];
			if (el) {
				addClass(el, 'active')
			}
		}

		for (var i = 0; i < elsItem.length; ++i) {
			elsItem[i].addEventListener('click', function (i) {
				showErrorsCode(i)
				activeFrame(i)
			}.bind(this, i))
		}

		hljs.initHighlightingOnLoad()
		</script>
	</body>
</html>
