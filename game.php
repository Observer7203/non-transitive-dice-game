<?php

require 'vendor/autoload.php';

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class DiceGame {
    private $dice;
    private $userDice;
    private $computerDice;

    public function __construct($args) {
        $this->validateDiceInput($args);
        $this->dice = $this->parseDice($args);
    }

    private function validateDiceInput($args) {
        if (count($args) < 3) {
            die("Error: At least 3 dice are required. Example: php game.php 2,2,4,4,9,9 6,8,1,1,8,6 7,5,3,7,5,3\n");
        }
        foreach ($args as $dice) {
            if (!preg_match('/^\d+(,\d+)*$/', $dice)) {
                die("Error: Invalid dice format. Each dice should be a comma-separated list of integers. Example: php game.php 2,2,4,4,9,9 6,8,1,1,8,6 7,5,3,7,5,3\n");
            }
            $values = array_map('intval', explode(',', $dice));
            if (count($values) !== 6) {
                die("Error: Each dice must have exactly 6 sides. Example: php game.php 2,2,4,4,9,9 6,8,1,1,8,6 7,5,3,7,5,3\n");
            }
        }
    }

    private function parseDice($args) {
        $dice = [];
        foreach ($args as $arg) {
            $dice[] = array_map('intval', explode(',', $arg));
        }
        return $dice;
    }

    public function startGame() {
        echo "Welcome to the Non-Transitive Dice Game!\n";
        $this->determineFirstMove();
    }

    private function determineFirstMove() {
        $key = bin2hex(random_bytes(32));
        $computerChoice = $this->generateFairRandomInt(0, 1);
        $hmac = hash_hmac('sha3-256', $computerChoice, $key);

        echo "Let's determine who makes the first move.\n";
        echo "I selected a random value in the range 0..1 (HMAC=$hmac).\n";
        echo "Try to guess my selection.\n";
        echo "0 - 0\n1 - 1\nX - exit\n? - help\nYour selection: ";

        $userChoice = $this->getUserInput(['0', '1', 'X', '?']);
        if ($userChoice === 'X') exit("Game exited.\n");
        if ($userChoice === '?') {
            $this->showHelp();
            return $this->determineFirstMove();
        }

        echo "My selection: $computerChoice (KEY=$key).\n";

        if ((int)$userChoice === $computerChoice) {
            echo "You make the first move!\n";
            $this->userTurn();
        } else {
            echo "I make the first move!\n";
            $this->computerTurn();
        }
    }

    private function userTurn() {
        $this->displayDiceOptions();
        echo "Choose your dice:\n";

        $userDiceIndex = $this->getValidDiceSelection();
        $this->userDice = $this->dice[$userDiceIndex];
        unset($this->dice[$userDiceIndex]);
        $this->dice = array_values($this->dice);

        $this->computerDice = $this->chooseComputerDice();
        echo "I chose the [" . implode(',', $this->computerDice) . "] dice.\n";

        $this->playRounds();
    }

    private function computerTurn() {
        $this->computerDice = $this->chooseComputerDice();
        echo "I chose the [" . implode(',', $this->computerDice) . "] dice.\n";

        $this->displayDiceOptions();
        echo "Choose your dice:\n";

        $userDiceIndex = $this->getValidDiceSelection();
        $this->userDice = $this->dice[$userDiceIndex];
        unset($this->dice[$userDiceIndex]);
        $this->dice = array_values($this->dice);

        $this->playRounds();
    }

    private function chooseComputerDice() {
        $selectedIndex = array_rand($this->dice);
        $selectedDice = $this->dice[$selectedIndex];
        unset($this->dice[$selectedIndex]);
        $this->dice = array_values($this->dice);
        return $selectedDice;
    }

    private function playRounds() {
        echo "\nIt's My turn.\n";
        $computerThrow = $this->makeThrow("Computer", $this->computerDice, $this->userDice);
    
        echo "\nIt's User's turn.\n";
        $userThrow = $this->makeThrow("User", $this->userDice, $this->userDice);
    
        echo "Comparing throws:\n";
        $this->compareThrows($userThrow, $computerThrow);
    }
    

    private function makeThrow($player, $dice, $humanDice) {
        $key = bin2hex(random_bytes(32));
        $index = $this->generateFairRandomInt(0, count($dice) - 1);
        $hmac = hash_hmac('sha3-256', $index, $key);

        echo "I selected a random value in the range 0..5 (HMAC=$hmac).\n";
        echo "Add your number modulo " . count($humanDice) . ".\n";

        foreach ($humanDice as $i => $value) {
            echo "$i - $value\n";
        }
        echo "X - exit\n? - help\nYour selection: ";

        $userChoice = $this->getUserInput(array_merge(range(0, count($humanDice) - 1), ['X', '?']));
        if ($userChoice === 'X') exit("Game exited.\n");
        if ($userChoice === '?') {
            $this->showHelp();
            return $this->makeThrow($player, $dice, $humanDice);
        }

        $userChoice = (int)$userChoice;
        echo "$player's number is " . $dice[$index] . " (KEY=$key).\n";

        $result = ($dice[$index] + $humanDice[$userChoice]) % count($humanDice);
        echo "The result is {$dice[$index]} + {$humanDice[$userChoice]} = {$result} (mod " . count($humanDice) . ").\n";
        return $result;
    }

    private function generateFairRandomInt($min, $max) {
        $range = $max - $min + 1;
        $limit = floor(PHP_INT_MAX / $range) * $range;
        do {
            $random = random_int(0, PHP_INT_MAX);
        } while ($random >= $limit);
        return $min + ($random % $range);
    }

    private function compareThrows($userResult, $computerResult) {
        echo "User's throw: $userResult\n";
        echo "My throw: $computerResult\n";

        if ($userResult > $computerResult) {
            echo "You win ($userResult > $computerResult)!\n";
        } elseif ($userResult < $computerResult) {
            echo "I win ($computerResult > $userResult)!\n";
        } else {
            echo "It's a draw ($computerResult = $userResult)!\n";
        }
    }

    private function displayDiceOptions() {
        echo "Available dice:\n";
        foreach ($this->dice as $index => $die) {
            echo "$index - [" . implode(',', $die) . "]\n";
        }
    }

    private function getValidDiceSelection() {
        $validOptions = array_keys($this->dice);
        $input = trim(fgets(STDIN));

        while (!in_array((int)$input, $validOptions)) {
            echo "Invalid input. Try again:\n";
            $input = trim(fgets(STDIN));
        }

        return (int)$input;
    }

    private function getUserInput($validOptions) {
        $input = trim(fgets(STDIN));
        while (!in_array($input, $validOptions)) {
            echo "Invalid input. Try again:\n";
            $input = trim(fgets(STDIN));
        }
        return $input;
    }

    private function showHelp() {
        echo "Help: Non-Transitive Dice Game.\n";
        echo "Each dice has exactly 6 sides. Each dice has different probabilities of winning against others.\n";
        $this->generateProbabilityTable();
    }

    private function generateProbabilityTable() {
        $output = new ConsoleOutput();
        $table = new Table($output);

        $header = ["User dice v"];
        foreach ($this->parseDice(array_slice($_SERVER['argv'], 1)) as $die) {
            $header[] = implode(",", $die);
        }
        $table->setHeaders($header);

        $originalDiceSet = $this->parseDice(array_slice($_SERVER['argv'], 1));
        foreach ($originalDiceSet as $userDice) {
            $row = [implode(",", $userDice)];
            foreach ($originalDiceSet as $opponentDice) {
                if ($userDice === $opponentDice) {
                    $row[] = "-";
                } else {
                    $probability = $this->calculateWinProbability($userDice, $opponentDice);
                    $row[] = number_format($probability, 4);
                }
            }
            $table->addRow($row);
        }

        echo "Probability of winning for all dice:\n";
        $table->render();
    }

    private function calculateWinProbability($userDice, $opponentDice) {
        $userWins = 0;
        $totalRounds = count($userDice) * count($opponentDice);

        foreach ($userDice as $userSide) {
            foreach ($opponentDice as $opponentSide) {
                if ($userSide > $opponentSide) {
                    $userWins++;
                }
            }
        }

        return $userWins / $totalRounds;
    }
}

$args = array_slice($argv, 1);
$game = new DiceGame($args);
$game->startGame();
