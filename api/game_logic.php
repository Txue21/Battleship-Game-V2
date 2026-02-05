<?php
session_start();
require_once 'utils.php';

header('Content-Type: application/json');

// Get the raw POST data
$input = json_decode(file_get_contents('php://input'), true);
// Determine action from GET (for state/reset) or POST (for firing/placing)
$action = $_GET['action'] ?? ($input['action'] ?? null);

// Initialize session if it doesn't exist
if (!isset($_SESSION['battleship'])) {
    $_SESSION['battleship'] = initializeGame();
}

$response = ['success' => false, 'message' => 'Invalid Action'];

switch ($action) {
    case 'get_state':
        $response = [
            'success' => true, 
            'full_state' => getSanitizedState($_SESSION['battleship'])
        ];
        break;

    case 'fire_shot':
        if ($_SESSION['battleship']['game_state'] !== 'PLAYER_TURN') {
            $response['message'] = "It's not your turn!";
        } else {
            $x = $input['x'];
            $y = $input['y'];
            
            // 1. Process Player Shot
            $playerRes = processShot('computer', $x, $y);
            
            // 2. Process Computer Turn (if game didn't end)
            $cpuRes = null;
            if ($_SESSION['battleship']['game_state'] !== 'GAME_OVER') {
                $cpuRes = runComputerTurn();
            }

            $response = [
                'success' => true,
                'player_action' => $playerRes,
                'computer_action' => $cpuRes,
                'full_state' => getSanitizedState($_SESSION['battleship'])
            ];
        }
        break;

    case 'reset':
        session_destroy();
        session_start();
        $_SESSION['battleship'] = initializeGame();
        $response = ['success' => true];
        break;
}

echo json_encode($response);