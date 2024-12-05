<?php
    declare(strict_types=1);

    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);


    $pageOrderingRules = []; // [ [p1,p2] ... ]
    $updates = [];
    $validUpdates = [];
    $inValidUpdates = [];
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
        if(!$valid){
            $invalidUpdates[] = $update;
        }
    }


    echo "Invalid updates: ". PHP_EOL;
    var_dump($invalidUpdates);

    for($i = 0; $i < count($invalidUpdates); ){
        $update = $invalidUpdates[$i];
        $updatePages = explode(',',$update);
        $valid = true;
        $invalidPageOrder = [];

        $goAgain = false;

        foreach($updatePages as $updatePage){
            foreach($pageOrderingRules as $rule){
                if($rule[0] == $updatePage){
                    $re = "/{$rule[1]}.*?{$rule[0]}/";
                    if(preg_match($re, $update)){
                        $valid = false;
                        $invalidPageOrder[] = $rule;
                        break;
                    }
                }
            }

            if(!$valid){
                echo "Invalid update: $update". PHP_EOL;
                echo "Invalid page order: ". implode(',',$invalidPageOrder[0]). PHP_EOL;
                $i1 = $i2 = 0;
                for($ri = 0; $ri < count($updatePages); $ri++){
                    if($updatePages[$ri] == $invalidPageOrder[0][1]){
                        $i1 = $ri;
                    }
                    if($updatePages[$ri] == $invalidPageOrder[0][0]){
                        $i2 = $ri;
                    }
                }

                $temp = $updatePages[$i1];
                $updatePages[$i1] = $updatePages[$i2];
                $updatePages[$i2] = $temp;
                $goAgain = true;

                break;
            }
        }
        $invalidUpdates[$i] = implode(',',$updatePages);
        if(!$goAgain){
            $i++;
        }
    }

    echo "Invalid updates after fix: ". PHP_EOL;
    var_dump($invalidUpdates);
        $validSum = 0;
    if(!empty($invalidUpdates)) {
        foreach($invalidUpdates as $update) {
            $updatePages = explode(',',$update);
            $validSum += intval($updatePages[(count($updatePages)-1)/2]);
        }
    }



    echo "Part 2 answer: $validSum". PHP_EOL;

