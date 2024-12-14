<?php

declare(strict_types=1);

namespace AOC\D11\P1;

class Grid
{
    /**
     * @var array<array<int|string>> $grid
     */
    private array $grid;

    /**
     * @param array<array<int|string>> $grid
     */
    public function __construct(array $grid)
    {
        $this->grid = $grid;
    }

    /**
     * @return array<array<int|string>> $grid
     */
    public function getGrid(): array
    {
        return $this->grid;
    }

    /**
     * @param array<array<int|string>> $grid
     */
    public function setGrid(array $grid): void
    {
        $this->grid = $grid;
    }

    public function getCell(int $r, int $c): string|int
    {
        if($r < 0 || $r >= count($this->grid) || $c < 0 || $c >= count($this->grid[0])){
            return "";
        }
        return $this->grid[$r][$c];
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
            if (empty(trim($line))) {
                continue;
            }
            $grid[] = str_split(trim($line));
        }
        return new Grid($grid);
    }
}

enum CellType {
    case CENTER;
    case CORNER;
    case VERTICAL_CORRIDOR;
    case HORIZONTAL_CORRIDOR;
    case STAND_ALONE;
    case SINGLE_EDGE;
    case PENINSULA;
}

function cellType(Grid $grid, int $r, int $c):CellType{
    $cell = $grid->getCell($r, $c);
    $upCell = $grid->getCell($r-1, $c);
    $leftCell = $grid->getCell($r, $c-1);
    $rightCell = $grid->getCell($r, $c+1);
    $downCell = $grid->getCell($r+1, $c);


    if($cell === $upCell &&
        $cell === $leftCell &&
        $cell === $rightCell &&
        $cell === $downCell){
        return CellType::CENTER;
    }else if($cell != $upCell && $cell != $leftCell && $cell == $downCell && $cell == $rightCell){ // top left corner

    /**
     * 1 1 1
     * 1 0 0
     * 1 0 0
     **/
        return CellType::CORNER;
    }else if($cell != $upCell && $cell != $rightCell && $cell == $downCell && $cell== $leftCell){ // top right corner
        /**
         * 1 1 1
         * 0 0 1
         * 0 0 1
         **/
        return CellType::CORNER;
    }else if($cell != $downCell && $cell != $leftCell && $cell == $upCell && $cell == $rightCell){ // bottom left corner
        /**
         * 1 0 0
         * 1 0 0
         * 1 1 1
         **/
        if($leftCell == $cell){
            return CellType::CENTER;
        }
        return CellType::CORNER;
    }else if($cell != $downCell && $cell !== $rightCell && $cell == $upCell && $cell == $leftCell){ // bottom right corner
        /**
         * 0 0 1
         * 0 0 1
         * 1 1 1
         **/
        if($rightCell == $cell){
            return CellType::CENTER;
        }
        return CellType::CORNER;
    }else if($cell == $upCell && $cell== $downCell){ // vertical coridor

        /**
         * ? 1 ?
         * 1 1 0
         * 0 1 0
         **/
        if($cell !== $leftCell && $cell !== $rightCell){
            return CellType::VERTICAL_CORRIDOR;
        }
        /**
         *   1 0 1
         **/
        return CellType::SINGLE_EDGE; // edge only
    }else if($cell == $leftCell && $cell== $rightCell){ // horizontal coridor

        /**  \/
         *  ? 0 1
         *  0 0 0
         *  ? 1 1
         **/
        if($cell != $upCell && $cell !== $downCell){
            return CellType::HORIZONTAL_CORRIDOR; // coridor
        }
        return CellType::SINGLE_EDGE; // edge only
    }else if($cell !== $upCell &&
        $cell !== $leftCell &&
        $cell !== $rightCell &&
        $cell !== $downCell){

        /**
         * 0 0 0
         * 0 1 0
         * 0 0 0
         **/
        return CellType::STAND_ALONE; // stand alone
    }else{
        return CellType::PENINSULA; // edge
    }
}



/**
 * @param array<array<int|string>> $grid
 * @param int $r
 * @param int $c
 * @param int $galaxyCount
 * @return array<array<int|string>> $grid
 */
function floodFill(array $grid, int $r, int $c, int $galaxyCount): array {
    $targ = $grid[$r][$c];
    if ($targ == $galaxyCount) return $grid;
    checkNeighbours($grid, $r, $c, $galaxyCount, $targ);
    return $grid;
}

/**
 * @param array<array<int|string>> $grid
 * @param int $r
 * @param int $c
 * @param int $galaxyCount
 */
function checkNeighbours(array &$grid, int $r, int $c, int $galaxyCount, string|int $targ): void {
    if (!isset($grid[$r][$c]) || $grid[$r][$c] != $targ){
        return;
    }

    $grid[$r][$c] = $galaxyCount;

    checkNeighbours($grid, $r - 1, $c, $galaxyCount, $targ);
    checkNeighbours($grid, $r + 1, $c, $galaxyCount, $targ);
    checkNeighbours($grid, $r, $c - 1, $galaxyCount, $targ);
    checkNeighbours($grid, $r, $c + 1, $galaxyCount, $targ);
}

function part1():void{
        $galaxies = [];
        $input = file_get_contents(__DIR__ . '/input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);
        $input = trim($input);

        $grid = Grid::fromString($input);


        $legend = [];
        $galaxyCount = 0;


        $oldGrid = $grid->getGrid();

        // preprocess grid
        for($r = 0; $r < count($oldGrid); $r++){
            for($c = 0; $c < count($oldGrid[0]); $c++){
                $cell = $oldGrid[$r][$c];

                if(!is_numeric($cell)){
                    echo "floodfilling {$oldGrid[$r][$c]} for {$galaxyCount} at $r, $c" . PHP_EOL;
                    $legend[$galaxyCount] = $cell;
                    $oldGrid = floodFill($oldGrid, $r, $c, $galaxyCount);
                    $galaxyCount++;
                }
            }
        }

        $newGrid = [];
        foreach($oldGrid as $row){
            $newGrid[] = $row;
        }


        if($newGrid){
            $grid->setGrid($newGrid);
        }

        $ans = 0;
        for($r = 0; $r < count($grid->getGrid()); $r++){
            for($c = 0; $c < count($grid->getGrid()[0]); $c++){

                $cellType = cellType($grid, $r, $c);

                if($cellType === CellType::STAND_ALONE){
                    $ans += 4;
                    continue;
                }

                switch($cellType){
                    case CellType::CENTER:
                        $fences = 0;
                        break;
                    case CellType::CORNER:
                        $fences = 2;
                        break;
                    case CellType::VERTICAL_CORRIDOR:
                        $fences = 2;
                        break;
                    case CellType::HORIZONTAL_CORRIDOR:
                        $fences = 2;
                        break;
                    case CellType::SINGLE_EDGE:
                        $fences = 1;
                        break;
                    case CellType::PENINSULA:
                        $fences = 3;
                        break;
                }

                $cell = $grid->getCell($r, $c);




                echo "cell: {$legend[$cell]}, ($r,$c) fences: $fences" . PHP_EOL;

               if(!isset($galaxies[$cell])){
                    $galaxies[$cell] = [
                        'area' => 1,
                        'key' => $legend[$cell],
                        'fences' => $fences,
                        'cells' => [[$r, $c]]
                    ];
                }else{
                    $galaxies[$cell]['area']++;
                    $galaxies[$cell]['fences'] += $fences;
                    $galaxies[$cell]['cells'][] = [$r, $c];
                }
            }
        }

        foreach($galaxies as $galaxy){
            echo "Galaxy {$galaxy['key']}\n\tArea:{$galaxy['area']}\n\tFence:{$galaxy['fences']} " . PHP_EOL;
            $ans += $galaxy['fences'] * $galaxy['area'];
        }

        echo "Part 1: $ans == 1930" . PHP_EOL;
    }

part1();
