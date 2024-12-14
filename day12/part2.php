<?php

declare(strict_types=1);

namespace AOC\D11\P2;

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

    /**
     * @param string[][] $grid
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

function cellType($grid, $r, $c):CellType{
    $cell = $grid->getCell($r, $c);
    $up = $grid->getCell($r-1, $c);
    $left = $grid->getCell($r, $c-1);
    $right = $grid->getCell($r, $c+1);
    $down = $grid->getCell($r+1, $c);
    $upL = $grid->getCell($r-1, $c-1);
    $downL = $grid->getCell($r+1, $c-1);
    $upR = $grid->getCell($r-1, $c+1);
    $downR = $grid->getCell($r+1, $c+1);


    if(
        /**
         * Stand alone
         * 0 0 0
         * 0 1 0
         * 0 0 0
         **/
        $cell !== $up &&
        $cell !== $left &&
        $cell !== $right &&
        $cell !== $down
    ){
        return CellType::STAND_ALONE;
    }else if(
        /**
         * Center
         * 1 1 1
         * 1 1 1
         * 1 1 1
         **/
        $cell == $upL &&
        $cell == $up &&
        $cell == $upR &&
        $cell == $right &&
        $cell == $downR &&
        $cell == $down &&
        $cell == $downL &&
        $cell == $left
    ){
            return CellType::CENTER;
    }else if(
        /**
         * Top left corner
         * 0 0 0
         * 0 1 1
         * 0 1 1
         **/
        $cell != $upL &&
        $cell != $up &&
        $cell != $upR &&
        $cell == $right &&
        $cell == $downR &&
        $cell == $down &&
        $cell != $downL &&
        $cell != $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Top right corner
         * 0 0 0
         * 1 1 0
         * 1 1 0
         **/
        $cell != $upL &&
        $cell != $up &&
        $cell != $upR &&
        $cell != $right &&
        $cell != $downR &&
        $cell == $down &&
        $cell == $downL &&
        $cell == $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Bottom right corner
         * 1 1 0
         * 1 1 0
         * 0 0 0
         **/
        $cell == $upL &&
        $cell == $up &&
        $cell != $upR &&
        $cell != $right &&
        $cell != $downR &&
        $cell != $down &&
        $cell != $downL &&
        $cell == $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Bottom left corner
         * 0 1 1
         * 0 1 1
         * 0 0 0
         **/
        $cell != $upL &&
        $cell == $up &&
        $cell == $upR &&
        $cell == $right &&
        $cell != $downR &&
        $cell != $down &&
        $cell != $downL &&
        $cell != $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Internal Join 1
         * 1 3 3
         * 2 1 1
         * 2 1 1
         **/
        $cell == $upL &&
        $cell !== $up &&
        $up == $upR &&
        $cell == $right &&
        $cell == $downR &&
        $cell == $down &&
        $cell !== $downL &&
        $cell !== $left &&
        $downL == $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Internal Join 2
         * 2 2 1
         * 1 1 3
         * 1 1 3
         **/
        $cell !== $upL &&
        $cell !== $up &&
        $cell == $upR &&
        $cell !== $right &&
        $cell !== $downR &&
        $cell == $down &&
        $cell == $downL &&
        $cell == $left &&
        $upR == $up &&
        $right == $downR
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Internal Join 3
         *
         * 1 1 2
         * 1 1 2
         * 3 3 1
         **/
        $cell == $upL &&
        $cell == $up &&
        $cell !== $upR &&
        $cell !== $right &&
        $cell == $downR &&
        $cell !== $down &&
        $cell !== $downL &&
        $cell == $left &&
        $upR == $right &&
        $down == $downR
    ){
        return CellType::CORNER;
    }else if (
        /**
         * Internal Join 4
         *
         * 3 1 1
         * 3 1 1
         * 1 2 2
         *
         **/
        $cell !== $upL &&
        $cell == $up &&
        $cell == $upR &&
        $cell == $right &&
        $cell !== $downR &&
        $cell !== $down &&
        $cell == $downL &&
        $cell !== $left &&
        $upL == $left &&
        $down == $downL
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Internal F
         * 1 1 1
         * 1 1 1
         * 1 1 0
         **/
        $cell == $upL &&
        $cell == $up &&
        $cell == $upR &&
        $cell == $right &&
        $cell !== $downR &&
        $cell == $down &&
        $cell == $downL &&
        $cell == $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Internal 7
         * 1 1 1
         * 1 1 1
         * 0 1 1
         **/
        $cell == $upL &&
        $cell == $up &&
        $cell == $upR &&
        $cell == $right &&
        $cell == $downR &&
        $cell == $down &&
        $cell !== $downL &&
        $cell == $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Internal J
         * 0 1 1
         * 1 1 1
         * 1 1 1
         **/
        $cell !== $upL &&
        $cell == $up &&
        $cell == $upR &&
        $cell == $right &&
        $cell == $downR &&
        $cell == $down &&
        $cell == $downL &&
        $cell == $left
    ){
        return CellType::CORNER;
    }else if(
        /**
         * Internal L
         * 1 1 0
         * 1 1 1
         * 1 1 1
         **/
        $cell == $upL &&
        $cell == $up &&
        $cell !== $upR &&
        $cell == $right &&
        $cell == $downR &&
        $cell == $down &&
        $cell == $downL &&
        $cell == $left
    ){
        return CellType::CORNER;
    }else if($cell == $up && $cell== $down){ // vertical coridor

        /**
         * ? 1 ?
         * 1 1 0
         * 0 1 0
         **/
        if($cell !== $left && $cell !== $right){
            return CellType::VERTICAL_CORRIDOR;
        }
        /**
         *   1 0 1
         **/
        return CellType::SINGLE_EDGE; // edge only
    }else if($cell == $left && $cell== $right){ // horizontal coridor

        /**  \/
         *  ? 0 1
         *  0 0 0
         *  ? 1 1
         **/
        if($cell != $up && $cell !== $down){
            return CellType::HORIZONTAL_CORRIDOR; // coridor
        }
        return CellType::SINGLE_EDGE; // edge only
    }else{
        return CellType::PENINSULA; // edge
    }
}



function floodFill($grid, $r, $c, $galaxyCount) {
    $targ = $grid[$r][$c];
    if ($targ == $galaxyCount) return $grid;
    checkNeighbours($grid, $r, $c, $galaxyCount, $targ);
    return $grid;
}

function checkNeighbours(&$grid, $r, $c, $galaxyCoung, $targ): void {
    if (!isset($grid[$r][$c]) || $grid[$r][$c] != $targ){
        return;
    }

    $grid[$r][$c] = $galaxyCoung;

    checkNeighbours($grid, $r - 1, $c, $galaxyCoung, $targ);
    checkNeighbours($grid, $r + 1, $c, $galaxyCoung, $targ);
    checkNeighbours($grid, $r, $c - 1, $galaxyCoung, $targ);
    checkNeighbours($grid, $r, $c + 1, $galaxyCoung, $targ);
}

function part2():void{
    /**
     * @var array<string,int> $galaxyIterations
     **/
    $galaxyIterations = [];
    $galaxies = [];
    //$input = file_get_contents(__DIR__ . '/single-galaxies-test-input.txt');
    //$input = file_get_contents(__DIR__ . '/e-region-test.txt');
    $input = file_get_contents(__DIR__ . '/price-test-input2.txt');
   //$input = file_get_contents(__DIR__ . '/base-test-input.txt');
   //$input = file_get_contents(__DIR__ . '/test-input.txt');
    //$input = file_get_contents(__DIR__ . '/input.txt');
    if($input === false) exit("Input file not found" . PHP_EOL);
    $input = trim($input);

    $grid = Grid::fromString($input);


    $legend = [];
    $galaxyCount = 0;



    $newGrid = $grid->getGrid();

    // preprocess grid


    for($r = 0; $r < count($newGrid); $r++){
        for($c = 0; $c < count($newGrid[0]); $c++){
            $cell = $newGrid[$r][$c];

            if(!is_numeric($cell)){
                $legend[$galaxyCount] = $cell;
                $newGrid = floodFill($newGrid, $r, $c, $galaxyCount);
                $galaxyCount++;
            }
        }
    }

    $floodedGrid = [];
    foreach($newGrid as $row){
        $floodedGrid[] = $row;
    }


    if($floodedGrid){
        $grid->setGrid($floodedGrid);
    }

    $ans = 0;
    for($r = 0; $r < count($grid->getGrid()); $r++){
        for($c = 0; $c < count($grid->getGrid()[0]); $c++){

            $cell = $grid->getCell($r, $c);
            $cellType = cellType($grid, $r, $c);







           if(!isset($galaxies[$cell])){
                $galaxies[$cell] = [
                    'area' => 1,
                    'key' => $legend[$cell],
                    'sides' => 0,
                    'type' => $cellType,
                    'cells' => [[$r,$c,$cellType]]
                ];
            }else{
                $galaxies[$cell]['area']++;
                $galaxies[$cell]['cells'][] = [$r, $c, $cellType];
            }
        }
    }



    $blownUpGrid = [];
    /**
     * Blow up the grid
     *
     * for each cell in the grid
     * create it as a 3x3 matrix in the new grid
     **/
    for($r = 0; $r < count($grid->getGrid()); $r++){
        for($c = 0; $c < count($grid->getGrid()[0]); $c++){
            $cell = $grid->getCell($r, $c);
            $blownUpGrid[$r*3][$c*3] = $cell;
            for($i = 1; $i < 3; $i++){
                $blownUpGrid[$r*3][$c*3+$i] = $cell;
            }

            $blownUpGrid[$r*3+1][$c*3] = $cell;
            $blownUpGrid[$r*3+1][$c*3+1] = $cell;
            $blownUpGrid[$r*3+1][$c*3+2] = $cell;

            $blownUpGrid[$r*3+2][$c*3] = $cell;
            $blownUpGrid[$r*3+2][$c*3+1] = $cell;
            $blownUpGrid[$r*3+2][$c*3+2] = $cell;
        }
    }

    $blownGrid = new Grid($blownUpGrid);
    echo $blownGrid . PHP_EOL;


    for($r = 0; $r < count($blownGrid->getGrid()); $r++){
        for($c = 0; $c < count($blownGrid->getGrid()[0]); $c++){

            $cell = $blownGrid->getCell($r, $c);
            $cellType = cellType($blownGrid, $r, $c);

            if($cellType === CellType::CORNER){
                echo "Corner at ($r,$c)" . PHP_EOL;
                $galaxies[$cell]['sides']++;
            }
        }
    }




    foreach($galaxies as $galaxy){


$t = $galaxy['type'] == CellType::STAND_ALONE ? 'stand alone':'norm';

        echo "Galaxy {$galaxy['key']}\n\tType:{$t}\n\tArea:{$galaxy['area']}\n\tSide:{$galaxy['sides']} " . PHP_EOL;
        $ans += $galaxy['sides'] * $galaxy['area'];
    }

    echo "Part 2: $ans == 1930" . PHP_EOL;
}

part2();
