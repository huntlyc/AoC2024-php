<?php
    declare(strict_types=1);

    define('DEBUG', false);
    define('TICK_TIMOUT', intval(1 * 1000));

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);
    $sum = 0;

    // All in Y,X
    $dir = [-1,0];
    $pos = [0,0];
    $visited = [];

    function cls(){
        print("\033[2J\033[;H");
    }

    function printGrid(array $grid) {
        foreach($grid as $row) {
            echo implode('', $row) . PHP_EOL;
        }
        echo PHP_EOL;
        echo PHP_EOL;
    }
    function printGridWithVisited(array $grid, array $visited, $pos = null) {

        $i = 0;
        foreach($visited as $v) {
            if(++$i === 1) {
                $grid[$v[0]][$v[1]] = '^';
            } else {
                $grid[$v[0]][$v[1]] = 'X';
            }
        }

        if($pos) {
            $grid[$pos[0]][$pos[1]] = '^';
        }

        foreach($grid as $row) {
            echo implode('', $row) . PHP_EOL;
        }

        echo PHP_EOL;
        echo PHP_EOL;
    }


    function isOutOfBounds($grid, $pos) {
        if($pos[0] < 0 || $pos[0] >= count($grid) -1){
            return true;
        }

        if($pos[1] < 0 || $pos[1] >= count($grid[0])){
            return true;
        }
    }

    function moveUntilBlock($grid, &$visited, &$pos, $dir) {
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
            printGridWithVisited($grid, $visited, $pos);
        }
    }

    function addVisited(&$visited, $pos) {
        $visited[implode(',',$pos)] = $pos;
    }

    function turnRight($dir) {
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




    //printGridWithVisited($grid, $visited);


    echo "Part 1 answer: ". count($visited) . PHP_EOL;
