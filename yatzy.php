<?php
session_start();
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

//echo '<p>Current server time: ' . date('Y-m-d H:i:s') . '</p>';
// Initialize game state and leaderboard in the session if not already set
if (!isset($_SESSION['game_state'])) {
    $_SESSION['game_state'] = [
        'playerDice' => [],//dice kept by player, i.e active
        'randomDice' => [],
        'playerScore' => [], //score for all rows?
        'rollCount' => 0,
        'numFilledRowScore' => 0
    ];
}

if (!isset($_SESSION['leaderboard'])) {
    $_SESSION['leaderboard'] = [
        ['player' => 'Player 1', 'score' => 0],
       // ['player' => 'Player 2', 'score' => 0],
        // Add more players as needed
    ];
}

// API endpoint to roll the dice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'rollDice') {
    
    // Simulate rolling the dice and updating game state
    $gameState = $_SESSION['game_state'];
    $dice = [];
    for ($i = 0; $i < 5; $i++) {
        $dice[] = rand(1, 6);
    }
    
    // Update playerDice, rollCount, etc. in $_SESSION['game_state']
    $gameState['randomDice'] = $dice;
    //echo '<p>Current roll Count: ' . htmlspecialchars($gameState['rollCount']) . '</p>';
    $gameState['rollCount']++;
    //echo '<p>Current roll Count2: ' . htmlspecialchars($gameState['rollCount']) . '</p>';
    /* Return updated game state as JSON response
    $response = $_SESSION['game_state'];
    echo json_encode($response);
    exit;*/
    $_SESSION['game_state'] = $gameState;
    
    // Return updated game state as JSON response
    echo json_encode($gameState);
    exit;
}
if ($_POST['action'] === 'updatePlayerDice') {
    // Update playerDice based on data received from JavaScript
    $randomDice = $_POST['randomDice']; // Assuming randomDice is sent as an array
    
    $gameState = $_SESSION['game_state']; //does this create a new one?
    $gameState['playerDice'] = $randomDice;
    $gameState['rollCount'] = $_SESSION['game_state']['rollCount'];
    
    $_SESSION['game_state'] = $gameState;
    
    // Return updated game state as JSON response
    echo json_encode($gameState);
    exit;
}

if ($_POST['action'] === 'getRollCount') {
    // Return just the rollCount as plain text
    echo $_SESSION['game_state']['rollCount'];
    exit;
}

// API endpoint to enter a score for a specific category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enterScore') {
    // Handle entering the score for a specific category
    // Update playerScore, check for filled rows, calculate totals, etc. in $_SESSION['game_state']
    // Return updated game state as JSON response
    
    // Handle entering the score for a specific category
    $gameState = $_SESSION['game_state'];
    $row = $_POST['row']; // Assuming row is passed as POST data
    $score = $_POST['score']; // Assuming score is passed as POST data
    
    // Update playerScore and calculate totals, etc.
    $gameState['playerScore'][$row] = $score;
    $gameState['numFilledRowScore']++;

    // Update game state
    $_SESSION['game_state'] = $gameState;
    
    // Return updated game state as JSON response
    echo json_encode($gameState);
    exit;
}

// API endpoint to fetch the leaderboard
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getLeaderboard') {
    // Return the leaderboard data as JSON response 
    $response = $_SESSION['leaderboard']; //reviewhow score should be updated
    echo json_encode($response);
    exit;
}

// You can add more API endpoints as needed for game state management and leaderboard functionality

// Handle other requests or serve static files
?>
