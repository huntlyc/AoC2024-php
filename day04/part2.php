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

    for($row = 1; $row < count($grid) - 1; $row++) {

        $rowStr = implode('', $grid[$row]);

        for($col = 1; $col < count($grid[$row]) - 1; $col++) {

            $char = $grid[$row][$col];

            if($char == 'A'){
                $sum += isMasGroup($grid, $row, $col);
            }
        }
    }




    /**
     * Check if valid
     *
     * M . M
     * . A .
     * S . S
     *
     * Note: can be MAS OR SAM in any diagonal direction
     *
     * @param $grid
     * @param $row
     * @param $col
     * @return bool
     */
    function isMasGroup(array &$grid, int $row, int $col):bool {

        $leftTop = $grid[$row - 1][$col - 1];
        $rightTop = $grid[$row - 1][$col + 1];
        $leftBottom = $grid[$row + 1][$col - 1];
        $rightBottom = $grid[$row + 1][$col + 1];

        return (
            (($leftTop == 'S'  && $rightBottom == 'M') || ($leftTop == 'M'  && $rightBottom == 'S')) &&
            (($rightTop == 'S'  && $leftBottom == 'M') || ($rightTop == 'M'  && $leftBottom == 'S')));
    }

    echo "Part 2 answer: $sum". PHP_EOL;
