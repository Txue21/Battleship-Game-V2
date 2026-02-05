<?php

/**
 * Creates the initial empty game structure
 */
function initializeGame() {
    $ships = [
        ['name' => 'Carrier', 'size' => 5, 'hits' => 0, 'coords' => [], 'sunk' => false],
        ['name' => 'Battleship', 'size' => 4, 'hits' => 0, 'coords' => [], 'sunk' => false],
        ['name' => 'Submarine', 'size' => 3, 'hits' => 0, 'coords' => [], 'sunk' => false],
        ['name' => 'Patrol Boat', 'size' => 2, 'hits' => 0, 'coords' => [], 'sunk' => false],
    ];

    $state = [
        'game_state' => 'PLAYER_TURN', // Skipping SETUP for this version to keep it simple
        'winner' => null,
        'players' => [
            'human' => ['grid' => array_fill(0, 10, array_fill(0, 10, 'EMPTY')), 'ships' => $ships],
            'computer' => ['grid' => array_fill(0, 10, array_fill(0, 10, 'EMPTY')), 'ships' => $ships, 'target_queue' => []]
        ]
    ];

    // Randomly place computer ships for now
    foreach ($state['players']['computer']['ships'] as &$ship) {
        autoPlaceShip($state['players']['computer']['grid'], $ship);
    }
    
    // For this version, let's also auto-place player ships so you can play immediately
    foreach ($state['players']['human']['ships'] as &$ship) {
        autoPlaceShip($state['players']['human']['grid'], $ship);
    }

    return $state;
}

/**
 * Simple random placement for the computer (and auto-setup)
 */
function autoPlaceShip(&$grid, &$ship) {
    $placed = false;
    while (!$placed) {
        $orientation = rand(0, 1) ? 'H' : 'V';
        $x = rand(0, 9); $y = rand(0, 9);
        $coords = [];
        $valid = true;

        for ($i = 0; $i < $ship['size']; $i++) {
            $nx = ($orientation === 'H') ? $x + $i : $x;
            $ny = ($orientation === 'V') ? $y + $i : $y;
            if ($nx > 9 || $ny > 9 || $grid[$ny][$nx] !== 'EMPTY') { $valid = false; break; }
            $coords[] = ['x' => $nx, 'y' => $ny];
        }

        if ($valid) {
            foreach ($coords as $c) { $grid[$c['y']][$c['x']] = 'SHIP'; }
            $ship['coords'] = $coords;
            $placed = true;
        }
    }
}

/**
 * Process a shot against a specific player
 */
function processShot($targetKey, $x, $y) {
    $target = &$_SESSION['battleship']['players'][$targetKey];
    $cell = $target['grid'][$y][$x];
    $result = ['x' => $x, 'y' => $y, 'result' => 'MISS', 'sunk' => null];

    if ($cell === 'SHIP') {
        $target['grid'][$y][$x] = 'HIT';
        $result['result'] = 'HIT';
        foreach ($target['ships'] as &$ship) {
            foreach ($ship['coords'] as $c) {
                if ($c['x'] == $x && $c['y'] == $y) {
                    $ship['hits']++;
                    if ($ship['hits'] >= $ship['size']) {
                        $ship['sunk'] = true;
                        $result['sunk'] = $ship['name'];
                    }
                    break 2;
                }
            }
        }
        checkWin($targetKey);
    } else {
        $target['grid'][$y][$x] = 'MISS';
    }
    return $result;
}

/**
 * Hunt/Target AI Logic
 */
function runComputerTurn() {
    $cpu = &$_SESSION['battleship']['players']['computer'];
    $humanGrid = $_SESSION['battleship']['players']['human']['grid'];
    $tx = null; $ty = null;

    while (!empty($cpu['target_queue'])) {
        $coord = array_shift($cpu['target_queue']);
        if ($coord['x'] >= 0 && $coord['x'] < 10 && $coord['y'] >= 0 && $coord['y'] < 10) {
            $status = $humanGrid[$coord['y']][$coord['x']];
            if ($status !== 'HIT' && $status !== 'MISS') {
                $tx = $coord['x']; $ty = $coord['y']; break;
            }
        }
    }

    if ($tx === null) {
        do { $tx = rand(0, 9); $ty = rand(0, 9); } 
        while ($humanGrid[$ty][$tx] === 'HIT' || $humanGrid[$ty][$tx] === 'MISS');
    }

    $res = processShot('human', $tx, $ty);
    if ($res['result'] === 'HIT') {
        array_push($cpu['target_queue'], ['x'=>$tx+1, 'y'=>$ty], ['x'=>$tx-1, 'y'=>$ty], ['x'=>$tx, 'y'=>$ty+1], ['x'=>$tx, 'y'=>$ty-1]);
    }
    return $res;
}

function checkWin($targetKey) {
    $allSunk = true;
    foreach ($_SESSION['battleship']['players'][$targetKey]['ships'] as $s) {
        if (!$s['sunk']) $allSunk = false;
    }
    if ($allSunk) {
        $_SESSION['battleship']['game_state'] = 'GAME_OVER';
        $_SESSION['battleship']['winner'] = ($targetKey === 'computer') ? 'human' : 'computer';
    }
}

function getSanitizedState($state) {
    $clean = $state;
    foreach ($clean['players']['computer']['grid'] as $y => $row) {
        foreach ($row as $x => $val) {
            if ($val === 'SHIP') $clean['players']['computer']['grid'][$y][$x] = 'EMPTY';
        }
    }
    return $clean;
}