<?php
    declare(strict_types=1);

    include 'utils.php'; // print grid,e.t.c.

    define('DEBUG', true);
    define('TICK_TIMOUT', intval(10 * 10000));

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);
    $sum = 0;

    // All in Y,X
    $dir = [-1,0];
    $pos = [0,0];
    $visited = [];


    function isOutOfBounds(array $grid, array $pos) {
        if($pos[0] < 0 || $pos[0] >= count($grid) -1){
            return true;
        }

        if($pos[1] < 0 || $pos[1] >= count($grid[0])){
            return true;
        }
    }

    function moveUntilBlock(array $grid, array &$visited, array &$pos, array $dir) {
        while(true) {
            $nextPos = [$pos[0] + $dir[0], $pos[1] + $dir[1]];

            if(isOutOfBounds($grid, $nextPos)) {
                $pos = $nextPos;
                break;
            }

            if($grid[$nextPos[0]][$nextPos[1]] == '#') {
                break;
            }

            $pos = $nextPos;
            addVisited($visited, $pos);
            usleep(TICK_TIMOUT);
            cls();
            printGridInColour($grid, $visited, $pos);
        }
    }

    function addVisited(array &$visited, array $pos) {
        $visited[implode(',',$pos)] = $pos;
    }

    function turnRight(array $dir) {
        // up to right
        switch(implode(',',$dir)) {
            case '-1,0': // up to right
                return [0,1];
            case '0,1': // right to down
                return [1,0];
            case '1,0': // down to left
                return [0,-1];
            case '0,-1': // left to up
                return [-1,0];
        }
    }

    $grid = [];
    foreach ($lines as $line) {
        $grid[] = str_split($line);
        if(array_search('^', $grid[count($grid)-1]) !== false) {
            $pos = [count($grid)-1, array_search('^', $grid[count($grid)-1])];
        }
    }

    $i = 0; $max = 10000;
    addVisited($visited, $pos);
    while(true){
        moveUntilBlock($grid, $visited, $pos, $dir);
        $dir = turnRight($dir);

        if(isOutOfBounds($grid, $pos) || $i++ > $max) {
            break;
        }
    }

    echo "Part 1 answer: ". count($visited) . PHP_EOL;
