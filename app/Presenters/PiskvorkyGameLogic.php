<?php


namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class PiskvorkyGameLogic
{

    /**
     * @var Nette\Database\Context
     */
    private $database;


    /**
     * PiskvorkyGameLogic constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }


    /**
     * @param string $positions
     * @param string $next_move
     * @param array $table_size
     * @return string (0 || X || O || 1)
     */
    public function checkState(string $positions, string $next_move, array $table_size)
    {
        $state = $this->checkTable($positions, $table_size);

        if($state == 1){
            if($next_move == "X"){
                return "O";
            }
            return "X";
        }

        return $this->canContinue($positions, $table_size);
    }


    /**
     * @param array $board_size
     * @return mixed
     */
    private function getCols(array $board_size)
    {
        return $board_size[0];
    }


    /**
     * @param array $table_size
     * @return mixed
     */
    private function getRows(array $table_size)
    {
        return $table_size[1];
    }


    public function generateRegex(array $table_size, int $win_count){
        $N = $this->getCols($table_size);

        $regex = "/(X\s){".($win_count-1)."}X|(O\s){".($win_count-1)."}O| X((\s.){".$N."}\sX){".($win_count-1)."}|O((\s.){".$N."}\sO){".($win_count-1)."}|X((\s.){".($N-1)."}\sX){".($win_count-1)."}|O((\s.){".($N-1)."}\sO){".($win_count-1)."}|X((\s.){".($N-2)."}\sX){".($win_count-1)."}|O((\s.){".($N-2)."}\sO){".($win_count-1)."}/";

        return $regex;
    }


    /**
     * @param string $positions
     * @param array $table_size
     * @return int  (0 - continue, 1 - end)
     */
    public function checkTable(string $positions, array $table_size)
    {

        $regex = $this->generateRegex($table_size, 5);
        preg_match($regex, $positions, $match);

        if($match){
            return 1;
        }

        return 0;
    }


    private function canContinue(string $positions, array $table_size)
    {
        $match = "";
        preg_match('/-/', $positions, $match);

        if($match) {
            return 0;
        }
        else{
            return 1;
        }
    }


}