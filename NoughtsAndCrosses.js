function NoughtsAndCrosses(){
	
	this.playerTurn = 1;
	this.moveTimer = 0;
	this.boxSize = 100;
	this.speed = 1000;
	this.playerIsHuman = 0;
	this.playerCanMove = 0;
	this.ajaxEndpoint = 'ajax.php';
	
	var thisInstance = this;
	
	this.initGame = function(){
		this.playerTurn = 1;
		this.playerCanMove = 0;
		this.drawCanvas();
		$('#controlsContainer').hide();
	}
	
	/*
	*	cleans the canvas, and draws the grid
	*/
	this.drawCanvas = function(){
		gameCanvas = document.getElementById('gameCanvas');
		ctx = gameCanvas.getContext('2d');
		ctx.fillStyle="#FFFFFF";
		ctx.fillRect(0,0,301,301);
		ctx.beginPath();
		ctx.moveTo(100,0);
		ctx.lineTo(100,301);
		ctx.moveTo(201,0);
		ctx.lineTo(201,301);
		ctx.moveTo(0,100);
		ctx.lineTo(301,100);
		ctx.moveTo(0,201);
		ctx.lineTo(301,201);
		ctx.strokeStyle = "#000000";
		ctx.lineWidth = 1;
		ctx.stroke();
		ctx.closePath();
	}
	
	/*
	*	displays a message
	*/
	this.setMessage = function(message){
		$('#gameMessage').text(message);
	}
	
	/*
	*	draws the player's symbol (nought or cross)
	*/
	this.drawPlayerSymbol = function(box){
		var topX = ((box-1) % 3) * (this.boxSize + 1);
		var topY = Math.floor((box-1) / 3) * (this.boxSize + 1);
		gameCanvas = document.getElementById('gameCanvas');
		ctx = gameCanvas.getContext('2d');
		ctx.beginPath();
		if (this.playerTurn == 1) {
			ctx.moveTo(topX+10, topY+10);
			ctx.lineTo(topX+this.boxSize-10, topY+this.boxSize-10);
			ctx.moveTo(topX+this.boxSize-10, topY+10);
			ctx.lineTo(topX+10, topY+this.boxSize-10);
			ctx.strokeStyle = "#0000FF";
		} else {
			halfSize = Math.floor(this.boxSize / 2);
			ctx.arc(topX+halfSize, topY+halfSize, halfSize-10, 0, Math.PI*2, true);
			ctx.strokeStyle = "#00FF00";
		}
		ctx.lineWidth = 3;
		ctx.stroke();
		ctx.closePath();
	}
	
	/*
	*	Shows the Start button
	*/
	this.endGame = function(){
		$('#controlsContainer').show();
	}
	
	/*
	*	Ajax call for the next AI move
	*/
	this.nextMove = function(){
		$.post(this.ajaxEndpoint, {'player':this.playerTurn, 'action':'ai_move'}, function(data){
			if (data.result > 0) {
				// draw the player's symbol
				thisInstance.drawPlayerSymbol(data.result);
				// set the turn for the next player
				thisInstance.playerTurn = (thisInstance.playerTurn == 1) ? -1 : 1;
				// check if the game has ended
				if (data.win > -2) {
					thisInstance.endGame();
				} else {
					if (thisInstance.playerIsHuman == 1) {
						// let the human player take the next move
						thisInstance.playerCanMove = 1;
					} else {
						// set up the timer for the next AI move
						thisInstance.setMoveTimer();
					}
				}
			}
			thisInstance.setMessage(data.message);
		}, 'json');
	}
	
	/*
	*	Delay timer for the next move
	*/
	this.setMoveTimer = function(){
		this.moveTimer = window.setTimeout(function(){
			thisInstance.nextMove();
		}, thisInstance.speed);
	}
	
	/*
	*	responds to a player click on the canvas
	*/
	this.canvasClick = function(posX, posY){
		
		if (this.playerIsHuman == 0 || this.playerCanMove == 0) return false;
		
		// calculate the box number 
		var boxX = Math.floor(posX / 100) + 1;
		if (boxX > 3) boxX = 3;
		var boxY = Math.floor(posY / 100) + 1;
		if (boxY > 3) boxY = 3;
		selectedBox = boxX + (boxY - 1) * 3;
		
		// Ajax call, check if its a legal move
		$.post(this.ajaxEndpoint, {'player':1, 'action':'pl_move', 'move':selectedBox}, function(data){
			if (data.result > 0) {
				// draw the player's symbol
				thisInstance.drawPlayerSymbol(data.result);
				// flag; its computer's turn
				thisInstance.playerCanMove = 0;
				thisInstance.playerTurn = (thisInstance.playerTurn == 1) ? -1 : 1;
				// check if the game has ended
				if (data.win > -2) {
					thisInstance.endGame();
				} else {
					// set up the timer for the next AI move
					thisInstance.setMoveTimer();
				}
			}
			thisInstance.setMessage(data.message);
		}, 'json');
	}
	
	/*
	*	starts the game :)
	*	Ajax call to initialize the game
	*/
	this.startGame = function()
	{
		$.post(this.ajaxEndpoint, {'player':1, 'action':'new_game'}, function(data){
			if (data.result == 1) {
				if (thisInstance.playerIsHuman == 1) {
					thisInstance.playerCanMove = 1;
					thisInstance.setMessage('Make your move :)');
					$('#gameCanvas').click(function(e){
						var posX = e.pageX - $(this).offset().left;
						var posY = e.pageY - $(this).offset().top;
						thisInstance.canvasClick(posX, posY);
					});
				} else {
					thisInstance.setMoveTimer();
				}
			} else {
				thisInstance.setMessage(data.message);
			}
		}, 'json');
	}
	
	/*
	*
	*/
	this.run = function(mode){
		$('#gameCanvas').unbind('click');
		this.initGame();
		if (mode == 1) {
			this.playerIsHuman = 1;
		} else {
			this.playerIsHuman = 0;
		}
		this.startGame();
	}
}