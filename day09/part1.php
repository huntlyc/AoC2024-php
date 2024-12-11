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

    function __construct(int $label){
        $this->label = $label;
    }
}


class FileSystem{
    /**
     * @var SplDoublyLinkedList<Block>
     **/
    private $blocks;

    public function __construct(){
        $this->blocks = new SplDoublyLinkedList();
    }

    public function defrag():void{
        $this->blocks->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);


        $tail = 0;
        $head = $this->blocks->count() - 1;
        for ($this->blocks->rewind(); $this->blocks->valid(); $this->blocks->next()) {
            if($tail >= $head) break;

            echo "Head: $head, Tail: $tail" . PHP_EOL;
            $block = $this->blocks->current();

            echo "checking block: {$block->label} against " . $this->blocks->offsetGet($head)->label . PHP_EOL;


            if($block->label == -1){
                do{
                    $endBlock = $this->blocks->offsetGet($head);
                    if($endBlock->label !== -1) break;
                    $head--;
                }while($head > $tail);

                // move end block here
                $this->blocks->offsetSet($tail, $endBlock);

                // set end block to free space
                $this->blocks->offsetSet($head, new Block(-1));

                $head--;
            }
            $tail++;
        }
    }

    public function checksum():int{
        $checksum = 0;
        $this->blocks->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);

        $sumMultiplier = 0;
        for ($this->blocks->rewind(); $this->blocks->valid(); $this->blocks->next()) {
            if($this->blocks->current()->label == -1) continue;
            $checksum += $sumMultiplier * $this->blocks->current()->label;
            $sumMultiplier++;
        }
        return $checksum;
    }

    static function fromMap(string $map):FileSystem{
        $fs = new FileSystem();

        if($map === '') return $fs;

        $map = str_split(trim($map));

        $isFreeSpace = false;
        $fileLabel = 0;

        foreach($map as $m){
            $label = $isFreeSpace ? -1 : $fileLabel;
            $size = intval($m);

            for($i = 0; $i < $size; $i++){
                $fs->blocks->push(new Block((int)$label));
            }

            if($label >= 0) $fileLabel++;
            $isFreeSpace = !$isFreeSpace;
        }

        return $fs;
    }


    public function __toString(){
        $str = '';
        foreach($this->blocks as $block){
            $label = $block->label == -1 ? '.' : $block->label;
            $str .= $label;
        }
        return $str;
    }
}


class Part1{
    static function run():void{
        $input = file_get_contents(__DIR__ . '/test-input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);

        $fs = FileSystem::fromMap($input);

        echo "{$fs}" . PHP_EOL;
        echo "Defragging..." . PHP_EOL;
        $fs->defrag();

        echo "{$fs}" . PHP_EOL;

        $checksum = $fs->checksum();


        echo "Part 1: $checksum" . PHP_EOL;
    }
}

Part1::run();
