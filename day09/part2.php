<?php

declare(strict_types=1);

namespace AOC\D09\P1;
use SplDoublyLinkedList;


/**
 * Class Block
 */
class Block{
    /**
     * @var int
     **/
    public $label;

    /**
     * @var int
     **/
    public $size;

    function __construct(int $label, int $size){
        $this->label = $label;
        $this->size = $size;
    }

    function __toString(){
        return "{$this->label} ({$this->size})";
    }
}


class FileSystem{
    /**
     * @var SplDoublyLinkedList
     **/
    private $blocks;

    public function __construct(){
        $this->blocks = new SplDoublyLinkedList();
    }

    public function defrag(){
        $this->blocks->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
        $head = $this->blocks->count() - 1;


        $targetLabel = null;
        while($head > 0){
            $tmp = $this->blocks->offsetGet($head);
            if($tmp->label != -1) {
                $targetLabel = $tmp->label;
                break;
            }
            $head--;
        }

        while($targetLabel > 0){

            // get a valid block from the end
            while($head > 0){
                $endBlock = $this->blocks->offsetGet($head);
                if($endBlock->label == $targetLabel) break;
                $head--;
            }


            // start from the beginning and look for something with enough free space
            for($this->blocks->rewind(); $this->blocks->valid(); $this->blocks->next()){
                $block = $this->blocks->current();
                if($block->label != -1) continue; // skip files

                /**
                 * We only move blocks left and into a space that is at least the same size as the block we are moving
                 **/
                if($this->blocks->key() < $head && $endBlock->size <= $block->size){

                    $remainingSpace = $block->size - $endBlock->size;

                    // swap blocks
                    $block->label = $endBlock->label;
                    $block->size = $endBlock->size;

                    $endBlock->label = -1;
                    $endBlock->size = $block->size;

                    /**
                     * add any remaining space back after the block we've just moved left
                     * @example
                     * 00[...]1111..(99)
                     * 00[99.]1111..(..)
                     **/
                    if($remainingSpace > 0){
                        $this->blocks->add($this->blocks->key() + 1, new Block(-1, $remainingSpace));
                    }
                    break;
                }else{
                }
            }

            $targetLabel--;
        }
    }



    public function checksum(){
        $checksum = 0;
        $this->blocks->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);

        $sumMultiplier = 0;
        for ($this->blocks->rewind(); $this->blocks->valid(); $this->blocks->next()) {
            $block = $this->blocks->current();
            for($i = 0; $i < $block->size; $i++){
                if($block->label !== -1){
                    $checksum += $sumMultiplier * $block->label;
                }
                $sumMultiplier++;
            }
        }
        return $checksum;
    }

    static function fromMap(string $map){
        $fs = new FileSystem();

        if($map === '') return $fs;

        $map = str_split(trim($map));

        $isFreeSpace = false;
        $fileLabel = 0;
        foreach($map as $m){
            $label = $isFreeSpace ? -1 : $fileLabel;
            $size = intval($m);

            $fs->blocks->push(new Block($label, $size));

            if($label >= 0) $fileLabel++;

            $isFreeSpace = !$isFreeSpace;
        }

        return $fs;
    }

    static function fromString(string $str){
        $fs = new FileSystem();

        if($str === '') return $fs;

        $str = str_split(trim($str));

        foreach($str as $s){
            $label = $s == '.' ? -1 : (int)$s;
            $fs->blocks->push(new Block($label));
        }

        return $fs;
    }

    public function __toString(){
        $str = '';
        foreach($this->blocks as $block){
            $label = $block->label == -1 ? '.' : $block->label;
            for($i = 0; $i < $block->size; $i++){
                $str .= $label;
            }
        }
        return $str;
    }
}

class Part2{
    static function run(){
        $input = file_get_contents(__DIR__ . '/input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);

        $fs = FileSystem::fromMap($input);

        echo "{$fs}" . PHP_EOL;
        /*
        echo "Defragging..." . PHP_EOL;
        $fs->defrag();


        $checksum = $fs->checksum();


        echo "Part 2: $checksum" . PHP_EOL;
         */
    }
}

Part2::run();
