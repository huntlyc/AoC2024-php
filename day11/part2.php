<?php

declare(strict_types=1);

namespace AOC\D11\P2;

class StoneCrusher{

    /**
     * @var array<string, int>
     */
    private $cache;

    public function __construct(){
        $this->cache = [];
    }

    /**
     * Recursion man, I hate this sh...
     *
     * For future self, *BASE CASES* and *CACHE* are your friends
     *
     * Base Case:
     * - no iterations, 1 stone
     * - check cache for a hit and return it
     *
     * Tranform rules as form part one:
     * - if stone is 0, return 1
     * - if stone is even, split it
     * - otherwise multiply stone by 2024
     *
     * Store the result in cache and return it
     */
    public function smash(int $stone, int $iteration):int{

        if($iteration === 0){
            return 1;
        }

        $key = md5("$stone:$iteration");

        if(isset($this->cache[$key])){
            return $this->cache[$key];
        }

        $output = 0;
        if($stone === 0){
            $output = $this->smash(1, $iteration - 1);
        }elseif(strlen("$stone") % 2 === 0){

            $left = intval(substr((string)$stone, 0, intval(strlen((string)$stone) / 2)));
            $right = intval(substr((string)$stone, intval(strlen((string)$stone) / 2)));

            $output = $this->smash($left, $iteration - 1) + $this->smash($right, $iteration - 1);
        }else{
            $output = $this->smash($stone * 2024, $iteration - 1);
        }


        $this->cache[$key] = $output;

        return $output;
    }
}

class Part2{
    static function run():void{
        $input = file_get_contents(__DIR__ . '/input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);
        $input = trim($input);

        $stones = array_map('intval', explode(" ", $input));

        $crusher = new StoneCrusher();

        $ans = 0;
        foreach($stones as $stone){
            $ans += $crusher->smash($stone, 75);
        }

        echo "Part 2: $ans" . PHP_EOL;
    }
}
Part2::run();

