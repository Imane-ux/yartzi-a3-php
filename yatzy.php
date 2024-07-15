<?php
session_start();

// Initialize game state and leaderboard in the session if not already set
if (!isset($_SESSION['game_state'])) {
    $_SESSION['game_state'] = [
        'playerDice' => [],
        'playerScore' => [],
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
    $gameState['playerDice'] = $dice;
    $gameState['rollCount']++;

    /* Return updated game state as JSON response
    $response = $_SESSION['game_state'];
    echo json_encode($response);
    exit;*/
    $_SESSION['game_state'] = $gameState;
    
    // Return updated game state as JSON response
    echo json_encode($gameState);
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
