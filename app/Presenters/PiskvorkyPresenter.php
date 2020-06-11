<?php

/**TODO:
- Next / previous moves
*/
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use App\Presenters\PiskvorkyGameLogic;

class PiskvorkyPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context  */
    private $database;

    /**
     * @var \App\Presenters\PiskvorkyGameLogic
     */
    private $logic;


    /**
     * PiskvorkyPresenter constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        $this->logic = new PiskvorkyGameLogic($database);;
    }


    /**
     * @param string $gameId
     */
    public function renderGame(string $gameId): void{
        if($gameId == "NovaHra"){
            $gameId = -1;
        }

        $game = $this->database->table('game_info')->get(intval($gameId));
        if(!$game){
            $this->createGame();
        }
        else{
            $this->loadGame($gameId);
        }
    }

    /**
     * @brief renders default game page (New game / load game)
     */
    public function renderDefault(): void{
        return;
    }


    /**
     * @brief Renders new gamme page
     * @throws Nette\Application\BadRequestException
     */
    public function renderNewgame(): void{
        $this->createGame();

        $lastGame = $this->database->table('game_info')
            ->order('id DESC')
            ->limit(1);

        foreach($lastGame as $game){
            $gameId = $game->id;
        }

        $game = $this->database->table('game_info')->get($gameId);

        if (!$game) {
            $this->error('Game id not found');
        }

        $this->parseGame($game);
    }


    /**
     * @brief renders load game page
     */
    public function renderLoad(): void{
        $this->template->games = $this->database->table('game_info')
            ->order('id ASC');
    }


    /**
     * @brief renders settings page
     * @param string $gameId
     */
    public function renderSettings(string $gameId): void{
        $this->template->gameId = $gameId;
        $this->loadGame($gameId);
    }

    /**
     * @brief creates new game in database
     */
    public function createGame(): void{
        $date = new \DateTime();
        $date = $date->format('j. n. Y');

        $default_positions = "";

        for($i = 0; $i < 25; $i++) {
            $default_positions =$default_positions." -";
        }

        $this->database->table('game_info')->insert([
            'id' => null,
            'positions' => $default_positions,
            'creation_date' => $date,
            'board_size' => "5x5",
            'move' => "X",
            'has_ended' => 0
        ]);
    }


    /**
     * @brief loads game from database by given gameId
     * @param int $gameId
     */
    public function loadGame(int $gameId): void{
        $game = $this->database->table('game_info')->get($gameId);
        if (!$game) {
            //$this->error('Game id not found');
            $this->createGame();
        }
        else {
            $this->parseGame($game);
        }
    }


    /**
     * @brief parses loaded game to template
     * @param Nette\Database\Table\ActiveRow|null $game
     */
    private function parseGame(?Nette\Database\Table\ActiveRow $game)
    {
        $this->template->game = $game;
        $this->template->isfull = 0;
        $this->template->move = $game->move;
        $this->template->next_move = $this->getNextMove($game->move);
        $this->template->N = $this->getCols($game->board_size);
        $this->template->M = $this->getRows($game->board_size);
        $this->template->positions = $this->parsePositions($game->positions, $game->board_size);
        $this->template->game_status = $game->has_ended;
    }


    /**
     * @brief returns next move
     * @param string $type
     * @return string X or O
     */
    private function getNextMove(string $type)
    {
        if($type == "X") {
            return "O";
        }
        return "X"; //else return X
    }

    /**
     * @brief returns number of columns on game board
     * @param string $board_size
     * @return mixed|string
     */
    private function getCols(string $board_size)
    {
        $cols = explode("x", $board_size);
        return $cols[0];
    }


    /**
     * @brief returns number of rows on game board
     * @param string $board_size
     * @return mixed|string
     */
    private function getRows(string $board_size)
    {
        $rows = explode("x", $board_size);
        return $rows[1];
    }


    /**
     * @brief returns array of current game board positions
     * @param string $positions
     * @param string $board_size
     * @return mixed
     */
    private function parsePositions(string $positions, string $board_size){
        $table_size = explode("x", $board_size);
        $max_index = $table_size[0] * $table_size[1];

        $positions_array = explode(" ", $positions); //explodes string containing all positions to array of single ones

        for($i = 0; $i < $max_index; $i++){
            if(isset($positions_array[$i+1])){
                if($positions_array[$i+1] == "X" || $positions_array[$i+1] == "O"){
                    $parsed_positions[$i] = $positions_array[$i+1];
                }
                else{
                    $parsed_positions[$i] = "-";
                }
            }
            else{
                $parsed_positions[$i] = "-"; //If the board has been resized, automatically generate missing positions
            }
        }

        return $parsed_positions;
    }


    /**
     * @brief Handles players choice of his turn
     * @param $index
     * @throws Nette\Application\AbortException
     * @throws Nette\Application\BadRequestException
     */
    public function handlePlayButton($index){
        $gameId = $this->getParameter('gameId');
        $game = $this->database->table('game_info')->get($gameId);
        if (!$game) {
            $this->error('Game not found');
        }

        $positions = $this->parsePositions($game->positions, $game->board_size);
        $next_move = $this->getNextMove($game->move);

        $table_size = explode("x", $game->board_size);
        $max_index = $table_size[0] * $table_size[1];

        $formatted_positions = "";
        for($j = 0; $j < $max_index; $j++){
            if($j == $index){
                $formatted_positions = $formatted_positions." ".$game->move;
            }
            else {
                $formatted_positions = $formatted_positions." ".$positions[$j];
            }
        }

        $currentState = $this->logic->checkState($formatted_positions, $next_move, $table_size);
        $game_status = 0;

        if($currentState === 0) {
            $this->flashMessage('Tah uspesne odehran!');
        }
        else if($currentState === "X" || $currentState === "O"){
            $this->flashMessage("Hrac ".$currentState." vyhral!");
            $game_status = 1;
        }
        else{
            $this->flashMessage("Remiza!");
            $game_status = 1;
        }

        $this->database->table('game_info')
            ->where('id', $gameId)
            ->update([
                'positions' => $formatted_positions,
                'move' => $next_move,
                'has_ended' => $game_status
            ]);

        $this->redirect('this');
    }

    /**
     * @brief creates form for settings page
     * @return Form
     * @throws Nette\Application\BadRequestException
     */
    protected function createComponentSettingsForm(): Form
    {
        $gameId = $this->getParameter('gameId');
        $form = new Form;

        $game = $this->database->table('game_info')->get($gameId);
        if (!$game) {
            $this->error('Game'.$gameId.'not found');
        }

        $N = $this->getCols($game->board_size);
        $M = $this->getRows($game->board_size);

        $next_move = $game->move;
        $defval = 1;

        if($next_move == "X"){
            $defval = 0;
        }

        $form->addText('N', 'Pocet sloupcu: ')
            ->setDefaultValue($N)
            ->setRequired();


        $form->addText('M', 'Pocet radku: ')
            ->setDefaultValue($M)
            ->setRequired();

        $form->addRadioList('next_move', 'Aktualni hrac')
            ->setItems(array('X', 'O'))
            ->setDefaultValue($defval);

        $form->addSubmit('save', 'Ulozit nastaveni');

        $form->onSuccess[] = [$this, 'settingsFormSucceeded'];

        return $form;
    }

    /**
     * @brief on successfull submit saves changes to database
     * @param Form $form
     * @param \stdClass $values
     */
    public function settingsFormSucceeded(Form $form, \stdClass $values): void
    {
        $gameId = $this->getParameter('gameId');
        $next_move = "O";

        if($values->next_move == "0"){
            $next_move = "X";
        }

        $this->database->table('game_info')
            ->where('id', $gameId)
            ->update([
                'board_size' => $values->N."x".$values->M,
                'move' => $next_move
            ]);

    }

 }