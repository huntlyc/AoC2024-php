<?php
    declare(strict_types=1);

    namespace AOC\D08;

    /**
     * Class InputToGrid
     *
     * Takes a string input and converts it to a 2D array
     *
     * @package AOC\D08
     * @example
     * $input = <<<EOF
     * 123
     * 456
     * 789
     * EOF;
     * $grid = new InputToGrid($input);
     * $grid->getGrid(); // -> [['1','2','3'],['4','5','6'],['7','8','9']]
     */
    class InputToGrid{
        /**
         * @var string[][] $grid
         */
        private array $grid;

        public function __construct(string $input){
            $rows = explode(PHP_EOL, $input);
            foreach($rows as $row){
                if(empty($row)) continue;
                $this->grid[] = str_split($row);
            }
        }

        /**
         * @return string[][]
         */
        public function getGrid():array{
            return $this->grid;
        }

        /**
         * Prints the current grid
         */
        public function printGrid():void{
            InputToGrid::printAGrid($this->grid);
        }

        /**
         * Prints any grid
         * @param string[][] $grid
         */
        static public function printAGrid(array $grid):void{
            foreach($grid as $row){
                echo implode('', $row) . PHP_EOL;
            }
            echo PHP_EOL;
        }
    }


    /**
     * Class Point
     * Represents a point in a 2D grid
     *
     * @package AOC\D08
     */
    class Point{
        private int $x;
        private int $y;

        public function __construct(int $x, int $y){
            $this->x = $x;
            $this->y = $y;
        }

        public function getX():int{
            return $this->x;
        }

        public function getY():int{
            return $this->y;
        }

        public function __toString(){
            return "x:{$this->x},y:{$this->y}";
        }
    }


    /**
     * Class Direction
     * Represents a direction in a 2D grid
     * The direction is represented as a 2D vector with
     * the x and y components.
     *
     * Also provides method to get the direction as a normalized version of the direction
     *
     * @example
     * $direction = new Direction(1, 0);
     * $direction->getDirection(); // -> [1,0]
     *
     *
     * @package AOC\D08
     */
    class Direction{
        private int $x;
        private int $y;

        public function __construct(int $x, int $y){
            $this->x = $x;
            $this->y = $y;
        }

        /**
         * Returns the direction as a vector
         * @return int[]
         */
        public function getDirection():array{
            return [$this->x, $this->y];
        }

        /**
         * Returns the direction as a normalized vector
         * @return int[]
         */
        public function getDirectionNormalized():array{
            $x = $this->x;
            $y = $this->y;

            if($x !== 0){
                $x = intval($x / abs($x));
            }

            if($y !== 0){
                $y = intval($y / abs($y));
            }

            return [$x, $y];
        }

        public function __toString(){
            $d = $this->getDirectionNormalized();
            return "[{$d[0]},{$d[1]}]";
        }
    }


    /**
     * Class Antenna
     *
     * Represents an antenna with a frequency and a position in a 2D grid
     *
     * @package AOC\D08
     */
    class Antenna{
        private Point $point;
        private string $freq;

        public function __construct(int $x, int $y, string $freq){
            $this->point = new Point($x, $y);
            $this->freq = $freq;
        }

        public function getPoint():Point{
            return $this->point;
        }

        public function getFreq():string{
            return $this->freq;
        }

        public function __toString(){
            return "{$this->freq}:{x:{$this->point->getX()},y:{$this->point->getY()}}";
        }
    }


    /**
     * Class AntinodeCalculator
     * Calculates the antinode locations on a 2D grid
     *
     * @package AOC\D08
     */
    class AntinodeCalculator{
        /**
         * @var Point[]
         */
        private array $antinodeLocations;

        /**
         * @var Antenna[]
         */
        private array $antennas;

        /**
         * @var string[][]
         */
        private array $grid;


        /**
         * AntinodeCalculator constructor.
         * @param string[][] $grid
         */
        public function __construct(array $grid){
            $this->grid = $grid;
            $this->antinodeLocations = [];
            $this->antennas = [];
        }

        /**
         * @return Antenna[]
         */
        public function getAntennasFromGrid():array{
            $antennas = [];

            for($r = 0; $r < count($this->grid); $r++){
                for($c = 0; $c < count($this->grid[$r]); $c++){
                    if($this->grid[$r][$c] !== '.' && preg_match('/[0-9a-zA-Z]/', $this->grid[$r][$c])){
                        $antennas[] = new Antenna($c, $r, $this->grid[$r][$c]);
                    }
                }
            }

            return $antennas;
        }


        /**
         * Returns the direction from point a to point b
         * @param Point $a
         * @param Point $b
         * @return Direction
         */
        public function getDirection(Point $a, Point $b):Direction{
            $x = $b->getX() - $a->getX();
            $y = $b->getY() - $a->getY();

            return new Direction($x, $y);
        }

        /**
         * Checks if a point is out of bounds
         * @param Point $point
         * @return bool
         */
        public function isPointOutOfBounds(Point $point):bool{
            if($point->getX() < 0 || $point->getY() < 0) return true;
            if($point->getX() >= count($this->grid[0]) || $point->getY() >= count($this->grid)) return true;
            return false;
        }



        /**
         * Checks if a point is a valid antinode location
         * It must not be out of bounds and must not have an antenna with the same frequency (Part 1)
         * @param Point $point
         * @param string $freq
         * @return bool
         */
        public function isValidAntinodeLocation(Point $point, string $freq):bool{
            if($this->isPointOutOfBounds($point)) return false;

            // Already an antenna with the same frequency
            if($this->grid[$point->getY()][$point->getX()] === $freq) return false;

            return true;
        }

        /**
         * PART 1
         * Calculates the antinode locations for each antenna pair
         *
         * @return Point[]
         */
        public function calculateAntinodeLocations():array{
            $antinodeLocations = [];

            foreach($this->antennas as $currentAntenna){

                foreach($this->antennas as $antenna){

                    // skip if the same antenna
                    if($currentAntenna === $antenna) continue;

                    // skip if both antennas are not on the same frequency
                    if($currentAntenna->getFreq() != $antenna->getFreq()) continue;

                    $direction = $this->getDirection($currentAntenna->getPoint(), $antenna->getPoint());
                    $directionNorm = $direction->getDirectionNormalized();

                    $distance = [
                        abs($currentAntenna->getPoint()->getX() - $antenna->getPoint()->getX()),
                        abs($currentAntenna->getPoint()->getY() - $antenna->getPoint()->getY())
                    ];

                    $nextPoint = new Point(
                        $antenna->getPoint()->getX() + ($directionNorm[0] * $distance[0]),
                        $antenna->getPoint()->getY() + ($directionNorm[1] * $distance[1])
                    );


                    if($this->isValidAntinodeLocation($nextPoint, $antenna->getFreq())){
                        $antinodeLocations["{$nextPoint}"] = $nextPoint;
                    }

                }
            }

            $gridWithAntinode = $this->grid;
            foreach($antinodeLocations as $antinode){
                $gridWithAntinode[$antinode->getY()][$antinode->getX()] = '#';
            }


            return $antinodeLocations;
        }

        /**
         * PART 2
         * Calculates the antinode locations for each antenna pair
         * but also takes into account harmonics.
         *
         * Biggest differnce from part 1 is that an antinode can now also
         * be an antenna of the same frequency.
         *
         * @return Point[]
         */
        public function calculateAntinodeLocationsWithHarmonics():array{
            $antinodeLocations = [];

            foreach($this->antennas as $currentAntenna){

                foreach($this->antennas as $antenna){

                    // skip if the same antenna
                    if($currentAntenna === $antenna) continue;

                    // skip if both antennas are not on the same frequency
                    if($currentAntenna->getFreq() != $antenna->getFreq()) continue;


                    // because of harmonics, antena is also an antinode
                    $antinodeLocations["{$antenna->getPoint()}"] = $antenna->getPoint();

                    $direction = $this->getDirection($currentAntenna->getPoint(), $antenna->getPoint());
                    $directionNorm = $direction->getDirectionNormalized();

                    $distance = [
                        abs($currentAntenna->getPoint()->getX() - $antenna->getPoint()->getX()),
                        abs($currentAntenna->getPoint()->getY() - $antenna->getPoint()->getY())
                    ];


                    $startingPoint = new Point(
                        $antenna->getPoint()->getX(),
                        $antenna->getPoint()->getY()
                    );


                    while(true){
                        // Do first point forward
                        $nextPoint = new Point(
                            $startingPoint->getX() + ($directionNorm[0] * $distance[0]),
                            $startingPoint->getY() + ($directionNorm[1] * $distance[1])
                        );

                        if($this->isPointOutOfBounds($nextPoint)) break;

                        if(in_array($this->grid[$nextPoint->getY()][$nextPoint->getX()], ['.', $currentAntenna->getFreq()])){
                            $antinodeLocations["{$nextPoint}"] = $nextPoint;
                        }

                        $startingPoint = $nextPoint;
                    }
                }
            }

            $gridWithAntinode = $this->grid;
            foreach($antinodeLocations as $antinode){
                $gridWithAntinode[$antinode->getY()][$antinode->getX()] = '#';
            }

            return $antinodeLocations;
        }

        /**
         * Calculates the antinode locations
         * @param bool $withHarmonics - false for part 1, true for part 2
         * @return int
         */
        public function calculate(bool $withHarmonics = false):int{
            $this->antennas = $this->getAntennasFromGrid();
            if($withHarmonics){
                $this->antinodeLocations = $this->calculateAntinodeLocationsWithHarmonics();
            }else{
                $this->antinodeLocations = $this->calculateAntinodeLocations();
            }
            return count($this->antinodeLocations);
        }
    }


    class Part1{
        public static function run():void{
            $input = file_get_contents('input.txt');
            if(!$input) die('Failed to open input.txt' . PHP_EOL);

            $grid = (new InputToGrid($input))->getGrid();

            $antinodeCalculator = new AntinodeCalculator($grid);

            $numAntinodes = $antinodeCalculator->calculate();


            echo "Part 1: {$numAntinodes}" . PHP_EOL;
        }
    }

    class Part2{
        public static function run():void{
            $input = file_get_contents('input.txt');
            if(!$input) die('Failed to open input.txt' . PHP_EOL);

            $grid = (new InputToGrid($input))->getGrid();

            $antinodeCalculator = new AntinodeCalculator($grid);

            $numAntinodes = $antinodeCalculator->calculate($withHarmonics = true);

            echo "Part 2: {$numAntinodes}" . PHP_EOL;
        }
    }

    Part1::run();
    Part2::run();
