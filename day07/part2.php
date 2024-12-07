<?php
    declare(strict_types=1);

    define('DEBUG', true);

    enum Operation{
        case ADDITION;
        case MULTIPLICATION;
    }

    class Equation{
        /**
         * @var int
         */
        public $answer;

        /**
         * @var int[]
         */
        public $parts;

        /**
         * Equation constructor.
         * @param int $answer
         * @param int[] $parts
         */
        public function __construct(int $answer, array $parts){
            $this->answer = $answer;
            $this->parts = $parts;
        }

        public function isSolvable():bool{
            return $this->solve($this->answer, array_slice($this->parts, 1), $this->parts[0]);
		}

        /**
         * @param int $answer
         * @param int[] $parts
         * @param int $current
         * @return bool
         */
        public function solve(int $answer, array $parts, int $current): bool{
            if(empty($parts)){
                return $answer === $current;
            }

            $next = array_shift($parts);

            return ($this->solve($answer, $parts, $current + $next) ||
                $this->solve($answer, $parts, $current * $next) ||
                $this->solve($answer, $parts, intval("{$current}{$next}"))
            );
        }

        public function __toString(){
            return $this->answer . ': ' . implode(' ', $this->parts);
        }
    }


    /**
	 * @return array<Equation>
	 */
    function getEquationsFromInput(): array{
        $equations = [];

		/** @phpstan-ignore ternary.alwaysTrue */
        $rawInput = file_get_contents(DEBUG ? 'test-input.txt' : 'input.txt');

		if(!$rawInput) return [];

        $lines = explode(PHP_EOL, $rawInput);


        foreach($lines as $line){
            if(empty($line)){
                continue;
            }

            $sides = explode(':', $line);

            $equation = new Equation(
                intval($sides[0]),  // Answer
                array_map('intval', explode(' ', trim($sides[1]))) // Parts
            );

            $equations[] = $equation;
        }

        return $equations;
    }


    /* @var Equation[] $equations */
    $equations = getEquationsFromInput();
    $sum = 0;
    foreach($equations as $equation){
        if($equation->isSolvable()){
            $sum += $equation->answer;
        }
    }

    echo "Part 2 answer: ". $sum . PHP_EOL;
