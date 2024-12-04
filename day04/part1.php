<?php
    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);


    function printGrid($grid) {
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

    printGrid($grid);

    for($row = 0; $row < count($grid); $row++) {
        for($col = 0; $col < count($grid[$row]); $col++) {
            $char = $grid[$row][$col];
            if($char == 'X'){
                echo "Checking $row, $col" . PHP_EOL;
                $sum += hasXmas($grid, $row, $col);
            }
        }
    }

    function hasXmas($grid, $row, $col) {
        $count = 0;
        // Check up
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($row - $i < 0) break;
            $s .= $grid[$row - $i][$col];
        }
        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Up: $s" . PHP_EOL;
        }

        // Check down
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($row + $i >= count($grid)) break;
            $s .= $grid[$row + $i][$col];
        }

        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Down: $s" . PHP_EOL;
        }

        // Check left
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($col - $i < 0) break;
            $s .= $grid[$row][$col - $i];
        }

        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Left: $s" . PHP_EOL;
        }

        // Check right
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($col + $i >= count($grid[$row])) break;
            $s .= $grid[$row][$col + $i];
        }

        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Right: $s" . PHP_EOL;
        }

        // Check up-left
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($row - $i < 0 || $col - $i < 0) break;
            $s .= $grid[$row - $i][$col - $i];
        }

        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Up-left: $s" . PHP_EOL;
        }

        // Check up-right
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($row - $i < 0 || $col + $i >= count($grid[$row])) break;
            $s .= $grid[$row - $i][$col + $i];
        }

        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Up-right: $s" . PHP_EOL;
        }

        // Check down-left
        //
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($row + $i >= count($grid) || $col - $i < 0) break;
            $s .= $grid[$row + $i][$col - $i];
        }

        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Down-left: $s" . PHP_EOL;
        }

        // Check down-right
        $s = '';
        for($i = 0; $i < 4; $i++) {
            if($row + $i >= count($grid) || $col + $i >= count($grid[$row])) break;
            $s .= $grid[$row + $i][$col + $i];
        }

        if($s == 'XMAS') {
            ++$count;
        }else{
            echo "Down-right: $s" . PHP_EOL;
        }

        return $count;
    }

    echo "Part 1 answer: $sum";
    echo PHP_EOL;
