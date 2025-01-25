Non-Transitive Dice Game Welcome to the Non-Transitive Dice Game, a console-based implementation of a fascinating probabilistic game. The game is designed to showcase the properties of non-transitive dice while ensuring fairness in every step through cryptographically secure random number generation.
Features Non-Transitive Dice Mechanics: Players and the computer choose from a set of dice, where each die can beat another but may lose to a third.
Dynamic Dice Configuration: The game supports any number of dice (greater than or equal to 3) passed as command-line arguments. Each die must have exactly 6 sides with customizable values.
Fair Randomness: Every throw is proven fair using HMAC (SHA3-256) with a cryptographically secure random key. This ensures the computer cannot cheat, and results are verifiable.
Interactive Gameplay:
Determine the first player through a fair coin toss with verifiable randomness. Choose your dice from the remaining available options. Roll your dice and compete against the computer's throw. Help Menu with Probability Table: An ASCII-based table displays the probabilities of each die winning against others, helping you strategize your choice.
How It Works Dice Configuration: The game accepts dice as command-line arguments in the format:
php game.php 2,2,4,4,9,9 1,1,6,6,8,8 3,3,5,5,7,7 Each argument is a comma-separated list of integers representing the sides of a die.
Gameplay:
A coin toss determines who goes first. Players select their dice one at a time. The computer excludes already-chosen dice from the options. Each player rolls their dice, with fairness ensured by a secure random number generation protocol. Verifiable Fairness:
The computer generates a random number and an HMAC based on it. The HMAC is shown before the player makes their move, and the key is revealed afterward for verification. Winning Condition:
The higher throw wins. If throws are equal, the round is a draw.
Example Gameplay
php game.php 2,2,4,4,9,9 6,8,1,1,8,6 7,5,3,7,5,3
Output:
Welcome to the Non-Transitive Dice Game! Let's determine who makes the first move. I selected a random value in the range 0..1 (HMAC=XXXXX...). Try to guess my selection. 0 - 0 1 - 1 Your selection: 1 My selection: 1 (KEY=YYYYY...). You make the first move!
Available dice: 0 - [2,2,4,4,9,9] 1 - [6,8,1,1,8,6] 2 - [7,5,3,7,5,3] Choose your dice: 0
I chose the [6,8,1,1,8,6] dice.
It's time for my throw. I selected a random value in the range 0..5 (HMAC=ZZZZZ...). Add your number modulo 6. 0 - 0 1 - 1 2 - 2 3 - 3 4 - 4 5 - 5 Your selection: 3 ...
Requirements:
PHP >= 7.4 Symfony Console Component (composer require symfony/console)
Setup
Clone the repository: git clone  https://github.com/your-username/dice-game.git cd dice-game
Install dependencies: composer install
Run the game: php game.php 2,2,4,4,9,9 6,8,1,1,8,6 7,5,3,7,5,3
License This project is open-sourced under the MIT license.
![image](https://github.com/user-attachments/assets/4e01281e-ffc4-4272-8c0c-72fa656f6851)
