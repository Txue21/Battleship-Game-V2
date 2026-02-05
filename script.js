/* script.js */
const API_URL = 'api/game_logic.php';

// Initialize the game when the page loads
document.addEventListener('DOMContentLoaded', () => {
    loadGameState();
});

/**
 * Fetch the current state from PHP session
 */
async function loadGameState() {
    try {
        const response = await fetch(`${API_URL}?action=get_state`);
        const data = await response.json();
        
        if (data.success) {
            updateUI(data.full_state);
        } else {
            document.getElementById('status-msg').innerText = "Ready to start?";
            document.getElementById('instruction').innerText = "Place your ships to begin.";
        }
    } catch (error) {
        console.error("Error loading game:", error);
    }
}

/**
 * Main UI Orchestrator
 */
function updateUI(gameState) {
    const playerBoard = document.getElementById('player-board');
    const computerBoard = document.getElementById('computer-board');

    // Render both grids
    renderGrid(playerBoard, gameState.players.human.grid, false);
    renderGrid(computerBoard, gameState.players.computer.grid, true);

    // Update Status Message
    const statusMsg = document.getElementById('status-msg');
    const instruction = document.getElementById('instruction');
    const state = gameState.game_state;

    statusMsg.innerText = state.replace('_', ' ');

    if (state === 'PLAYER_TURN') {
        instruction.innerText = "Click on the Enemy Waters to fire!";
        computerBoard.classList.add('my-turn');
    } else if (state === 'GAME_OVER') {
        instruction.innerText = `Winner: ${gameState.winner.toUpperCase()}!`;
        computerBoard.classList.remove('my-turn');
    } else if (state === 'SETUP') {
        instruction.innerText = "Deployment Phase: Place your fleet.";
    }
}

/**
 * Logic for drawing cells and adding click listeners
 */
function renderGrid(container, gridData, isOpponent) {
    container.innerHTML = '';
    
    gridData.forEach((row, y) => {
        row.forEach((cellStatus, x) => {
            const cell = document.createElement('div');
            cell.className = 'cell';
            
            // Map the server string to CSS classes
            if (cellStatus === 'HIT') cell.classList.add('hit');
            else if (cellStatus === 'MISS') cell.classList.add('miss');
            else if (cellStatus === 'SHIP') cell.classList.add('ship');

            // Only allow clicking on the opponent's grid if it's the player's turn
            if (isOpponent && cellStatus !== 'HIT' && cellStatus !== 'MISS') {
                cell.onclick = () => handlePlayerShot(x, y);
            }

            container.appendChild(cell);
        });
    });
}

/**
 * Handle user clicking a coordinate
 */
async function handlePlayerShot(x, y) {
    try {
        const response = await fetch(API_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'fire_shot', x, y })
        });

        const data = await response.json();
        if (data.success) {
            updateUI(data.full_state);
            
            // Log what happened in the console for debugging
            if (data.player_action.sunk) {
                console.log(`You sunk their ${data.player_action.sunk}!`);
            }
        } else {
            console.warn(data.message);
        }
    } catch (error) {
        console.error("Fetch error:", error);
    }
}