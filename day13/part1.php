<?php

declare(strict_types=1);

namespace AOC\D13\P1;

class Button{
    public int $xMove;
    public int $yMove;
    private string $identifier;

    public function __construct(int $xMove, int $yMove, string $identifier){
        $this->xMove = $xMove;
        $this->yMove = $yMove;
        $this->identifier = $identifier;
    }

    public function __toString(): string{
        return "{$this->identifier}: X:$this->xMove, Y:$this->yMove";
    }
}

class Point{
    public int $x;
    public int $y;

    public function __construct(int $x, int $y){
        $this->x = $x;
        $this->y = $y;
    }

    public function __toString(): string{
        return "($this->x,$this->y)";
    }
}

class Game{
    private Button $A;
    private Button $B;
    private Point $prizeCoord;


    public function __construct(Button $A, Button $B, Point $prizeCoord, int $prizeOffset = 0){
        $this->A = $A;
        $this->B = $B;

        // add 10000000000000 to each prize coord
        $prizeCoord->x += $prizeOffset;
        $prizeCoord->y += $prizeOffset;

        $this->prizeCoord = $prizeCoord;
    }

    public function play():int{

    /**
     * Cramer's rule
     * -------------
     * This works because the two buttons are moving in a straight line
     * and the prize is a point on that line.
     *
     * All we need to do is find the intersection of the two lines.
     *
     * The equation of a lines is given by:
     * A = a_x*b_y - a_y*b_x
     * B = a_x*p_y - a_y*p_x
     *
     *
     * In our case:
     *  - a_? = (x: A->xMove, y: A->yMove)
     *  - b_? = (x: B->xMove, y: B->yMove)
     *  - p_? = (x: prizeCoord->x, y: prizeCoord->y)
     *
     * A = (p_x*b_y - prize_y*b_x) / (a_x*b_y - a_y*b_x)
     * B = (a_x*p_y - a_y*p_x) / (a_x*b_y - a_y*b_x)
     */
        $a = (
            ($this->prizeCoord->x * $this->B->yMove - $this->prizeCoord->y * $this->B->xMove) /
            ($this->A->xMove * $this->B->yMove - $this->A->yMove * $this->B->xMove)
        );

        $b = (
            ($this->A->xMove * $this->prizeCoord->y - $this->A->yMove * $this->prizeCoord->x) /
            ($this->A->xMove * $this->B->yMove - $this->A->yMove * $this->B->xMove)
        );

        /**
         * If the result is not an integer, it means the prize is not on the line
         */
        if($a < 0 || $b < 0 || is_float($a) || is_float($b)){
            return 0;
        }

        // it costs 3 tokens to press A, 1 for B
        return (3 * $a) + $b;
    }

    public function __toString(): string{
        return "Game:\n\t$this->A\n\t$this->B\n\tPrize: $this->prizeCoord";
    }
}



/**
 * @param string $input
 * @param int $prizeOffset
 * @return array<Game>
 */
function gamesFromInput(string $input, $prizeOffset = 0):array{
    $games = [];
    $lines = explode("\n", $input);

    $a = null;
    $b = null;
    $p = null;

    $baseRE = ':\sX(\+|\-)(\d+),\sY(\+|\-)(\d+)';


    foreach($lines as $line){
        if(preg_match("/A{$baseRE}/", $line, $matches)){
           $x = (int) $matches[2] * ($matches[1] === '-' ? -1 : 1);
           $y = (int) $matches[4] * ($matches[3] === '-' ? -1 : 1);
           $a = new Button($x, $y, 'A');
        }else if(preg_match("/B{$baseRE}/", $line, $matches)){
           $x = (int) $matches[2] * ($matches[1] === '-' ? -1 : 1);

           $x = (int) $matches[2] * ($matches[1] === '-' ? -1 : 1);
           $y = (int) $matches[4] * ($matches[3] === '-' ? -1 : 1);
           $b = new Button($x, $y, 'B');
        }else if(preg_match("/Prize: X=(\d+), Y=(\d+)/", $line, $matches)){
            $p = new Point(
                (int) $matches[1],
                (int) $matches[2]
            );

           if($a === null || $b === null){
               exit("Invalid input" . PHP_EOL);
           }

           $games[] = new Game($a, $b, $p, $prizeOffset);

           $a = $b = $p = null;
        }
    }

    return $games;
}

function main():void{
    $input = file_get_contents(__DIR__ . '/input.txt');
    if($input === false) exit("Input file not found" . PHP_EOL);
    $input = trim($input);

    /**
     * @var array<Game> $games
     */
    $games = gamesFromInput($input);

    $ans = 0;
    foreach($games as $game){
        $tokens = $game->play();
        $ans += $tokens;
    }

    echo "Part 1: {$ans}" . PHP_EOL;

    /**
     * @var array<Game> $games
     */
    $games = gamesFromInput($input, $offset = 10000000000000);

    $ans = 0;
    foreach($games as $game){
        $tokens = $game->play();
        $ans += $tokens;
    }

    echo "Part 2: {$ans}" . PHP_EOL;
}

main();
