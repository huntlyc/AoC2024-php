<?php

declare(strict_types=1);

namespace AOC\D15\P1;

enum Direction{
    case UP;
    case LEFT;
    case DOWN;
    case RIGHT;
}

class Grid
{
    /**
     * @var string[][] $grid
     */
    private array $grid;

    /**
     * @param string[][] $grid
     */
    public function __construct(array $grid)
    {
        $this->grid = $grid;
    }

    /**
     * @return string[][]
     */
    public function getGrid(): array
    {
        return $this->grid;
    }

    public function __toString(): string
    {
        $str = "";
        foreach ($this->grid as $row) {
            $str .= "'" . implode("", $row) . "'" . PHP_EOL;
        }
        return $str;
    }

    /**
     * Create a grid from a string
     * @param string $input
     * @return Grid
     */
    static function fromString(string $input): Grid
    {
        $grid = [];
        $lines = explode("\n", $input);
        foreach ($lines as $line) {
            if (empty($line)) {
                continue;
            }
            $grid[] = str_split(trim($line));
        }
        return new Grid($grid);
    }
}


class Vector{
    public int $x;
    public int $y;

    public function __construct(int $x, int $y){
        $this->x = $x;
        $this->y = $y;
    }

    static function ZERO():Vector{
        return new Vector(0, 0);
    }

    public function __toString():string{
        return "({$this->x}, {$this->y})";
    }
}

class Robot{
    private int $identifier;
    private int $x;
    private int $y;

    /**
     * @var Direction[] $instructions
     **/
    private array $instructions;
    private int $instructionIdx;


    /**
     * @param int $x
     * @param int $y
     * @param int $identifier
     * @param Direction[] $instructions
     */
    public function __construct(int $x, int $y, int $identifier, array $instructions){
        $this->x = $x;
        $this->y = $y;
        $this->identifier = $identifier;
        $this->instructions = $instructions;
        $this->instructionIdx = 0;
    }

    public function getID():int{
        return $this->identifier;
    }

    public function hasNextMove():bool{
        return $this->instructionIdx < count($this->instructions) - 1;
    }

    public function skipMove():void{
        $this->instructionIdx++;
    }

    public function moveTo(Vector $pos):void{
        $this->x = $pos->x;
        $this->y = $pos->y;
        $this->instructionIdx++;
    }

    /**
     * Get the position after the next move
     * @return array{Vector, Vector}
     */
    public function posAfterMove():array{
        if(empty($this->instructions)){
            throw new \Exception("No instructions");
        }

        $curPos = $this->getPos();
        $instruction = $this->instructions[$this->instructionIdx];
        $direction = Vector::ZERO();

        switch($instruction){
            case Direction::UP:
                $curPos->y--;
                $direction = new Vector(0, -1);
                break;
            case Direction::DOWN:
                $curPos->y++;
                $direction = new Vector(0, 1);
                break;
            case Direction::LEFT:
                $curPos->x--;
                $direction = new Vector(-1, 0);
                break;
            case Direction::RIGHT:
                $curPos->x++;
                $direction = new Vector(1, 0);
                break;
            default:
                throw new \Exception("Invalid instruction");
        }

        return [$curPos, $direction];
    }

    public function getPos():Vector{
        return new Vector($this->x, $this->y);
    }

    public function __toString():string{
        $str = "Robot {$this->identifier}\n  loc: ({$this->x}, {$this->y})" . PHP_EOL;
        if(!empty($this->instructions)){

            $strInstructons = array_map(function($i){
                switch($i){
                    case Direction::UP: return "^";
                    case Direction::DOWN: return "v";
                    case Direction::LEFT: return "<";
                    case Direction::RIGHT: return ">";
                    default:
                        var_dump($i);
                        throw new \Exception("Invalid instruction");
                }
            }, $this->instructions);

            // current instruction
            $str .= "  current instruction:  " . $strInstructons[$this->instructionIdx] . PHP_EOL;

            // instruction set
            $str .= "Instructions: " . PHP_EOL . implode(", ", $strInstructons);
        }
        return $str;
    }
}

class Box{
    private int $identifier;
    private int $x;
    private int $y;

    public function __construct(int $x, int $y, int $identifier){
        $this->x = $x;
        $this->y = $y;
        $this->identifier = $identifier;
    }

    public function getID():int{
        return $this->identifier;
    }

    public function getPos():Vector{
        return new Vector($this->x, $this->y);
    }

    public function setPos(Vector $pos):void{
        $this->x = $pos->x;
        $this->y = $pos->y;
    }


    public function __toString():string{
        return "Box {$this->identifier} loc:({$this->x}, {$this->y})";
    }
}

class WarehouseSimulator2024{
    /**
     * @var Box[]
     */
    private array $boxes;
    private Robot $robot;
    private Grid $grid;


    /**
     * @param Robot $robot
     * @param Grid $grid
     * @param Box[] $boxes
     */
    public function __construct(Grid $grid, Robot $robot, array $boxes){
        $this->robot = $robot;
        $this->grid = $grid;
        $this->boxes = $boxes;
    }

    public function getCell(Vector $pos):string|Box{
        $grid = $this->grid->getGrid();

        if($this->isWall($pos)){
            return '#';
        }

        foreach($this->boxes as $box){
            $boxPos = $box->getPos();
            if($boxPos->x === $pos->x && $boxPos->y === $pos->y){
                return $box;
            }
        }
        return $grid[$pos->y][$pos->x];
    }

    public function getBoxGPSSum():int{
        $this->runSimulation();

        $ans = 0;
        foreach($this->boxes as $box){
            $boxPos = $box->getPos();
            $ans += 100 * $boxPos->y + $boxPos->x;
        }
        return $ans;
    }

    private function isWall(Vector $pos):bool{
        $grid = $this->grid->getGrid();
        return $grid[$pos->y][$pos->x] === '#';
    }

    private function isBox(Vector $pos):bool|Box{
        foreach($this->boxes as $box){
            $boxPos = $box->getPos();
            if($boxPos->x === $pos->x && $boxPos->y === $pos->y){
                return $box;
            }
        }
        return false;
    }

    /**
     * Shunts a box in a given direction, will also shunt any boxes in the way
     * @param Box $box
     * @param Vector $dir
     * @return bool true if the box was successfully shunted
     */
    private function shuntBox(Box $box, Vector $dir):bool{
        $newBoxPos = new Vector($box->getPos()->x + $dir->x, $box->getPos()->y + $dir->y);


        if($this->isWall($newBoxPos)){
            //echo "Box {$box->getID()} at {$box->getPos()} cant move {$dir} to {$newBoxPos} because of wall" . PHP_EOL;
            return false;
        }

        // if box at location, shunt it
        $nextBox = $this->isBox($newBoxPos);
        if($nextBox instanceof Box){
            if(!$this->shuntBox($nextBox, $dir)){
                //echo "Box {$box->getID()} at {$box->getPos()} cant move {$dir} to {$newBoxPos}" . PHP_EOL;
                return false;
            }
        }

        // moving into free space
        //echo "Box {$box->getID()} at {$box->getPos()} will be moved {$dir} to {$newBoxPos}" . PHP_EOL;
        $box->setPos($newBoxPos);
        return true;
    }


    private function runSimulation():void{
        /*
        $maxMoves = 10000;
         */
        $moves = 0;

        while($this->robot->hasNextMove()){// && $moves < $maxMoves){

            if($this->isBox($this->robot->getPos())){
                throw new \Exception("!!! Robot is on a box !!!");
            }


            list($newRobotPos,$dir) = $this->robot->posAfterMove();

            if($this->isWall($newRobotPos)){
                //echo "Wall at {$newRobotPos}" . PHP_EOL;
                $this->robot->skipMove();
                $moves++;
                continue;
            }


            $box = $this->isBox($newRobotPos);
            if($box && $box instanceof Box){
                if($this->shuntBox($box, $dir)){
                    $moves++;
                    $this->robot->moveTo($newRobotPos);
                }else{
                    $moves++;
                    $this->robot->skipMove();
                }
            }else{ // free space
                $moves++;
                $this->robot->moveTo($newRobotPos);
            }

            //echo "After $moves moves" . PHP_EOL;
            //echo $this . PHP_EOL;
        }
    }


    public function __toString():string{
        $currentGrid = $this->grid->getGrid();

        // Place boxes on grid
        foreach($this->boxes as $box){
            $boxPos = $box->getPos();
            $currentGrid[$boxPos->y][$boxPos->x] = "O";
        }

        // Place robot on grid
        $robotPos = $this->robot->getPos();
        $currentGrid[$robotPos->y][$robotPos->x] = '@';

        $str = "";
        foreach($currentGrid as $row){
            $str .= implode("", $row) . PHP_EOL;
        }

        $str .= PHP_EOL;
        $str .= $this->robot . PHP_EOL;

        return $str;
    }
}

function parseInput(string $input):WarehouseSimulator2024{
    $lines = explode("\n", $input);
    $grid = null;
    $boxes = [];
    $robot = null;
    $map = [];
    $instructions = [];

    $parsingMap = true;

    $boxID = 0;
    $robotPos = new Vector(0, 0);

    foreach($lines as $y => $line){
        if(empty($line)){
            $parsingMap = false;
            continue;
        }

        if($parsingMap){
            $mapTiles = str_split($line);
            foreach($mapTiles as $x => $mapTile){
                if($mapTile === '@'){
                    $robotPos = new Vector($x, $y);
                    $mapTile = '.';
                }elseif($mapTile === 'O'){
                    $boxes[] = new Box($x, $y, $boxID++);
                    $mapTile = '.';
                }
                $map[$y][$x] = $mapTile;
            }
        }else{
            $rawInstruction = str_split($line);
            foreach($rawInstruction as $rawInstruction){
                switch($rawInstruction){
                    case '^': $instruction = Direction::UP; break;
                    case 'v': $instruction = Direction::DOWN; break;
                    case '>': $instruction = Direction::RIGHT; break;
                    case '<': $instruction = Direction::LEFT; break;
                    default:
                        throw new \Exception("Invalid instruction: {$rawInstruction}");
                }
                $instructions[] = $instruction;
            }
        }
    }

    $grid = new Grid($map);

    if(!empty($instructions)){
        $robot = new Robot($robotPos->x, $robotPos->y, 0, $instructions);
    }

    if(is_null($robot)){
        throw new \Exception("No robot found");
    }

    return new WarehouseSimulator2024($grid, $robot, $boxes);
}

function main():void{
    //$input = file_get_contents(__DIR__ . '/base-test-input.txt');
    //$input = file_get_contents(__DIR__ . '/test-input.txt');
    $input = file_get_contents(__DIR__ . '/input.txt');
    if($input === false) exit("Input file not found" . PHP_EOL);
    $input = trim($input);

    $runner = parseInput($input);
    $ans = $runner->getBoxGPSSum();

    echo "Part 1: {$ans}" . PHP_EOL;
}

main();
