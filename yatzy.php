<?php
session_start();
$_SESSION = array();
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
    if (isset($_POST['playerDice'])) {
        $playerDice = json_decode($_POST['playerDice'], true); // Decode JSON string into array
        $gameState['playerDice'] = $playerDice; // Update playerDice state
    }

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
    error_log('Session data1: ' . print_r($_SESSION['game_state'], true));
    
    // Return updated game state as JSON response
    echo json_encode($gameState);
    exit;
}

if ($_POST['action'] === 'getRollCount') {
    // Return just the rollCount as plain text
    echo $_SESSION['game_state']['rollCount'];
    exit;
}
//not using this?
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

// API endpoint to calculate and update scores
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'calculateScores') {
    // Assume dice values are sent via POST
    //$dice = json_decode($_POST['dice'], true); //can also fetch them from here and reduce traffic!!
    $dice= $_SESSION['game_state']['playerDice'];
    error_log('Session data: ' . print_r($_SESSION['game_state'], true));
    

    // Defining all score calculation functions here
    function calculateOnes($dice) {
        $score = 0;
        foreach ($dice as $die) {
            if ($die == 1) {
                $score += 1;
            }
        }
        return $score;
    }
    function calculateTwos($dice) {
        $score = 0;
        foreach ($dice as $die) {
            if ($die === 2) {
                $score += 2;
            }
        }
        return $score;
    }
    function calculateThrees($dice) {
        $score = 0;
        foreach ($dice as $die) {
            if ($die === 3) {
                $score += 3;
            }
        }
        return $score;
    }
    function calculateFours($dice) {
        $score = 0;
        foreach ($dice as $die) {
            if ($die === 4) {
                $score += 4;
            }
        }
        return $score;
    }
    function calculateFives($dice) {
        $score = 0;
        foreach ($dice as $die) {
            if ($die === 5) {
                $score += 5;
            }
        }
        return $score;
    }
    function calculateSixes($dice) {
        $score = 0;
        foreach ($dice as $die) {
            if ($die === 6) {
                $score += 6;
            }
        }
        return $score;
    }
    function calculateChance($dice) {
        $score = array_sum($dice);
        return $score;
    }
    function calculateYatzy($dice) {
        if (count($dice) == 5) {$firstDie = $dice[0];}
        $score = 50;
        foreach ($dice as $die) {
            if ($die !== $firstDie) {
                $score = 0;
                break;
            }
        }
        return $score;
    }
    function calculateOnePair($dice) {
        $pairs = [];
        $score = 0;
        foreach ($dice as $i => $die) {
            $count = 1;
            foreach ($dice as $j => $otherDie) {
                if ($j !== $i && $die === $otherDie) {
                    $count++;
                }
            }
            if ($count >= 2 && !in_array($die, $pairs)) {
                $pairs[] = $die;
                $score = max($score, $die * 2); // Take the highest pair
            }
        }
        return $score;
    }
    function calculateTwoPair($dice) {
        $pairs = [];
        $score = 0;
        foreach ($dice as $i => $die) {
            $count = 1;
            foreach ($dice as $j => $otherDie) {
                if ($j !== $i && $die === $otherDie) {
                    $count++;
                }
            }
            if ($count >= 2 && !in_array($die, $pairs)) {
                $pairs[] = $die;
            }
        }
        if (count($pairs) >= 2) {
            $score = array_sum($pairs) * 2; // Sum of both pairs
        }
        return $score;
    }
    function calculateThreeOfAKind($dice) {
        $score = 0;
        foreach ($dice as $i => $die) {
            $count = 1;
            foreach ($dice as $j => $otherDie) {
                if ($j !== $i && $die === $otherDie) {
                    $count++;
                }
            }
            if ($count >= 3) {
                $score = array_sum($dice);
                break;
            }
        }
        return $score;
    }
    function calculateFourOfAKind($dice) {
        $score = 0;
        foreach ($dice as $i => $die) {
            $count = 1;
            foreach ($dice as $j => $otherDie) {
                if ($j !== $i && $die === $otherDie) {
                    $count++;
                }
            }
            if ($count >= 4) {
                $score = array_sum($dice);
                break;
            }
        }
        return $score;
    }
    function calculateFullHouse($dice) {
        $score = 0;
        $diceCopy = $dice;
        sort($diceCopy);
        if (count($diceCopy) == 5) {
            if (($diceCopy[0] === $diceCopy[1] && $diceCopy[1] === $diceCopy[2] && $diceCopy[3] === $diceCopy[4]) ||
                ($diceCopy[0] === $diceCopy[1] && $diceCopy[2] === $diceCopy[3] && $diceCopy[3] === $diceCopy[4])) {
                $score = 25;
            }
        }
        return $score;
    }
    function calculateSmallStraight($dice) {
        $score = 0;
        $diceCopy = array_unique($dice);
        sort($diceCopy);
        if (count($diceCopy) >= 5) {
            if (($diceCopy[1] === $diceCopy[0] + 1 &&
                    $diceCopy[2] === $diceCopy[1] + 1 &&
                    $diceCopy[3] === $diceCopy[2] + 1) ||
                ($diceCopy[2] === $diceCopy[1] + 1 &&
                    $diceCopy[3] === $diceCopy[2] + 1 &&
                    $diceCopy[4] === $diceCopy[3] + 1)) {
                $score = 30;
            }
        }
        return $score;
    }
    
    function calculateLargeStraight($dice) {
        $score = 0;
        $diceCopy = array_unique($dice);
        sort($diceCopy);
        if (count($diceCopy) >= 5) {
            if ($diceCopy[1] === $diceCopy[0] + 1 &&
                $diceCopy[2] === $diceCopy[1] + 1 &&
                $diceCopy[3] === $diceCopy[2] + 1 &&
                $diceCopy[4] === $diceCopy[3] + 1) {
                $score = 40;
            }
        }
        return $score;
    }


    // Initialize an array to store scores
    $scoreTable = [];

    // Calculate scores for each category
    $scoreTable['Ones'] = calculateOnes($dice);
    $scoreTable['Twos'] = calculateTwos($dice);
    $scoreTable['Threes'] = calculateThrees($dice);
    $scoreTable['Fours'] = calculateFours($dice);
    $scoreTable['Fives'] = calculateFives($dice);
    $scoreTable['Sixes'] = calculateSixes($dice);
    $scoreTable['OnePair'] = calculateOnePair($dice);
    $scoreTable['TwoPair'] = calculateTwoPair($dice);
    $scoreTable['ThreeOfKind'] = calculateThreeOfAKind($dice);
    $scoreTable['FourOfKind'] = calculateFourOfAKind($dice);
    $scoreTable['smallStraight'] = calculateSmallStraight($dice);
    $scoreTable['LargeStraight'] = calculateLargeStraight($dice);
    $scoreTable['FullHouse'] = calculateFullHouse($dice);
    $scoreTable['chance'] = calculateChance($dice);
    $scoreTable['yatzy'] = calculateYatzy($dice);

    // Update playerScore with the calculated scores in session
    $_SESSION['game_state']['playerScore'] = $scoreTable;
    //error_log('Score Table: ' . print_r($scoreTable, true));
    // Return updated game state as JSON response
    //header('Content-Type: application/json');
    echo json_encode($scoreTable);
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
