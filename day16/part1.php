<?php

    namespace AOC\D16\P1;

    use \SplPriorityQueue;

    include __DIR__ . '/utils.php'; // print grid,e.t.c.

    /**
     * helper function to convert a direction to a string
     *
     * @param Direction $dir
     * @return string
     */
    function dirToStr(Direction $dir):string{
        return match($dir){
            Direction::EAST => 'E',
            Direction::SOUTH => 'S',
            Direction::WEST => 'W',
            Direction::NORTH => 'N',
        };
    }

    class Grid
    {
        /**
         * @var string[][] $grid
         */
        private array $grid;

        /**
         * @param string[][] $grid
         */
        public function __construct(array $grid)
        {
            $this->grid = $grid;
        }

        /**
         * @return string[][]
         */
        public function getGrid(): array
        {
            return $this->grid;
        }


        public function setCell(Position $position, string $char): void
        {
            $this->grid[$position->y][$position->x] = $char;
        }

        public function cellAt(Position $position): string
        {
            return $this->grid[$position->y][$position->x];
        }

        public function findChar(string $char): Position
        {
            foreach ($this->grid as $y => $row) {
                foreach ($row as $x => $c) {
                    if ($c === $char) {
                        return new Position($x, $y);
                    }
                }
            }
            return new Position(-1, -1);
        }

        public function __toString(): string
        {
            $str = "";
            foreach ($this->grid as $row) {
                $str .= "'" . implode("", $row) . "'" . PHP_EOL;
            }
            return $str;
        }

        /**
         * Create a grid from a string
         * @param string $input
         * @return Grid
         */
        static function fromString(string $input): Grid
        {
            $grid = [];
            $lines = explode("\n", $input);
            foreach ($lines as $line) {
                if (empty($line)) {
                    continue;
                }
                $grid[] = str_split(trim($line));
            }
            return new Grid($grid);
        }
    }


    class Position
    {
        public int $x;
        public int $y;

        public function __construct(int $x, int $y)
        {
            $this->x = $x;
            $this->y = $y;
        }

        public function __toString(): string
        {
            return "({$this->x}, {$this->y})";
        }
    }

    class Vector
    {
        public int $x;
        public int $y;

        public function __construct(int $x, int $y)
        {
            $this->x = $x;
            $this->y = $y;
        }

        public function __toString(): string
        {
            return "({$this->x}, {$this->y})";
        }
    }

    enum Direction {
        case NORTH;
        case SOUTH;
        case EAST;
        case WEST;
    }

    class DirectionalVector
    {
        static function fromDirection(Direction $direction): Vector
        {
            switch ($direction) {
                case Direction::NORTH:
                    return new Vector(0, -1);
                case Direction::SOUTH:
                    return new Vector(0, 1);
                case Direction::EAST:
                    return new Vector(1, 0);
                case Direction::WEST:
                    return new Vector(-1, 0);
            }
        }
    }

    class PathPoint
    {
        public Position $position;
        public Direction $direction;
        /**
         * @var Position[] $path
         */
        public array $path;

        /**
         * PathPoint constructor.
         *
         * @param Position $position
         * @param Direction $direction
         * @param Position[] $path
         */
        public function __construct(Position $position, Direction $direction, array $path)
        {
            $this->position = $position;
            $this->direction = $direction;
            $this->path = $path;
        }
    }


    class PathFinder{
        /**
         * @var bool[] $visitedPositions
         */
        private array $visitedPositions;
        private \SplPriorityQueue $queue;// @phpstan-ignore-line  // phpstan is wrong here,imho
        private Grid $grid;
        private int $shortestPath;



        public function __construct(Grid $grid){
            $this->grid = $grid;
            $this->queue = new \SplPriorityQueue();
            $this->queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
            $this->visitedPositions = [];
            $this->shortestPath = PHP_INT_MAX;
        }

        private function getKey(Position $position, Direction $direction): string{
            $strDirection = match($direction){
                Direction::EAST => 'E',
                Direction::SOUTH => 'S',
                Direction::WEST => 'W',
                Direction::NORTH => 'N',
            };
            return "{$position->x},{$position->y}:{$strDirection}";
        }

        public function getShortestPathBetween(Position $start, Position $end): int{
            $shortestPath = PHP_INT_MAX;
            echo "Finding path between {$start} and {$end}" . PHP_EOL;
            $this->queue->insert([new PathPoint($start, Direction::EAST, [])], 0);


            /**
             * @var PathPoint[] $pathPoint
             */
            $pathPoint = [];
            $priority = PHP_INT_MAX;
            foreach($this->queue as ["data" => $pathPoint, "priority" => $priority]){

                /**
                 * These checks are for phpstan, and probably good practice anyways.
                 */
                if(!is_numeric($priority)){
                    throw new \Exception("Priority is not a number");
                }

                if(!is_array($pathPoint) || !$pathPoint[0] instanceof PathPoint){
                    var_dump($pathPoint);
                    throw new \Exception("PathPoint is not a PathPoint");
                }
                $pathPoint = $pathPoint[0];

                /**
                 * @var Position[] $path
                 */
                $path = $pathPoint->path;
                $path[] = $pathPoint->position;
                $visitedLookupKey = $this->getKey($pathPoint->position, $pathPoint->direction);
                $this->visitedPositions[$visitedLookupKey] = true;


                /**
                 * We need to fake our way to a minimum priority queue, out of the boc
                 * it's a maximum priority queue.
                 */
                $cost = -1 * $priority;


                // check for end before we start adding new points to the queue
                if($pathPoint->position == $end){
                    return (int) $cost;
                }


                // rotate left
                $left = match($pathPoint->direction){
                    Direction::NORTH => Direction::WEST,
                    Direction::WEST => Direction::SOUTH,
                    Direction::SOUTH => Direction::EAST,
                    Direction::EAST => Direction::NORTH,
                };

                // rotate right
                $right = match($pathPoint->direction){
                    Direction::NORTH => Direction::EAST,
                    Direction::EAST => Direction::SOUTH,
                    Direction::SOUTH => Direction::WEST,
                    Direction::WEST => Direction::NORTH,
                };

                /**
                 * @var Direction[] $directions
                 */
                $directions = [$left, $pathPoint->direction, $right];

                foreach($directions as $dir){
                    $strDirection = dirToStr($dir);
                    echo "Checking direction {$strDirection}" . PHP_EOL;
                    $dest = new Position(
                        $pathPoint->position->x + DirectionalVector::fromDirection($dir)->x,
                        $pathPoint->position->y + DirectionalVector::fromDirection($dir)->y
                    );

                    $nextCell = $this->grid->cellAt($dest);
                    $vLookupKey = $this->getKey($dest, $dir);
                    if(isset($this->visitedPositions[$vLookupKey]) || $nextCell === '#'){
                        continue;
                    }

                    echo "Adding {$dest} with direction {$strDirection} to queue" . PHP_EOL;

                    $newCost = 1;
                    if($dir != $pathPoint->direction){
                        $newCost = 1001;
                    }
                    $moveCost = $cost + $newCost;
                    $this->queue->insert(
                        [new PathPoint($dest, $dir, $path)],
                        -1 * $moveCost // see note above about faking a min priority queue
                    );
                }
            }

            throw new \Exception("No path found");
        }
    }

    function main():void{
        // $input = file_get_contents(__DIR__ . '/input.txt');
        $input = file_get_contents(__DIR__ . '/test-input.txt');
        if($input === false) exit("Input file not found" . PHP_EOL);
        $input = trim($input);

        $grid = Grid::fromString($input);
        $start = $grid->findChar('S');
        $end = $grid->findChar('E');

        $pathFinder = new PathFinder($grid);
        $ans = $pathFinder->getShortestPathBetween($start, $end);


        echo $grid . PHP_EOL;


        echo "Part 1: {$ans}" . PHP_EOL;
    }

    main();
