<?php

declare(strict_types=1);

namespace AOC\D15\P2;

/**
* cls - Clear the screen
* taken from https://gist.github.com/icebreaker/4130200
*/
function cls():void{
    print("\033[2J\033[;H");
}

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

class Actor{
    protected Vector $pos;
    protected int $identifier;
    protected int $width;

    /**
     * @var Vector[] $boundingBox
     **/
    protected array $boundingBox;

    public function __construct(int $x, int $y, int $identifier, int $width = 1){
        $this->pos = new Vector($x, $y);
        $this->identifier = $identifier;
        $this->width = $width;
    }

    public function getID():int{
        return $this->identifier;
    }

    public function getPos():Vector{
        return $this->pos;
    }

    public function setPos(Vector $pos):void{
        $this->pos = $pos;
    }

    /**
     * @return Vector[]
     */
    public function getBoundingBox():array{
        return [
            new Vector($this->pos->x, $this->pos->y),
            new Vector($this->pos->x + ($this->width - 1), $this->pos->y),
        ];
    }

    public function collidesWith(Actor $actor):bool{
        $actorBoundingBox = $actor->getBoundingBox();

        foreach($this->getBoundingBox() as $pos){
            foreach($actorBoundingBox as $actorPos){
                if($actorPos->x === $pos->x && $actorPos->y === $pos->y){
                    return true;
                }
            }
        }

        return false;
    }

    public function isIntersecting(Vector $pos):bool{
        return in_array($pos, $this->boundingBox);
    }


    public function __toString():string{
        return "Actor {$this->identifier} loc:({$this->pos->x}, {$this->pos->y})";
    }
}

class Robot extends Actor{
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
        parent::__construct($x, $y, $identifier);

        $this->instructions = $instructions;
        $this->instructionIdx = 0;
    }


    public function hasNextMove():bool{
        return $this->instructionIdx < count($this->instructions);
    }

    public function skipMove():void{
        $this->instructionIdx++;
    }

    public function setPos(Vector $pos):void{
        $this->pos = $pos;
        $this->instructionIdx++;
    }

    public function tmpSetPos(Vector $pos):void{
        $this->pos = $pos;
    }




    /**
     * Get the position after the next move
     * @return array{Vector, Vector}
     */
    public function posAfterMove():array{
        if(empty($this->instructions)){
            throw new \Exception("No instructions");
        }

        $newPos = [];
        $curPos = $this->getPos();
        $instruction = $this->instructions[$this->instructionIdx];
        $direction = Vector::ZERO();

        switch($instruction){
            case Direction::UP:
                $newPos = new Vector($curPos->x, $curPos->y - 1);
                $direction = new Vector(0, -1);
                break;
            case Direction::DOWN:
                $newPos = new Vector($curPos->x, $curPos->y + 1);
                $direction = new Vector(0, 1);
                break;
            case Direction::LEFT:
                $newPos = new Vector($curPos->x - 1, $curPos->y);
                $direction = new Vector(-1, 0);
                break;
            case Direction::RIGHT:
                $newPos = new Vector($curPos->x + 1, $curPos->y);
                $direction = new Vector(1, 0);
                break;
            default:
                throw new \Exception("Invalid instruction");
        }

        return [$newPos, $direction];
    }


    public function __toString():string{
        $str = "Robot {$this->identifier}\n  loc: ({$this->pos->x}, {$this->pos->y})" . PHP_EOL;
        return $str;

        if(!empty($this->instructions)){

            $strInstructons = array_map(function($i){
                switch($i){
                    case Direction::UP: return "^";
                    case Direction::DOWN: return "v";
                    case Direction::LEFT: return "<";
                    case Direction::RIGHT: return ">";
                    default:
                        throw new \Exception("Invalid instruction");
                }
            }, $this->instructions);

            // current instruction
            if(isset($strInstructons[$this->instructionIdx])){
                $str .= "  current instruction:  " . $strInstructons[$this->instructionIdx] . PHP_EOL;
            }else{
                $str .= "  No instructions left" . PHP_EOL;
            }

            // instruction set
            $str .= "Instructions: " . PHP_EOL . implode(", ", $strInstructons);
        }
        return $str;
    }
}


class Box extends Actor{

    public function __construct(int $x, int $y, int $identifier){
        parent::__construct($x, $y, $identifier, $width = 2);
    }

    public function __toString():string{
        return "Box {$this->identifier} loc:({$this->pos->x}, {$this->pos->y})";
    }

    function __clone(){
        return new Box($this->pos->x, $this->pos->y, $this->identifier);
    }
}


class WarehouseSimulator2024{
    /**
     * @var Box[]
     */
    private array $boxes;
    private Robot $robot;
    private Grid $grid;

    private string $userInput;


    /**
     * @param Robot $robot
     * @param Grid $grid
     * @param Box[] $boxes
     */
    public function __construct(Grid $grid, Robot $robot, array $boxes){
        $this->robot = $robot;
        $this->grid = $grid;
        $this->boxes = $boxes;

        $this->userInput = '';
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

        echo $this . PHP_EOL;

        $gridHeight = count($this->grid->getGrid());
        $gridWidth = count($this->grid->getGrid()[0]);





        $ans = 0;
        foreach($this->boxes as $box){
            $boxPos = $box->getPos();

            /*
            // closest X edge
            $leftX = $boxPos->x;
            $rightX = $gridWidth - ($boxPos->x + 1);
            $closestX = min($leftX, $rightX);

            // closest Y edge
            $topY = $boxPos->y;
            $bottomY = $gridHeight - ($boxPos->y);
            $closestY = min($topY, $bottomY);
             */




            $ans += 100 * $boxPos->y + $boxPos->x;
        }
        return $ans;
    }

    private function isWall(Vector $pos):bool{
        $grid = $this->grid->getGrid();
        return $grid[$pos->y][$pos->x] === '#';
    }

    private function boxIntersectsWall(Box $box):bool{
        $boundingBox = $box->getBoundingBox();

        foreach($boundingBox as $pos){
            if($this->isWall($pos)){
                return true;
            }
        }
        return false;
    }

    private function isBox(Vector $pos, Actor $a):bool|Box|array{
        $boxes = [];
        foreach($this->boxes as $box){
            if($a !== $box && $box->collidesWith($a)){
                $boxes[] = $box;
            }
        }

        if(count($boxes) === 1){
            return $boxes[0];
        }else if(count($boxes) > 1){
            return $boxes;
        }

        return false;
    }

    /**
     * Shunts a box in a given direction, will also shunt any boxes in the way
     * @param Box $currentBox
     * @param Vector $dir
     * @param Box[] $shuntedBoxes
     * @return bool true if the box was successfully shunted
     */
    private function shuntBox(Box $currentBox, Vector $dir, $shuntedBoxes = []):bool{
        // echo $currentBox . $dir . PHP_EOL;
        $startPos = $currentBox->getPos();
        $newBoxPos = new Vector($startPos->x + $dir->x, $startPos->y + $dir->y);


        $currentBox->setPos($newBoxPos);
        if($this->boxIntersectsWall($currentBox)){
            // echo "Box {$currentBox->getID()} at {$currentBox->getPos()} cant move {$dir} to {$newBoxPos} because of wall" . PHP_EOL;
            $currentBox->setPos($startPos);
            return false;
        }

        // if box at location, shunt it
        $nextBox = false;
        $nextBoxes = $this->isBox($newBoxPos, $currentBox);
        if($nextBoxes){


        /**
         * Debug
         **/
            // echo "   !!!  DEBUG  !!!" . PHP_EOL;
            if(is_array($nextBoxes)){
                // echo "Multiple boxes at {$newBoxPos}" . PHP_EOL;
                foreach($nextBoxes as $box){
                    if($box instanceof Box){
                        // echo $box . PHP_EOL;
                    }
                }
                // echo '---' . PHP_EOL;
            }else if($nextBoxes instanceof Box){
                // echo "Box at {$newBoxPos}" . PHP_EOL;
                // echo $nextBoxes . PHP_EOL;
            }else{
                // echo "No box at {$newBoxPos}" . PHP_EOL;
            }

        /**
         * /Debug
         **/



            if(is_array($nextBoxes)){
                $canShuntAll = true;


                $boxStartingPositions = [];
                foreach($nextBoxes as $box){
                    if($box instanceof Box){
                        $boxStartingPositions[] = $box->getPos();
                    }
                }


                foreach($nextBoxes as $box){
                    if($box instanceof Box && !$this->shuntBox($box, $dir, [...$shuntedBoxes, $box])){
                        // echo "Box {$currentBox->getID()} at {$currentBox->getPos()} cant move {$dir} to {$newBoxPos}" . PHP_EOL;
                        $currentBox->setPos($startPos);
                        $canShuntAll = false;
                    }
                }

                if(!$canShuntAll){

                    // reset all boxes
                    foreach($nextBoxes as $idx => $box){
                        if($box instanceof Box){
                            $box->setPos($boxStartingPositions[$idx]);
                        }
                    }
                    $currentBox->setPos($startPos);

                    // echo "couldn't shunt all boxes, reset and returning" . PHP_EOL;
                    // var_dump($shuntedBoxes);
                    foreach($shuntedBoxes as $box){
                        // echo "Resetting box {$box->getID()} to {$box->getPos()}" . PHP_EOL;
                    }
                    return false;
                }
            }else if($nextBoxes instanceof Box){
                $nextBox = $nextBoxes;
                // echo "{$currentBox->getID()} at {$newBoxPos} " . ($nextBox ? " hits {$nextBox->getID()}" : " is free") . PHP_EOL;
                if(!$this->shuntBox($nextBox, $dir, [...$shuntedBoxes, $nextBox])){
                    // echo "Box {$currentBox->getID()} at {$currentBox->getPos()} cant move {$dir} to {$newBoxPos}" . PHP_EOL;
                    $currentBox->setPos($startPos);
                    return false;
                }
            }
        }



        // moving into free space
        // echo "Box {$currentBox->getID()} at {$currentBox->getPos()} will be moved {$dir} to {$newBoxPos}" . PHP_EOL;
        $currentBox->setPos($newBoxPos);
        return true;
    }

    function waitForInput(){

    $input = '';

    $read = [STDIN];
    $write = null;
    $except = null;

    readline_callback_handler_install('', function() {});

    // Read characters from the command line one at a time until there aren't any more to read
    do{
        $input .= fgetc(STDIN);
    } while(stream_select($read, $write, $except, 0, 1));

    readline_callback_handler_remove();

    return $input;

}



// Start the readline callback handler



    private function runSimulation():void{
        $moves = 0;
            cls();
           echo "Initial" . PHP_EOL;
           echo $this . PHP_EOL;
        // while(true){
        while($this->robot->hasNextMove()){


            $dir = new Vector(0, 0);


            /*

            $this->userInput = $this->waitForInput();
            switch($this->userInput){
                case 'h': $dir = new Vector(-1, 0); break;
                case 'j': $dir = new Vector(0, 1); break;
                case 'k': $dir = new Vector(0, -1); break;
                case 'l': $dir = new Vector(1, 0); break;
                case 'q':
                    exit("Quitting" . PHP_EOL);
            }
            $this->userInput = '';

            if($dir->x === 0 && $dir->y === 0){
                continue;
            }
             */


            if(true == ($box = $this->isBox($this->robot->getPos(), $this->robot))){
                // echo "Robot is on a box" . PHP_EOL;
                // echo "Robot: {$this->robot->getPos()} " . PHP_EOL;
                // echo "Box: {$box}" . PHP_EOL;

                exit("!!! Robot is on a box !!!");

                throw new \Exception("!!! Robot is on a box !!!");

            }




            $startPos = $this->robot->getPos();
            list($newRobotPos,$dir) = $this->robot->posAfterMove();
            // $newRobotPos = new Vector($startPos->x + $dir->x, $startPos->y + $dir->y);
            $this->robot->tmpSetPos($newRobotPos);

            // echo "C: {$this->robot->getPos()}" . PHP_EOL;
            // echo "N: {$newRobotPos}" . PHP_EOL;


            if($this->isWall($newRobotPos)){
                // echo "Wall at {$newRobotPos}" . PHP_EOL;
                $this->robot->setPos($startPos);
                $moves++;
                continue;
            }


            $boxes = $this->isBox($newRobotPos, $this->robot);

            // var_dump($boxes);
            if($boxes){
                if(is_array($boxes)){
                    throw new \Exception("Multiple boxes at {$newRobotPos}");
                }else if($boxes instanceof Box){
                    $box = $boxes;

                    $preShuntBoxes = array_map(function($box){
                        return clone $box;
                    }, $this->boxes);

                    $res = $this->shuntBox($box, $dir);

                    // var_dump($res);

                    if($res){
                        // echo "ROB: could shunt box {$box}" . PHP_EOL;
                        $moves++;
                        $this->robot->setPos($newRobotPos);
                    }else{
                         echo "ROB: couldnt shunt box {$box}" . PHP_EOL;
                        // $this->boxes = $preShuntBoxes;
                        $moves++;
                        $this->robot->setPos($startPos);

                    }
                }
            }else{ // free space
                // echo "ROB: free space at {$newRobotPos}" . PHP_EOL;
                $moves++;
                $this->robot->setPos($newRobotPos);
            }

            // echo "ROB: POS: {$this->robot->getPos()}" . PHP_EOL;




            cls();
            echo "After $moves moves" . PHP_EOL;
            echo $this . PHP_EOL;
            usleep(1000);

            /*
                file_put_contents(__DIR__ . '/mine/frame-' . $moves -1, $this);
            // wait for user to press space
            if($moves > 1090){

                $prompt = "Press 'q' to quit, or enter other key to continue\n";
                $res = readline($prompt);
                if($res === 'q'){
                    exit("Quitting" . PHP_EOL);
                }
            }
             */

        }
            cls();
            echo "After $moves moves" . PHP_EOL;
            echo $this . PHP_EOL;

            if($moves === 2000){
                exit;
            }
    }


    public function __toString():string{
        $currentGrid = $this->grid->getGrid();

        // Place boxes on grid
        foreach($this->boxes as $box){
            $boxPos = $box->getPos();
            $currentGrid[$boxPos->y][$boxPos->x] = "{$box->getID()}";
            $currentGrid[$boxPos->y][$boxPos->x+1] = "{$box->getID()}";
            /*
            $currentGrid[$boxPos->y][$boxPos->x] = "[";
            $currentGrid[$boxPos->y][$boxPos->x+1] = "]";
             */
        }

        // Place robot on grid
        $robotPos = $this->robot->getPos();
        // colour robot green
        $currentGrid[$robotPos->y][$robotPos->x] = "\033[32m@\033[0m";
        // $currentGrid[$robotPos->y][$robotPos->x] = "@";


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
            $x = 0;
            foreach($mapTiles as $mapTile){
                if($mapTile === '@'){
                    $robotPos = new Vector($x, $y);
                    $map[$y][] = '.';
                    $map[$y][] = '.';
                    $x += 2;
                }elseif($mapTile === 'O'){
                    $boxes[] = new Box($x, $y, $boxID++);
                    $map[$y][] = '.';
                    $map[$y][] = '.';
                    $x += 2;
                }else if($mapTile == '#'){
                    $map[$y][] = $mapTile;
                    $map[$y][] = $mapTile;
                    $x += 2;
                }else if($mapTile == '.'){
                    $map[$y][] = $mapTile;
                    $map[$y][] = $mapTile;
                    $x += 2;
                }
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


    /*
    foreach($boxes as $box){
        echo $box . PHP_EOL;
    }
     */
    return new WarehouseSimulator2024($grid, $robot, $boxes);
}

function main():void{
    $input = file_get_contents(__DIR__ . '/new-test.txt');
    //$input = file_get_contents(__DIR__ . '/test-input.txt');
    //$input = file_get_contents(__DIR__ . '/input.txt');
    if($input === false) exit("Input file not found" . PHP_EOL);
    $input = trim($input);

    $runner = parseInput($input);
    $ans = $runner->getBoxGPSSum();

    echo "Part 2: {$ans}" . PHP_EOL;
}

main();
