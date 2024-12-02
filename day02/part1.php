<?php
    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $reports = explode(PHP_EOL, $rawInput);

    $numSafe = 0;

    foreach ($reports as $report) {
        // skip empty lines
        if(empty($report)) {
            continue;
        }

        $levels = explode(' ', $report);
        $isSafe = true;
        $dir = null;

        for($i = 1; $i < count($levels); $i++) {
            $curr = $levels[$i];
            $prev = $levels[$i - 1];

            // can't do a change of more than 3
            if(abs($curr - $prev) > 3) {
                $isSafe = false;
                break;
            }

            // get current direction of travel
            $curDir = 0;
            if($curr > $prev) {
                $curDir = 1;
            } else if($curr < $prev) {
                $curDir = -1;
            }

            // if dir has changed, bail as it needs to all go one way or the other
            if($dir === null) {
                $dir = $curDir;
            }else if($curDir !== $dir) {
                $isSafe = false;
                break;
            }
        }

        if($isSafe) {
            $numSafe++;
        }
    }



    echo "Part 1 answer: $numSafe";
    echo PHP_EOL;
