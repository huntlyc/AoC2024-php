<?php
    define('DEBUG', false);

    $rawInput = file_get_contents(DEBUG ? 'test-input2.txt' : 'input.txt');
    $lines = explode(PHP_EOL, $rawInput);

    $validFnCalls = []; // [ [arg1, arg2], ... ]

    $fullProgram = '';

    foreach ($lines as $line) {
        if (empty($line)) {
            continue;
        }
        $fullProgram .= $line;
    }

    // strip out don't()->do() calls
    $invalidFnCallsRE = '/don\'t.+?(do\(\)|$)/m';
    if(preg_match($invalidFnCallsRE, $fullProgram)) {
        $fullProgram = preg_replace($invalidFnCallsRE, '', $fullProgram);
    }


    $re = '/mul\((\d{1,3}),(\d{1,3})\)/m';
    if(preg_match($re, $fullProgram)) {
        preg_match_all($re, $fullProgram, $matches, PREG_SET_ORDER, 0);
        if($matches) {
            foreach ($matches as $match) {
                $validFnCalls[] = [$match[1], $match[2]];
            }
        }
    }
    $sum = 0;

    foreach ($validFnCalls as $fnCall) {
        $sum += $fnCall[0] * $fnCall[1];
    }

    echo "Part 2 answer: $sum";
    echo PHP_EOL;
