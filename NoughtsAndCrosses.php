<?php
	
	class Board
	{
		/*
		*	representation of the playing board
		*	boxes claimed by player #1 have value of 1, by player #2 -1, and empty boxes - 0
		*/
		private $boxes = array();
		
		/*
		*	list of the game winning combinations
		*/
		private static $winCombos = array(
			array(1,2,3),
			array(4,5,6),
			array(7,8,9),
			array(1,4,7),
			array(2,5,8),
			array(3,6,9),
			array(1,5,9),
			array(3,5,7)
		);
		
		public function setBoxes($boxes)
		{
			$this->boxes = $boxes;
		}
		
		public function getBoxes()
		{
			return $this->boxes;
		}
		
		public function cleanBoard()
		{
			for ($i=1; $i<10; $i++) $this->boxes[$i] = 0;
		}
		
		public function load()
		{
			if (isset($_SESSION['board'])) {
				$this->setBoxes($_SESSION['board']);
			} else {
				$this->save();
			}
		}
		
		public function save()
		{
			$_SESSION['board'] = $this->getBoxes();
		}
		
		public function getBoxValue($index)
		{
			return $this->boxes[$index];
		}
		
		/*
		*	checks if any player placed 3 in a line
		*/
		public function checkWin()
		{
			foreach (self::$winCombos as $combo) {
				$lineTotal = 0;
				foreach ($combo as $boxIndex) {
					$lineTotal += $this->boxes[$boxIndex];
				}
				if ($lineTotal == 3) return 1;
				if ($lineTotal == -3) return -1;
			}
			return 0;
		}
		
		public function getMoveNo()
		{
			$move = 0;
			foreach ($this->boxes as $box) {
				if ($box != 0) $move++;
			}
			return $move;
		}
		
		public function getPossibleMoves()
		{
			$result = array();
			for ($i=1; $i<10; $i++) {
				if ($this->boxes[$i] == 0) $result[] = $i;
			}
			return $result;
		}
		
		public function setBox($box, $player)
		{
			$this->boxes[$box] = $player;
		}
		
	}
	
	class NoughtsAndCrosses
	{
		private $board;
		private $player = 1;
		
		public function __construct()
		{
			$this->board = new Board();
			$this->board->load();
		}
		
		public function setPlayer($player)
		{
			$this->player = $player;
		}
		
		/*
		*	check if a move is valid
		*/
		public function checkValidMove($box)
		{
			if ($box < 1 || $box > 9) return false;
			if ($this->board->getBoxValue($box) != 0) return false;
			return true;
		}
		
		/*
		*	mark a move on the board
		*/
		public function makeMove($box)
		{
			if (!$this->checkValidMove($box)) return false;
			$this->board->setBox($box, $this->player);
			$this->board->save();
			return true;
		}
		
		public function endGame()
		{
			$this->board->cleanBoard();
			$this->board->save();
		}
		
		/*
		*	returns +-1 if player 1/2 won, 0 if its a draw, -2 if the game is not concluded
		*/
		public function checkEndGame()
		{
			$endGame = $this->board->checkWin();
			if ($endGame == 0) {
				if ($this->board->getMoveNo() < 9) $endGame = -2;
			}
			return $endGame;
		}
		
		/*
		*	returns the next AI move
		*	uses random if its the very first move in the game, or MinMax algorithm otherwise 
		*/
		public function getAiMove()
		{
			if ($this->board->getMoveNo() == 0) {
				return mt_rand(1, 9);
			} else {
				return $this->getMinMaxMove();
			}
		}
		
		/*
		*	MinMax algorithm with alpha beta
		*/
		public function getMinMaxMove()
		{
			$testBoard = new Board();
			$testBoard->setBoxes($this->board->getBoxes());
			$move = $this->getMaxMove($this->player, $testBoard, -10, 10);
			return $move[0];
		}
		
		/*
		*	maximizer for the MinMax
		*/
		public function getMaxMove($player, $board, $alpha, $beta)
		{
			$result = array(0, -10);
			$possibleMoves = $board->getPossibleMoves();
			if (empty($possibleMoves)) {
				$result[1] = 0;
				return $result;
			}
			foreach ($possibleMoves as $pm) {
				$board->setBox($pm, $player);
				if ($board->checkWin() != 0) {
					$result[0] = $pm;
					$result[1] = 1;
				} else {
					$moveValue = $this->getMinMove(-1*$player, $board, $alpha, $beta);
					if ($moveValue[1] > $result[1]) {
						$result[0] = $pm;
						$result[1] = $moveValue[1];
					}
				}
				$board->setBox($pm, 0);
				if ($result[1] > $beta) {
					break;
				}
				$alpha = max($alpha, $result[1]);
			}
			return $result;
		}
		
		/*
		*	minimizer for the MinMax
		*/
		public function getMinMove($player, $board, $alpha, $beta)
		{
			$result = array(0, 10);
			$possibleMoves = $board->getPossibleMoves();
			if (empty($possibleMoves)) {
				$result[1] = 0;
				return $result;
			}
			foreach ($possibleMoves as $pm) {
				$board->setBox($pm, $player);
				if ($board->checkWin() != 0) {
					$result[0] = $pm;
					$result[1] = -1;
				} else {
					$moveValue = $this->getMaxMove(-1*$player, $board, $alpha, $beta);
					if ($moveValue[1] < $result[1]) {
						$result[0] = $pm;
						$result[1] = $moveValue[1];
					}
				}
				$board->setBox($pm, 0);
				if ($result[1] < $alpha) {
					break;
				}
				$beta = min($beta, $result[1]);
			}
			return $result;
		}

	}