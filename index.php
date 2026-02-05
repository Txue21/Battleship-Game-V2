<?php
session_start();
// If the session doesn't exist yet, it will be initialized by the first JS call to get_state
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server-Side Battleship</title>
    <style>
        :root {
            --cell-size: 40px;
            --hit-color: #ff4d4d;
            --miss-color: #80c1ff;
            --ship-color: #444;
            --empty-color: #fff;
            --border-color: #ccc;
        }

        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; align-items: center; background-color: #f4f7f6; }
        
        #game-container { display: flex; gap: 50px; margin-top: 20px; flex-wrap: wrap; justify-content: center; }
        
        .board-wrapper { text-align: center; }
        
        .grid { 
            display: grid; 
            grid-template-columns: repeat(10, var(--cell-size)); 
            grid-template-rows: repeat(10, var(--cell-size)); 
            gap: 2px; 
            background-color: #999; 
            border: 2px solid #555;
            padding: 2px;
        }

        .cell { 
            width: var(--cell-size); 
            height: var(--cell-size); 
            background-color: var(--empty-color); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
            font-size: 0.8rem;
            transition: background 0.2s;
        }

        .cell:hover { filter: brightness(0.9); }

        /* Status Classes */
        .cell.ship { background-color: var(--ship-color); border-radius: 4px; }
        .cell.hit { background-color: var(--hit-color); color: white; }
        .cell.hit::after { content: '●'; }
        .cell.miss { background-color: var(--miss-color); }
        .cell.miss::after { content: '○'; }

        #status-area { margin: 20px; padding: 15px; border-radius: 8px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 80%; max-width: 800px; text-align: center; }
        
        .controls { margin-top: 20px; }
        button { padding: 10px 20px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

    <h1>Battleship: PHP vs JavaScript</h1>

    <div id="status-area">
        <h2 id="status-msg">Loading Game...</h2>
        <p id="instruction">Connect to your local server to start.</p>
    </div>

    <div id="game-container">
        <div class="board-wrapper">
            <h3>Your Fleet</h3>
            <div id="player-board" class="grid"></div>
        </div>

        <div class="board-wrapper">
            <h3>Enemy Waters</h3>
            <div id="computer-board" class="grid"></div>
        </div>
    </div>

    <div class="controls">
        <button onclick="resetGame()">Reset / New Game</button>
    </div>

    <script src="script.js"></script>
    
    <script>
        // Inline helper to handle a hard reset
        async function resetGame() {
            if(confirm("Are you sure you want to clear your progress?")) {
                await fetch('api/game_logic.php?action=reset');
                location.reload();
            }
        }
    </script>

</body>
</html>