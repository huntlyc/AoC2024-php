<?php

declare(strict_types=1);

namespace AOC\D11\P2;

enum CellType {
    case CENTER;
    case CORNER;
    case VERTICAL_CORRIDOR;
    case HORIZONTAL_CORRIDOR;
    case STAND_ALONE;
    case SINGLE_EDGE;
    case PENINSULA;
}

class Grid
{
    /**
     * @var string[][] $grid
     */
    private array $grid;

    /**
     * @var array<int, string> $legend
     */
    private $legend;

    /**
     * @param string[][] $grid
     */
    public function __construct(array $grid)
    {
        $this->grid = $grid;
        $this->legend = [];
    }

    public function addLegend(int $key, string $value):void{
        $this->legend[$key] = $value;
    }

    public function getLegend(int $key):string{
        return $this->legend[$key];
    }


    /**
     * @return string[][]
     */
    public function getGrid(): array {
        return $this->grid;
    }

    public function getWidth(): int {
        return count($this->grid);
    }

    public function getHeight(): int {
        return count($this->grid[0]);
    }

    /**
     * @param string[][] $grid
     */
    public function setGrid(array $grid): void {
        $this->grid = $grid;
    }

    public function getCell(int $r, int $c): string {
        if($r < 0 || $r >= count($this->grid) || $c < 0 || $c >= count($this->grid[0])){
            return "";
        }
        return $this->grid[$r][$c];
    }


    public function __toString(): string {
        $str = "";
        foreach ($this->grid as $row) {
            $str .= "'" . implode("", $row) . "'" . PHP_EOL;
        }
        return $str;
    }

    /**
     * @param int $r
     * @param int $c
     * @param int $galaxyCount
     * @return string[][]
     */
    public function floodFill(int $r, int $c, int $galaxyCount):array{
        $targ = $this->grid[$r][$c];
        if ($targ == $galaxyCount) return $this->grid;
        $this->fillNeighbours($r, $c, $galaxyCount, $targ);
        return $this->grid;
    }

    private function fillNeighbours(int $r, int $c, int $galaxyCount, string|int $targ): void {
        if (!isset($this->grid[$r][$c]) || $this->grid[$r][$c] != $targ){
            return;
        }

        $this->grid[$r][$c] = "$galaxyCount";

        $this->fillNeighbours($r - 1, $c, $galaxyCount, $targ);
        $this->fillNeighbours($r + 1, $c, $galaxyCount, $targ);
        $this->fillNeighbours($r, $c - 1, $galaxyCount, $targ);
        $this->fillNeighbours($r, $c + 1, $galaxyCount, $targ);
    }


    // clone
    public function clone(): Grid{
        $newGrid = [];
        foreach($this->grid as $row){
            $newGrid[] = $row;
        }
        return new Grid($newGrid);
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

    public function getCellType(Grid $grid, int $r, int $c):CellType{
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
            $downL == $left &&
            $up !== $left
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

            $upL == $up &&
            $right == $downR &&
            $right !== $up

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
            $down == $downL &&
            $down !== $right

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
            $down == $downR &&
            $down !== $left
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
}



class Solver{

    /**
     * @var int $galaxyCount
     */
    private $galaxyCount;
    /**
     * @var Grid $grid
     */
    private $grid;

    function __construct(string $input){
        $this->galaxyCount = 0;
        $this->grid = Grid::fromString($input);
    }

    private function getFloddedGrid():Grid{
        $floodedGrid = $this->grid->clone();
        echo 'w: ' . $floodedGrid->getWidth() . PHP_EOL;
        echo 'h: ' . $floodedGrid->getHeight() . PHP_EOL;

        for($r = 0; $r < $floodedGrid->getWidth(); $r++){
            for($c = 0; $c < $floodedGrid->getHeight(); $c++){

                $cell = $floodedGrid->getCell($r, $c);

                if(!is_numeric($cell)){
                    $floodedGrid->addLegend($this->galaxyCount, $cell);
                    $floodedGrid->floodFill($r, $c, $this->galaxyCount);
                    $this->galaxyCount++;
                }

            }
        }

        return $floodedGrid;

    }

    /**
     * @param Grid $floodedGrid
     *
     * @return array<string, array{area: int, key: string, sides: int, cells: array<array{int, int, \AOC\D11\P2\CellType}>}>
     */
    private function getGalaxiesFromGrid(Grid $floodedGrid):array{
        $galaxies = [];
        for($r = 0; $r < count($floodedGrid->getGrid()); $r++){
            for($c = 0; $c < count($floodedGrid->getGrid()[0]); $c++){

                $cell = $floodedGrid->getCell($r, $c);
                $cellType = $floodedGrid->getCellType($floodedGrid, $r, $c);


                if(!isset($galaxies[$cell])){
                    $galaxies[$cell] = [
                        'area' => 1,
                        'key' => $floodedGrid->getLegend((int) $cell),
                        'sides' => 0,
                        'cells' => [[$r,$c,$cellType]]
                    ];
                }else{
                    $galaxies[$cell]['area']++;
                    $galaxies[$cell]['cells'][] = [$r, $c, $cellType];
                }
            }
        }
        return $galaxies;
    }


    public function solve(): int{
        $floodedGrid = $this->getFloddedGrid();
        $galaxies = $this->getGalaxiesFromGrid($floodedGrid);


        $blownUpGrid = [];
        /**
         * Blow up the grid
         *
         * for each cell in the grid
         * create it as a 3x3 matrix in the new grid
         **/
        for($r = 0; $r < count($floodedGrid->getGrid()); $r++){
            for($c = 0; $c < count($floodedGrid->getGrid()[0]); $c++){
                $cell = $floodedGrid->getCell($r, $c);
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


        for($r = 0; $r < count($blownGrid->getGrid()); $r++){
            for($c = 0; $c < count($blownGrid->getGrid()[0]); $c++){

                $cell = $blownGrid->getCell($r, $c);
                $cellType = $blownGrid->getCellType($blownGrid, $r, $c);

                if($cellType === CellType::CORNER){
                    $galaxies[$cell]['sides']++;
                }
            }
        }

        $ans = 0;
        foreach($galaxies as $galaxy){
            $ans += $galaxy['sides'] * $galaxy['area'];
        }
        return $ans;
    }
}

class Part2{
    static function run():void{
        /**
         * @var string[] $files
         */
        $files =[
            'base-test-input.txt',
            'single-galaxies-test-input.txt',
            'e-region-test.txt',
            'price-test-input2.txt',
            'test-input.txt',
            'input.txt'
        ];


        foreach($files as $file){
            $input = file_get_contents(__DIR__ . '/' . $file);
            if($input === false) exit("Input file not found" . PHP_EOL);
            $input = trim($input);

            $ans = (new Solver($input))->solve();
            echo "{$ans} ({$file})" . PHP_EOL;
        }
    }
}

Part2::run();
