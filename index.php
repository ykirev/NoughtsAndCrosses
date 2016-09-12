<?php
	/*
	*	Simple Noughts and Crosses example
	*/
?>
<html>
	<head>
		<title>Noughts and crosses</title>
		<link rel="stylesheet" type="text/css" href="style.css"/>
		<script src="http://code.jquery.com/jquery-3.1.0.min.js"></script>
		<script src="NoughtsAndCrosses.js"></script>
	</head>
	<body>
		<h1>A simple Noughts and crosses example</h1>
		<div class="canvasContainer">
			<canvas id="gameCanvas" width="302" height="302"></canvas>
		</div>
		<div id="gameMessage">Select game mode:</div>
		<div id="controlsContainer">
			<input id="btnStartPlayer" type="button" value="Player vs Computer" class="btn"/>
			<input id="btnStartAI" type="button" value="Computer vs Computer" class="btn ml10"/>
		</div>
		<script>		
			var nac = new NoughtsAndCrosses();
			
			$(function(){
				nac.drawCanvas();
			});
			
			$('#btnStartAI').click(function(){
				nac.run(0);
			});
			
			$('#btnStartPlayer').click(function(){
				nac.run(1);
			});
		</script>
		
	</body>
</html>