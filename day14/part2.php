<?php

declare(strict_types=1);

namespace AOC\D14\P2;

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


    $startLine = 0;
    $i = 0;
    while(true){
        $i++;

        foreach($robots as $robot){
            $robot->move(1, $gridX, $gridY);
        }
        $currentGrid = $grid->getGrid();
        foreach($robots as $robot){
            $currentGrid[$robot->getPos()->y][$robot->getPos()->x] = 'X';
        }

        // if there are more than 10 robots in a straight line, we can stop
        foreach($currentGrid as $row){
            if(preg_match('/X{10,}/', implode('', $row))){
                break 2;
            }
            $startLine++;
        }
    }


    // print the grid
    echo PHP_EOL;
    echo PHP_EOL;
    echo " MERRY CHRISTMAS " . PHP_EOL;
    echo " =============== " . PHP_EOL;
    echo PHP_EOL;
    echo PHP_EOL;
    $currentGrid = $grid->getGrid();
    foreach($robots as $robot){
        $currentGrid[$robot->getPos()->y][$robot->getPos()->x] = 'X';
    }

    $r = 0;
    foreach($currentGrid as $row){
        $r++;
        if($r < 41 || $r > 75){
            continue;
        }
        echo implode('', array_slice($row, 43, 34)) . PHP_EOL;
    }
    echo PHP_EOL;


    /*
     * Appartnly, this is what we're after...
     *
............................................XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX..........................
.......................X....................X.............................X.............X............
............................................X.............................X..........................
...........................X................X.............................X..........................
.........X..................................X.............................X............X.............
............................................X..............X..............X........X.................
..X..X.....................X................X.............XXX.............X..........................
............................................X............XXXXX............X..............X...........
............................................X...........XXXXXXX...........X..........................
.....................X......................X..........XXXXXXXXX..........X..........................
..........X.................................X............XXXXX............X..........................
............................................X...........XXXXXXX...........X..........................
............................................X..........XXXXXXXXX..........X..........................
...................X........................X.........XXXXXXXXXXX.........X.......X..................
..........X.................................X........XXXXXXXXXXXXX........X......................X...
...............X............................X..........XXXXXXXXX..........X..........................
...........X...X............................X.........XXXXXXXXXXX.........X..........................
.........................X..................X........XXXXXXXXXXXXX........X.......................X..
............................................X.......XXXXXXXXXXXXXXX.......X..........................
............................................X......XXXXXXXXXXXXXXXXX......X..........................
............................................X........XXXXXXXXXXXXX........X..........................
..........X.................................X.......XXXXXXXXXXXXXXX.......X..........................
............................................X......XXXXXXXXXXXXXXXXX......X..........................
........................X........X..........X.....XXXXXXXXXXXXXXXXXXX.....X..........................
............................................X....XXXXXXXXXXXXXXXXXXXXX....X..........................
....X.......................................X.............XXX.............X..........................
..X.....................X...................X.............XXX.............X...................X......
...............X............................X.............XXX.............X..........................
.............X..............................X.............................X..........................
..............X...........................X.X.............................X..........................
............................................X.............................X................X.........
............................................X.............................X..........................
....................X.......................XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX..........................
**/




    echo "Part 2: {$i}" . PHP_EOL;
}

main();
