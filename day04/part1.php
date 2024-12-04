<?php
    declare(strict_types=1);

    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);


    function printGrid(array &$grid) {
        foreach($grid as $row) {
            echo implode('', $row) . PHP_EOL;
        }
        echo PHP_EOL;
        echo PHP_EOL;
    }

    $sum = 0;
    $grid = [];
    // Parse the input
    foreach ($lines as $line) {
        if(empty($line)) continue;

        $grid[] = str_split($line);
    }

    if(DEBUG) printGrid($grid);

    for($row = 0; $row < count($grid); $row++) {

        $rowStr = implode('', $grid[$row]);

        $sum += substr_count($rowStr, 'XMAS');
        $sum += substr_count($rowStr, 'SAMX');

        for($col = 0; $col < count($grid[$row]); $col++) {
            if($row == 0){
                $colStr = implode('', array_column($grid, $col));
                $sum += substr_count($colStr, 'XMAS');
                $sum += substr_count($colStr, 'SAMX');
            }
            $char = $grid[$row][$col];

            if($char == 'X'){
                $sum += countXmasDiagonals($grid, $row, $col);
            }
        }
    }


    /**
     * Check if the word XMAS is on the diagonal direction
     * @param $grid
     * @param $x
     * @param $y
     * @param $dir [x, y] direction with poitive values being down and right
     * @return bool
     */
    function hasXmasOnDiagonalDirection(array &$grid, int $row, int $col, array $dir):bool {
        $s = '';

        for($i = 0; $i < 4; $i++) {
            $s .= $grid[$row + ($dir[0] * $i)][$col + ($dir[1] * $i)];
        }

        if($s == 'XMAS') {
            return true;
        }

        return false;

    }


    /**
     * Count the number of XMAS in the diagonals from the given position
     * @param $grid
     * @param $row
     * @param $col
     * @return int
     */
    function countXmasDiagonals(array &$grid, int $row, int $col):int {
        $count = 0;

        // Check up-left
        if($row - 3 >= 0 && $col - 3 >= 0) {
            $count += hasXmasOnDiagonalDirection($grid, $row, $col, [-1, -1]);
        }

        // Check up-right
        if($row - 3 >= 0 && $col + 3 < count($grid[$row])) {
            $count += hasXmasOnDiagonalDirection($grid, $row, $col, [-1, 1]);
        }

        // Check down-left
        if($row + 3 < count($grid) && $col - 3 >= 0) {
            $count += hasXmasOnDiagonalDirection($grid, $row, $col, [1, -1]);
        }

        // Check down-right
        if($row + 3 < count($grid) && $col + 3 < count($grid[$row])) {
            $count += hasXmasOnDiagonalDirection($grid, $row, $col, [1, 1]);
        }

        return $count;
    }

    echo "Part 1 answer: $sum". PHP_EOL;
