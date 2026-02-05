# Battleship V2+

## Major Iterations

### Iteration 1: Server-Controlled Persistent State
Moved game logic and state management from the client to the server using PHP Sessions. This ensures the "Source of Truth" lives on the server, allowing the game to survive browser refreshes without losing progress.

### Iteration 2: Smart AI (Hunt/Target Behavior)
Implemented an advanced computer opponent that uses a `target_queue` to remember hits. Once a player's ship is hit, the AI intelligently targets adjacent squares to "hunt" and sink the ship instead of firing randomly.

## Known Limitations

* **Automatic Deployment:** Both the player and computer fleets are automatically placed by the server at the start of the game. Manual ship placement is not currently available.
* **Single Player Only:** The application only supports one player against the computer AI.
* **Fixed Grid Size:** The game board is hard-coded to a standard 10x10 grid.