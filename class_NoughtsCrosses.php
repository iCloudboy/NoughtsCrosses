<?php
require_once("db/class_dbconn.php");

Class NoughtsCrosses {

    //variable declarations
    private $x_count = 0;
    private $o_count = 0;
    private $draw_count = 0;

    public function __construct()
    {
    }

    public function get_aggregate_results()
    {
        $db = DBConn::getConnection(); // connect to db

        //queries the database and returns how many of each condition happened over every game.
        $x_wins = $db->query('SELECT COUNT(*) FROM games WHERE winner = 1')->fetchColumn();
        $o_wins = $db->query('SELECT COUNT(*) FROM games WHERE winner = 2')->fetchColumn();
        $draws = $db->query('SELECT COUNT(*) FROM games WHERE winner = 0')->fetchColumn();

        echo "Results from all games: \n";
        echo "X wins: {$x_wins} \n";
        echo "O wins: {$o_wins} \n";
        echo "Draws: {$draws} ";
    }

    public function calculate_winners($input)
    {
        $db = DBConn::getConnection(); // connect to db

        while( (feof($input))!== true) { //loop until the end of the file is reached
            $currentrow = fgets($input);
            if ($currentrow[0] == 'X' || $currentrow[0] == 'O') { //check if the first character of the row is an X or an O
                $board[] = trim($currentrow); //add the current row to the board array

                $board = str_replace('\n', '', $board); //remove the new lines
            }
        }

        //variable declarations.
        $winner = 0;

        for ($x=0; $x<count($board); $x += 3){ //loop until the final row.
            $fullboard = $board[$x] . $board[$x+1] . $board[$x+2]; //concatenate 3 rows to make the full board.
            /******* CHECK FOR EACH POSSIBLE WIN CONDITION AND INSERT THE RESULTS INTO THE MYSQL DATABASE********/
            if (($board[$x][0] == 'X' && $board[$x][1] == 'X' && $board[$x][2] == 'X') ||
                ($board[$x+1][0] == 'X' && $board[$x+1][1] == 'X' && $board[$x+1][2] == 'X') ||
                ($board[$x+2][0] == 'X' && $board[$x+2][1] == 'X' && $board[$x+2][2] == 'X') ||
                ($board[$x][0] == 'X' && $board[$x+1][0] == 'X' && $board[$x+2][0] == 'X') ||
                ($board[$x][1] == 'X' && $board[$x+1][1] == 'X' && $board[$x+2][1] == 'X') ||
                ($board[$x][2] == 'X' && $board[$x+1][2] == 'X' && $board[$x+2][2] == 'X') ||
                ($board[$x][0] == 'X' && $board[$x+1][1] == 'X' && $board[$x+2][2] == 'X') ||
                ($board[$x][2] == 'X' && $board[$x+1][1] == 'X' && $board[$x+2][0] == 'X')
            ) {
                $this->x_count = $this->x_count + 1;
                $winner = 1; //x wins
                $sql_game_board = "INSERT INTO games(board, winner) VALUES (?,?)";
                $stmt = $db->prepare($sql_game_board);
                $stmt->bindParam(1, $fullboard, PDO::PARAM_STR);
                $stmt->bindParam(2, $winner, PDO::PARAM_INT);
                $stmt->execute();

            } else if (($board[$x][0] == 'O' && $board[$x][1] == 'O' && $board[$x][2] == 'O') ||
                ($board[$x+1][0] == 'O' && $board[$x+1][1] == 'O' && $board[$x+1][2] == 'O') ||
                ($board[$x+2][0] == 'O' && $board[$x+2][1] == 'O' && $board[$x+2][2] == 'O') ||
                ($board[$x][0] == 'O' && $board[$x+1][0] == 'O' && $board[$x+2][0] == 'O') ||
                ($board[$x][1] == 'O' && $board[$x+1][1] == 'O' && $board[$x+2][1] == 'O') ||
                ($board[$x][2] == 'O' && $board[$x+1][2] == 'O' && $board[$x+2][2] == 'O') ||
                ($board[$x][0] == 'O' && $board[$x+1][1] == 'O' && $board[$x+2][2] == 'O') ||
                ($board[$x][2] == 'O' && $board[$x+1][1] == 'O' && $board[$x+2][0] == 'O')
            ) {
                $this->o_count = $this->o_count + 1;
                $winner = 2; // O wins
                $sql_game_board = "INSERT INTO games(board, winner) VALUES (?,?)";
                $stmt = $db->prepare($sql_game_board);
                $stmt->bindParam(1, $fullboard, PDO::PARAM_STR);
                $stmt->bindParam(2, $winner, PDO::PARAM_INT);
                $stmt->execute();
            } else {
                $this->draw_count = $this->draw_count + 1;
                $sql_game_board = "INSERT INTO games(board, winner) VALUES (?,?)";
                $stmt = $db->prepare($sql_game_board);
                $stmt->bindParam(1, $fullboard, PDO::PARAM_STR);
                $stmt->bindParam(2, $winner, PDO::PARAM_INT);
                $stmt->execute();
            }
            /******* END OF CHECK  ********/
        }

    }

    public function get_results(){
        return "X Wins: " .  $this->x_count . "\nO Wins: " . $this->o_count . "\nDraws: " . $this->draw_count . "";
    }
}
