<?php
// Collection of utility fns shared by both parts

    /**
    * cls - Clear the screen
    * taken from https://gist.github.com/icebreaker/4130200
    */
    function cls(){
        print("\033[2J\033[;H");
    }



   /**
    * Colours a char for output onto the terminal
    .*/
    function colourChar(string $char, string $colour) {
        switch($colour) {
            case 'black':
                return "\033[0;30m$char\033[0m";
            case 'white':
                return "\033[1;37m$char\033[0m";
            case 'dark_grey':
                return "\033[1;30m$char\033[0m";
            case 'red':
                return "\033[0;31m$char\033[0m";
            case 'green':
                return "\033[0;32m$char\033[0m";
            case 'brown':
                return "\033[0;33m$char\033[0m";
            case 'yellow':
                return "\033[1;33m$char\033[0m";
            case 'blue':
                return "\033[0;34m$char\033[0m";
            case 'magenta':
                return "\033[0;35m$char\033[0m";
            case 'cyan':
                return "\033[0;36m$char\033[0m";
            case 'light_cyan':
                return "\033[1;36m$char\033[0m";
            case 'light_grey':
                return "\033[0;37m$char\033[0m";
            case 'light_red':
                return "\033[1;31m$char\033[0m";
            case 'light_green':
                return "\033[1;32m$char\033[0m";
            case 'light_blue':
                return "\033[1;34m$char\033[0m";
            case 'light_magenta':
                return "\033[1;35m$char\033[0m";
        }

        return "\033[1;37m^\033[0m"; // white
    }


    /**
     * output plain grid
     **/
    function printGrid(array $grid) {
        foreach($grid as $row) {
            echo implode('', $row) . PHP_EOL;
        }
        echo PHP_EOL;
        echo PHP_EOL;
    }


    /**
     * output grid with visited positions
     **/
    function printGridWithVisited(array $grid, array $visited, $pos = null) {
        $i = 0;
        foreach($visited as $v) {
            if(++$i === 1) {
                $grid[$v[0][0]][$v[0][1]] = '^';
            } else {
                $grid[$v[0][0]][$v[0][1]] = 'X';
            }
        }

        if($pos) {
            $grid[$pos[0]][$pos[1]] = '^';
        }

        foreach($grid as $row) {
            echo implode('', $row) . PHP_EOL;
        }

        echo PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Full technicolour neon dreams
     **/
    function printGridInColour(array $grid, array $visited, $pos = null) {

        $i = 0;
        foreach($visited as $v) {
            if(++$i === 1) {
                $grid[$v[0][0]][$v[0][1]] = colourChar('^', 'green');
            } else {
                if($grid[$v[0][0]][$v[0][1]] == 'L') {
                    $grid[$v[0][0]][$v[0][1]] = colourChar('X', 'yellow');
                }else{
                    $grid[$v[0][0]][$v[0][1]] = colourChar('X', 'light_blue');
                }
            }
        }

        if($pos) {
            $grid[$pos[0]][$pos[1]] = "\033[1;36m^\033[0m";
        }

        foreach($grid as $row) {
            foreach($row as $char) {
                if($char == '#') {
                    echo colourChar($char, 'red');
                    continue;
                }else if($char == '.') {
                    echo colourChar($char, 'light_grey');
                    continue;
                }else{
                    echo $char;
                }
            }
            echo PHP_EOL;

        }
    }

