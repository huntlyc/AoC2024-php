<?php

declare(strict_types=1);

namespace AOC\D11\P1;

use SplDoublyLinkedList;


class Part1{
    static function run():void{
        $blink = 25;
        $input = file_get_contents(__DIR__ . '/input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);
        $input = trim($input);

        $tmpStones = array_map('intval', explode(" ", $input));

        /**
         * @var SplDoublyLinkedList<int> $stones
         */
        $stones = new SplDoublyLinkedList();

        foreach($tmpStones as $stone){
            $stones->push($stone);
        }

        //var_dump($stones);

        for($i = 0; $i < $blink; $i++){
            for ($stones->rewind(); $stones->valid(); $stones->next()) {
                $stone = $stones->current();


                //echo "checking stone: $stone" . PHP_EOL;

                if($stone === 0){
                 //   echo "making stone 0 to 1" . PHP_EOL;
                    $stones->offsetSet($stones->key(), 1);
                }elseif(strlen((string)$stone) % 2 === 0){
                  //  echo "splitting stone $stone" . PHP_EOL;
                    $left = substr((string)$stone, 0, intval(strlen((string)$stone) / 2));
                    $right = substr((string)$stone, intval(strlen((string)$stone) / 2));

                    //echo "l: $left, r: $right" . PHP_EOL;

                    $stones->offsetSet($stones->key(), (int)$left);
                    $stones->add($stones->key() + 1, (int)$right);

                    // move next
                    $stones->next();
                }else{
                   // echo "multiplying stone $stone * 2024" . PHP_EOL;
                    $stones->offsetSet($stones->key(), $stone * 2024);
                }
            }
            //echo "After $i blinks" . PHP_EOL;
            //var_dump($stones);
            //echo PHP_EOL;
        }

        $ans = count($stones);

        echo "Part 1: $ans" . PHP_EOL;
    }
}

Part1::run();
