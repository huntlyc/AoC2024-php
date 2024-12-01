<?php
    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);

    $list1 = [];
    $list2 = [];


    foreach ($lines as $line){
        if(preg_match('/(\d+)\s+(\d+)/', $line, $matches)){
            $list1[] = $matches[1];
            $list2[] = $matches[2];
        }
    }

    sort($list1);
    sort($list2);


    $sum = 0;

    for($i = 0; $i < count($list1); $i++){
        $sum += abs($list1[$i] - $list2[$i]);
    }


    echo "Part 1 answer: $sum";
    echo PHP_EOL;
