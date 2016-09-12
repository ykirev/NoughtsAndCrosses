<?php
	
	session_start();
	require 'NoughtsAndCrosses.php';
	
	function sendResponse($resp)
	{
		$resp = json_encode($resp);
		echo $resp;
		exit;
	}
	
	function getMoveMessage($player)
	{
		$result = ($player == 1) ? '#2 turn (Green nought)' : '#1 turn (Blue cross)';
		return 'Its player '.$result;
	}
	
	function getWinMessage($player)
	{
		if ($player != 0) {
			$result = ($player == 1) ? 'Player #1 (Blue cross) wins!' : 'Player #2 (Green nought) wins!';
		} else {
			$result = 'The game ended in a draw';
		}
		return $result;
	}
	
	$player = (int)$_POST['player'];
	$action = trim($_POST['action']);
	
	$response = array(
		'result' => 0,
		'message' => ''
	);
	
	if (empty($player) || empty($action)) {
		$response['message'] = 'Invalid data received.';
		sendResponse($response);
	}
	
	$nac = new NoughtsAndCrosses();
	
	switch ($action)
	{
		case 'new_game':
			$nac->endGame();
			$response['result'] = 1;
			break;
		
		case 'ai_move':
			$nac->setPlayer($player);
			$response['result'] = $nac->getAiMove();
			$nac->makeMove($response['result']);
			$response['win'] = $nac->checkEndGame();
			if ($response['win'] > -2) {
				$response['message'] = getWinMessage($response['win']);
			} else {
				$response['message'] = getMoveMessage($player);
			}
			break;
			
		case 'pl_move':
			$move = (int)$_POST['move'];
			if ($nac->makeMove($move)) {
				$response['result'] = $move;
				$response['win'] = $nac->checkEndGame();
				if ($response['win'] > -2) {
					$response['message'] = getWinMessage($response['win']);
				} else {
					$response['message'] = getMoveMessage($player);
				}
			} else {
				$response['result'] = 0;
				$response['message'] = 'This is not a valid move!';
			}
			break;
			
		default:
			$response['message'] = 'Invalid data received.';
		break;
	}

	sendResponse($response);
?>