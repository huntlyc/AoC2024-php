<?php
    declare(strict_types=1);

    include 'utils.php';

    define('DEBUG', true);
    define('TICK_TIMOUT', intval(2 * 10000));

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);
    $sum = 0;

    // All in Y,X
    $dir = [-1,0];
    $pos = [0,0];
    $visited = []; // [[Y,X], $dir]


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

            if(isLoop($visited, $nextPos, $dir)) {
                throw new Exception("Loop detected");
                break;
            }

            if(isOutOfBounds($grid, $nextPos)) {
                $pos = $nextPos;
                break;
            }

            if(in_array($grid[$nextPos[0]][$nextPos[1]], ['#', 'O'])) {
                break;
            }

            $pos = $nextPos;
            addVisited($visited, $pos, $dir);
            if(DEBUG){
                usleep(TICK_TIMOUT);
                cls();
                printGridInColour($grid, $visited, $pos);
            }
        }
    }

    function addVisited(array &$visited, array $pos, array $dir) {
        $visited[implode(',',$pos)] = [$pos, $dir];
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

    function isLoop(array $visited, array $pos, array $dir){
        foreach($visited as $v) {
            if(implode(',', $v[0]) == implode(',', $pos) && implode(',', $v[1]) == implode(',', $dir)) {
                return true;
            }
        }
        return false;
    }

    $grid = [];
    $startPos = null;
    foreach ($lines as $line) {
        $grid[] = str_split($line);
        if(array_search('^', $grid[count($grid)-1]) !== false) {
            $startPos = $pos = [count($grid)-1, array_search('^', $grid[count($grid)-1])];
        }
    }

    addVisited($visited, $pos, $dir);

    function walkGrid(array $grid, array &$visited, array $pos, array $dir) {
        $i = 0; $max = 10000;
        while(true){
            try{
                moveUntilBlock($grid, $visited, $pos, $dir);
            }catch(Exception $e) {
                throw $e;
                break;
            }
            $dir = turnRight($dir);

            if(isOutOfBounds($grid, $pos) || $i++ > $max) {
                break;
            }
        }
    }

    walkGrid($grid, $visited, $pos, $dir);

    echo "Part 1 answer: ". count($visited) . PHP_EOL;



    $loopCount = 0;
    $loopPoints = [];
    $part1Visited = $visited;

    // got a list of visited positions, so not including the start position, add a 'O' to each visited position and test for loop
    foreach($part1Visited as $v) {
        if(implode(',', $v[0]) == implode(',', $startPos)) {
            continue;
        }

        $pos = $startPos;
        $visited = [];
        $newGrid = $grid;
        $newGrid[$v[0][0]][$v[0][1]] = 'O';
        $dir = [-1,0];



        try{
            walkGrid($newGrid, $visited, $pos, $dir);
        }catch(Exception $e) {
            $loopPoints[] = $v;
            $grid[$v[0][0]][$v[0][1]] = 'L';
            $loopCount++;
        }

    }

    echo "Part 2 answer: ". $loopCount . PHP_EOL;
