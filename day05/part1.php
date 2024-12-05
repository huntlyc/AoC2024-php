<?php
    declare(strict_types=1);

    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);


    $pageOrderingRules = []; // [ [p1,p2] ... ]
    $updates = [];
    $validUpdates = [];
    $isPageOrdering = true;
    // Parse the input
    foreach ($lines as $line) {
        if(empty($line)){
            $isPageOrdering = false;
            continue;
        }

        if($isPageOrdering){
            $pageOrderingRules[] = explode('|',$line);
        } else {
            $updates[] = $line;
        }
    }


    foreach ($updates as $update) {
        $updatePages = explode(',',$update);
        $valid = true;

        foreach($updatePages as $updatePage){
            foreach($pageOrderingRules as $rule){
                if($rule[0] == $updatePage){
                    $re = "/{$rule[1]}.*?{$rule[0]}/";
                    if(preg_match($re, $update)){
                        $valid = false;
                        break;
                    }
                }
            }
            if(!$valid){
                break;
            }
        }
        if($valid){
            $validUpdates[] = $update;
        }
    }

    $validSum = 0;
    if(!empty($validUpdates)) {
        foreach($validUpdates as $update) {
            $updatePages = explode(',',$update);
            $validSum += intval($updatePages[(count($updatePages)-1)/2]);
        }
    }




    echo "Part 1 answer: $validSum". PHP_EOL;
