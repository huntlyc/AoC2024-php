<?php

declare(strict_types=1);

namespace AOC\D14\P1;

class Grid {
    /**
     * @var string[][]
     */
    private array $grid = [];

    public function __construct(int $sizeY, int  $sizeX) {
        $this->grid = array_fill(0, $sizeY, array_fill(0, $sizeX, '.'));
    }

    /**
     * @return string[][]
     */
    public function getGrid(): array {
        return $this->grid;
    }

    public function __toString():string{
        $str = '';
        foreach($this->grid as $row) {
            $str .= implode('', $row) . PHP_EOL;
        }
        return $str;
    }
}


class Vector{
    public int $x;
    public int $y;

    public function __construct(int $x, int $y){
        $this->x = $x;
        $this->y = $y;
    }

    public function __toString():string{
        return "({$this->x}, {$this->y})";
    }
}

class Robot{
    private int $identifier;
    private int $x;
    private int $y;
    private Vector $direction;

    public function __construct(int $x, int $y, Vector $direction, int $identifier){
        $this->x = $x;
        $this->y = $y;
        $this->direction = $direction;
        $this->identifier = $identifier;
    }

    public function getID():int{
        return $this->identifier;
    }

    public function move(int $distance, int $xMax, int $yMax):void{

        $newX = (($this->x + ($this->direction->x * $distance)) % $xMax);
        if($this->direction->x < 0 && $newX < 0){
            $this->x = $newX + $xMax;
        }else{
            $this->x = $newX;
        }

        $newY = (($this->y + ($this->direction->y * $distance)) % $yMax);
        if($this->direction->y < 0 && $newY < 0){
            $this->y = $newY + $yMax;
        }else{
            $this->y = $newY;
        }
    }

    public function getPos():Vector{
        return new Vector($this->x, $this->y);
    }


    public function __toString():string{
        return "Robot {$this->identifier} loc:({$this->x}, {$this->y}) vel:{$this->direction}";
    }
}

function main():void{
    $input = file_get_contents(__DIR__ . '/input.txt');
    if($input === false) exit("Input file not found" . PHP_EOL);
    $input = trim($input);

    $gridX = 101;
    $gridY = 103;

    $grid = new Grid($gridY, $gridX);
    $robots = [];
    $robotID = 0;
    foreach(explode(PHP_EOL, $input) as $line){
        if(preg_match('/p=(\d+),(\d+)\sv=(-?\d+),(-?\d+)/', $line, $matches)){
            $x = (int) $matches[1];
            $y = (int) $matches[2];
            $vx = (int) $matches[3];
            $vy = (int) $matches[4];
            $robots[] = new Robot($x, $y, new Vector($vx, $vy), ++$robotID);
        }
    }


    echo "Initial state" . PHP_EOL;
    $currentGrid = $grid->getGrid();
    foreach($robots as $robot){
        $currentGrid[$robot->getPos()->y][$robot->getPos()->x] = (string) $robot->getID();
    }

    foreach($currentGrid as $row){
        echo implode('', $row) . PHP_EOL;
    }
    echo PHP_EOL;

    $moves = 100;
    for($i = 0; $i < $moves; $i++){
        foreach($robots as $robot){
            $robot->move(1, $gridX, $gridY);
        }
    }

    $currentGrid = $grid->getGrid();

    foreach($robots as $robot){
        $currentGrid[$robot->getPos()->y][$robot->getPos()->x] = 'X';
    }
    echo "After move {$i}" . PHP_EOL;
    foreach($currentGrid as $row){
        echo implode('', $row) . PHP_EOL;
    }
    echo PHP_EOL;

    // work out quadrents
    $xQuad1 = (int) floor($gridX / 2);
    $xQuad2 = (int) ceil($gridX / 2);
    $yQuad1 = (int) floor($gridY / 2);
    $yQuad2 = (int) ceil($gridY / 2);

    $qr = [0, 0, 0, 0];
    foreach($robots as $robot){
        $pos = $robot->getPos();
        if($pos->x < $xQuad1 && $pos->y < $yQuad1){
            $qr[0]++;
        }elseif($pos->x >= $xQuad2 && $pos->y < $yQuad1){
            $qr[1]++;
        }elseif($pos->x < $xQuad1 && $pos->y >= $yQuad2){
            $qr[2]++;
        }elseif($pos->x >= $xQuad2 && $pos->y >= $yQuad2){
            $qr[3]++;
        }
    }


    $ans = $qr[0] * $qr[1] * $qr[2] * $qr[3];



    echo "Part 1: {$ans}" . PHP_EOL;
}

main();
