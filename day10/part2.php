<?php

declare(strict_types=1);

namespace AOC\D10\P2;

class Grid{

    /**
     * @var string[][] $grid
     */
    private array $grid;

    /**
     * @param string[][] $grid
     **/
    public function __construct(array $grid){
        $this->grid = $grid;
    }

    /**
     * @return string[][]
     */
    public function getGrid(): array{
        return $this->grid;
    }

    public function __toString(): string{
        $str = '';
        foreach($this->grid as $row){
            $str .= "'". implode('', $row) . "'" . PHP_EOL;
        }
        return $str;
    }

    /**
     * Create a grid from a string
     * @param string $input
     * @return Grid
     */
    static function fromString(string $input): Grid{
        $grid = [];
        $lines = explode("\n", $input);
        foreach($lines as $line){
            if(empty($line)) continue;
            $grid[] = str_split(trim($line));
        }
        return new Grid($grid);
    }
}


class Point{
    public int $x;
    public int $y;

    public function __construct(int $x, int $y){
        $this->x = $x;
        $this->y = $y;
    }

    public function __toString(): string{
        return "($this->x, $this->y)";
    }
}

class PathFinder{
    /**
     * @var Grid $grid
     */
    private Grid $grid;
    /**
     * @var Point[] $startingPositions
     */
    private array $startingPositions;



    /**
     * PathFinder constructor.
     * @param Grid $grid
     */
    public function __construct(Grid $grid){
        $this->grid = $grid;
        $this->startingPositions = $this->findStartingPositions();
    }

    /**
     * Find all the starting positions (0) in the grid
     * @return Point[]
     */
    private function findStartingPositions(): array{
        $startingPositions = [];
        foreach($this->grid->getGrid() as $y => $row){
            foreach($row as $x => $cell){
                if($cell === '0'){
                    //echo "found starting position at ($x, $y)" . PHP_EOL;
                    $startingPositions[] = new Point($x, $y);
                }
            }
        }

        return $startingPositions;
    }


    /**
     * Find the number of paths from the starting position to the end (9)
     *
     * @return int
     */
    public function solve(): int{
        $ans = 0;
        foreach($this->startingPositions as $startingPosition){
            //echo "starting at $startingPosition" . PHP_EOL;
            echo "{$this->grid}" . PHP_EOL;
            $ans += $this->findPathFromPoint($startingPosition, $startingPosition);
        }
        return $ans;
    }



    /**
     * @param Point $startingPosition
     * @return int
     */
    private function findPathFromPoint(Point $origin, Point $startingPosition, int $ans = 0): int{
        //echo "finding path from $startingPosition, (ans: {$ans})" . PHP_EOL;
        /**
         * base cases
         * - if point is at the end (9), return 1
         * - if the point is out of bounds, return 0
         * - if point can't go forward, return 0
         **/

        if($this->grid->getGrid()[$startingPosition->y][$startingPosition->x] === '9'){
            return 1;
        }

        if($this->isPointOutOfBounds($startingPosition, $this->grid)){
            return 0;
        }

        /**
         * @var Point[] $validMoves
         */
        $validMoves = [];

        // check if point can go up
        $up = new Point($startingPosition->x, $startingPosition->y - 1);
        if($this->isValidMove($startingPosition, $up)){
            $validMoves[] = $up;
        }

        // check if point can go down
        $down = new Point($startingPosition->x, $startingPosition->y + 1);
        if($this->isValidMove($startingPosition, $down)){
            //echo "can move down $down" . PHP_EOL;
            $validMoves[] = $down;
        }

        // check if point can go left
        $left = new Point($startingPosition->x - 1, $startingPosition->y);
        if($this->isValidMove($startingPosition, $left)){
            //echo "can move left $left" . PHP_EOL;
            $validMoves[] = $left;
        }

        // check if point can go right
        $right = new Point($startingPosition->x + 1, $startingPosition->y);
        if($this->isValidMove($startingPosition, $right)){
            //echo "can move right $right" . PHP_EOL;
            $validMoves[] = $right;
        }

        if(count($validMoves) === 0){
            return 0;
        }

        if(count($validMoves) == 1){
            return $this->findPathFromPoint($origin, $validMoves[0], $ans);
        }

        echo "cur ans $ans" . PHP_EOL;
        echo "diverging paths at {$startingPosition}" . PHP_EOL;

        $ans = 0;
        // @TODO: diverging paths
        foreach($validMoves as $move){
            $ans += $this->findPathFromPoint($origin, $move, $ans);
        }

        return $ans;
    }

    private function isValidMove(Point $a, Point $b): bool{
        $grid = $this->grid->getGrid();
        //echo "checking if $a can move to $b" . PHP_EOL;
        if($this->isPointOutOfBounds($b, $this->grid)){
            //echo "$b out of bounds" . PHP_EOL;
            return false;
        }
        if($grid[$b->y][$b->x] === '.'){
            //echo "$b is a wall" . PHP_EOL;
            return false;
        }
        $gradientAtB = intval($grid[$b->y][$b->x]);
        $gradientAtA = intval($grid[$a->y][$a->x]);

        if($gradientAtB - $gradientAtA === 1){
            //echo "can move to $b" . PHP_EOL;
            return true;
        }

        //echo "can't move to $b - gradient is ". ($gradientAtB - $gradientAtA) . PHP_EOL;

        return false;
    }

    private function isPointOutOfBounds(Point $point, Grid $grid): bool{
        $x = $point->x;
        $y = $point->y;
        return $x < 0 || $x >= count($grid->getGrid()[0]) || $y < 0 || $y >= count($grid->getGrid());
    }
}


class Part2{
    static function run():void{
        $input = file_get_contents(__DIR__ . '/input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);

        $grid = Grid::fromString($input);
        $pathFinder = new PathFinder($grid);
        $ans = $pathFinder->solve();
        echo $grid . PHP_EOL;


        echo "Part 2: $ans" . PHP_EOL;
    }
}

Part2::run();
