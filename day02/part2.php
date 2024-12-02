<?php
    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $reports = explode(PHP_EOL, $rawInput);

    $numSafe = 0;

    function checkReport($report, $faultCount = 0){
        if($faultCount > 1){
            return false;
        }

        $levels = explode(' ', $report);

        $increasing = true;
        $decreasing = true;

        for($i = 1; $i < count($levels); $i++) {
            $curr = $levels[$i];
            $prev = $levels[$i - 1];
            $isError = false;

            // can't do a change of less than 1 or more than 3
            if(abs($curr - $prev) == 0 || abs($curr - $prev) > 3) {
                $isError = true;
            }

            // get current direction of travel
            if($curr > $prev) {
                $decreasing = false;
            } else if($curr < $prev) {
                $increasing = false;
            }

            if(!$increasing && !$decreasing){
                $isError = true;
            }

            if($isError){
                /**
                 * Forgive me, for I am about to brute force
                 **/
                for($j = 0; $j < count($levels); $j++){
                    $tmp = $levels;
                    unset($tmp[$j]);
                    if(checkReport(implode(' ', $tmp), $faultCount + 1)){
                        return true;
                    }
                }
                return false;

            }
        }

        return true;
    }



    foreach ($reports as $report) {
        // skip empty lines
        if(empty($report)) {
            continue;
        }

        if(checkReport($report)){
            $numSafe++;
        }
    }

    echo "Part 2 answer: $numSafe";
    echo PHP_EOL;
