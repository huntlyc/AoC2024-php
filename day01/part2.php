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

    $sum = 0;

    foreach ($list1 as $itm){
        $count = 0;
        foreach($list2 as $v){
            if($v === $itm){
                ++$count;
            }
        }
        $sum += $itm * $count;
    }


    echo "Part 2 answer: $sum";
    echo PHP_EOL;
