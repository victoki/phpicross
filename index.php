<!--
    Victoria King
    11/29/2020
    http://chelan.highline.edu/~victoki/116/finalProject/index.php
    Final Project - Picross - CSCI 116

    Uses PHP to play a randomly picked picross puzzle.

    DONE: If the squares of the puzzle are correct, stop allowing the person to click and report it is correct
    DONE: Tell the person what the puzzle picture is when they complete it.
    DONE: Allow person to reset and play again
    DONE: Nice to have: Pick a random puzzle from an assortment to solve.
    DONE: User can toggle the ability to mark grid square with indicators
    DONE: Menu, grid, css
    DONE: The main box menu can be reused
    DONE: Puzzle solution keys

    While I could use a for array to generate a lot of the code for the grid and answer keys, we are doing 11s at work
    and I have only a few lingering brain cells left at the end of the day and another final project for the SQL
    class to also finish.

-->

<?php
//This page requires usage of a session to store persisting variables.
//Otherwise, Post will clear them.

session_start();

//Enable all error reporting as part of assignment specs
error_reporting(E_ALL);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="picross.css" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="description" content="Picross Puzzle">
    <title>Picross</title>
</head>

<body>

<?php




// Initialize variables. If they are not set give them a default or empty value.
// Certain variables need to be set into session to persist, as post otherwise unsets variables.
if (!array_key_exists('mode',$_SESSION)) { $_SESSION['mode'] = 1; } // store the mode to toggle page parts. 1 - initial menu 2 - show grid mode 3 - player wins
if (!isset($hintOrReset)) { $hintOrReset = ''; } // This is the message to echo in the main button box about hints or reset.
if (!isset($tutorialOrQuit)) { $tutorialOrQuit = ''; } // This is the message that echos in main box asking user for tutorial or quit.
if (!isset($tutorial)) { $tutorial = 0; } // toggles if person wants to see a tutorial.
if (!array_key_exists('topKeys',$_SESSION)) { $_SESSION['topKeys'] = array(); } // stores the values to show as top solution key
if (!array_key_exists('sideKeys',$_SESSION)) { $_SESSION['sideKeys'] = array(); } // stores values to show as side solution key
if (!array_key_exists('modeToggle',$_SESSION)) { $_SESSION['modeToggle'] = 'fill'; } // stores value for mode toggle. defaults to fill.
if (!isset($action)) { $action = ''; } // captures post value for button menu
if (!isset($gridIndex)) { $gridIndex = ''; } //stores a Grid index number
if (!isset($gameOver)) { $gameOver = ''; } // default to fill mode
if (!array_key_exists('puzzleImgDesc',$_SESSION)) { $_SESSION['puzzleImgDesc'] = ''; } // Used to hold what the image is for later.

//Include the puzzle solution keys. In an include to avoid messing them up by accident.
include 'solutions.php';

// initialize the grid css classes
// contains info on what button is using what class for the mode toggles to affect the CSS display
// initialize all squares for 'clear' display - empty squares
// each index in this array is a css class. This is what drives boxes to change color.
if (!array_key_exists('gridButtonClass',$_SESSION)) {
    
    $classSetUp = array();
    
    // store the class array into session var to force it to persist a bit
    //If not debug mode, set up. If debug mode, set to the answer key.
    
    for ($i = 0; $i < 100; $i++) {
        $classSetUp[$i] = 'clear';
    }
    
    $_SESSION['gridButtonClass'] = $classSetUp;
}

// used to store the user's input into puzzle grid.
// Initialize to 1s (blank).
if (!array_key_exists('currentSolution',$_SESSION)) {
    $solutionArray = array();    

    for ($x = 0; $x < 100; $x++) {
        $solutionArray[$x] = 1;
    }

    $_SESSION['currentSolution'] = $solutionArray;

}

//Random puzzle function
//Selects one of three puzzles randomly from solutions.php and returns it as the function output.
function initPuzzle() {
    $randNum = mt_rand(1,3);

    //debug use only
    //$randNum = 4;
    //echo $randNum;

    //While I could have a for loop create the rows/cols keys time is pretty limmited
    //right now so I have to take a quicker route.
    switch ($randNum) {
        case 1:
            global $fish, $fishCols, $fishRows;
            $_SESSION['puzzleImgDesc'] = 'Fish';
            $_SESSION['sideKeys'] = $fishRows;
            $_SESSION['topKeys'] = $fishCols;
            return $fish;
            break;
        case 2:
            global $clock, $clockCols, $clockRows;
            $_SESSION['puzzleImgDesc'] = 'Clock';
            $_SESSION['sideKeys'] = $clockRows;
            $_SESSION['topKeys'] = $clockCols;
            return $clock;
            break;
        case 3:
            global $bird, $birdCols, $birdRows;
            $_SESSION['puzzleImgDesc'] = 'Bird';
            $_SESSION['sideKeys'] = $birdRows;
            $_SESSION['topKeys'] = $birdCols;
            return $bird;
            break;

        //debug only
        case 4:
            global $box, $boxCols, $boxRows;
            $_SESSION['puzzleImgDesc'] = 'Box';
            $_SESSION['sideKeys'] = $boxRows;
            $_SESSION['topKeys'] = $boxCols;
            return $box;
            break; 
    }
}

//Random Hint function
//Automatically fills in one randomly selected row by updating the css classes and player's solution
//Only called once at the start if you click 'yes'.
function giveHint() {
    //Fills in a random row at the start
    //pick a number between 0 and 9. This is the row number.
    $randRow = rand(0,9);
    $rowNum = $randRow; // unsure if it generates a new num every time so just making sure it is stored
    $currentCell = $rowNum;


    //start assigning the row values to player's solution values
    for ($i = 0; $i < 10; $i++) {
        //Get the id of the cell by multiplying the value generated by randRow by 10
        //and adding the value of $i.
        $currentCell = ($currentCell * 10) + $i;
        //set the current solution in the cell to what the current puzzle's value is for the cell
        $_SESSION['currentSolution'][$currentCell] = $_SESSION['puzzle'][$currentCell];

        //Update the button class array to show the hint.
        if ($_SESSION['puzzle'][$currentCell] == 1) {
            $_SESSION['gridButtonClass'][$currentCell] = 'clear';
        }
        elseif ($_SESSION['puzzle'][$currentCell] == 2) {
            $_SESSION['gridButtonClass'][$currentCell] = 'fill';
        }
        
        //reset currentCell for new loop iteration
        $currentCell = $rowNum;
    }
}

function endGridClear() {
    //sets all clear spaces in a grid to aliceblue in the end
    $currentCell = 0;


    //loop in a loop assigns aliceblue to all 'clear' squares to avoid a mix of tan and red marks.
    //This lets you look at the pattern much more clearly.
    for ($r = 0; $r < 10; $r++) {
        //Runs through 10 rows
        for ($i = 0; $i < 10; $i++) {
            //This loop processes cells in a row left to right.
            //Get the id of the cell by multiplying the value of currentCell by 10
            //and adding the value of $i.
            $currentCell = ($currentCell * 10) + $i;
            //set the current solution in the cell to what the current puzzle's value is for the cell
            $_SESSION['currentSolution'][$currentCell] = $_SESSION['puzzle'][$currentCell];

            //Update the button class array to show the hint.
            if ($_SESSION['puzzle'][$currentCell] == 1) {
                $_SESSION['gridButtonClass'][$currentCell] = 'endclear';
            }
        
            //reset currentCell for new loop iteration
            $currentCell = 0;
        }
    }
}

//Capture inputs to use a switch statement for button behaviours.
if (isset($_POST['action'])) {
    $action = $_POST['action'];
}

//Switch statement for $action that decides what to do with buttons
switch ($action) {
    //'Yes' button
    case 'Yes':
        //If in initial menu mode, start the game with a hint
        if ($_SESSION['mode'] == 1) {
            //Show grid
            $_SESSION['mode'] = 2;

            // pick a random Puzzle when you start, assign the data for the puzzle
            // and provide a hint to start with the selected puzzle.
            if (!array_key_exists('puzzle',$_SESSION)) {
                $randPuzzle = initPuzzle();
                $_SESSION['puzzle'] = $randPuzzle;
                giveHint();
            }
        }

        //If you are already in mode 2 with grid visible
        //Resets the puzzle state to fully blank
        elseif ($_SESSION['mode'] == 2) {
            $classSetUp = array();
            $solutionArray = array();

            //set arrays to 'clear' and '1'
            for ($i = 0; $i < 100; $i++) {
                $classSetUp[$i] = 'clear';
                $solutionArray[$i] = 1;
            }

            //Assign the values to clear the grid.
            $_SESSION['gridButtonClass'] = $classSetUp;
            $_SESSION['currentSolution'] = $solutionArray;
        }

    $tutorial = 0;
    break;

    //If in mode 1, it starts the game without a hint.
    case 'No':
        if ($_SESSION['mode'] == 1) {
            $_SESSION['mode'] = 2;

            // pick a random Puzzle when you start
            // does not start with a hint.
            if (!array_key_exists('puzzle',$_SESSION)) {
                $randPuzzle = initPuzzle();
                $_SESSION['puzzle'] = $randPuzzle;
            }
        }

        $tutorial = 0;
        break;

    //Shows tutorial/game infomation
    case 'Tutorial':
        $tutorial = 1;
        break;

    //Quits the puzzle and returns to main menu
    case 'Quit':
        $_SESSION['mode'] = 1;
        $gameOver = 1;
        $tutorial = 0;
        break;

    //These buttons control what mark is made when a person clicks the grid.
    //as a standin for an X the grid turns red
    case 'X':
        $_SESSION['modeToggle'] = 'x';
        break;

    //Fills a square in black if you are sure a square is there
    case 'Fill':
        $_SESSION['modeToggle'] = 'fill';
        break;

    //clears a square so it is neither red nor black.
    case 'Clear':
        $_SESSION['modeToggle'] = 'clear';
        break;
    
    // if none of the above its very likely a grid button click.
    default:
        // make absolutely sure it is a number for sure
        if (is_numeric($action)) {
            //create an index for use by subtracting 1 from value of action
            $gridIndex = $action - 1;

            //set the state of the class into the array that holds info about button states
            $_SESSION['gridButtonClass'][$gridIndex] = $_SESSION['modeToggle'];

            //Update the player's solution array to save the data
            if ($_SESSION['modeToggle'] == 'clear' || $_SESSION['modeToggle'] == 'x') {
                //if player is in clear or x mode, update value to 1
                $_SESSION['currentSolution'][$gridIndex] = 1;
            }
            if ($_SESSION['modeToggle'] == 'fill') {
                //if player is in fill mode, update value to 2
                $_SESSION['currentSolution'][$gridIndex] = 2;
            }

           //Use array_diff_assoc to check what the arrays have in common.
           $compareArray = array();
           $compareArray = array_diff_assoc($_SESSION['currentSolution'],$_SESSION['puzzle']);
           
           // Check if the solution matches the puzzle key.
           // If compareArray is empty, the two arrays are the same.
           if (empty($compareArray)) {
               //set all empty non-black squares from tan or red to aliceblue
               endGridClear();

               //Load into 'player wins' mode where grid is unclickable.
               $_SESSION['mode'] = 3;
           }
        }

}

//if gameOver = 1, someone hit the quit button. Run code to clean up.
if ($gameOver == 1) {
    //destroy the session and restart it.
    session_destroy();
    session_start();

    //Because you started a new session, you need to reinit the vars as if you just loaded in
    if (!array_key_exists('mode',$_SESSION)) { $_SESSION['mode'] = 1; } // store the mode to toggle page parts. 1 - initial menu 2 - show grid mode 3 - player wins
    if (!isset($hintOrReset)) { $hintOrReset = ''; } // This is the message to echo in the main button box about hints or reset.
    if (!isset($tutorialOrQuit)) { $tutorialOrQuit = ''; } // This is the message that echos in main box asking user for tutorial or quit.
    if (!isset($tutorial)) { $tutorial = 0; } // toggles if person wants to see a tutorial.
    if (!array_key_exists('topKeys',$_SESSION)) { $_SESSION['topKeys'] = array(); } // stores the values to show as top solution key
    if (!array_key_exists('sideKeys',$_SESSION)) { $_SESSION['sideKeys'] = array(); } // stores values to show as side solution key
    if (!array_key_exists('modeToggle',$_SESSION)) { $_SESSION['modeToggle'] = 'fill'; } // stores value for mode toggle. defaults to fill.
    if (!isset($action)) { $action = ''; } // captures post value for button menu
    if (!isset($gridIndex)) { $gridIndex = ''; } //stores a Grid index number
    if (!isset($gameOver)) { $gameOver = ''; } // default to fill mode
    if (!array_key_exists('puzzleImgDesc',$_SESSION)) { $_SESSION['puzzleImgDesc'] = ''; } // Used to hold what the image is for later.

    //re-initialize grid classes
    if (!array_key_exists('gridButtonClass',$_SESSION)) {
        $classSetUp = array();
    
        //reset all classes to clear
        for ($i = 0; $i < 100; $i++) {
            $classSetUp[$i] = 'clear';
        }
        $_SESSION['gridButtonClass'] = $classSetUp;
    }

     // reinit player solution to 1's
    if (!array_key_exists('currentSolution',$_SESSION)) {
        $solutionArray = array();    
        for ($x = 0; $x < 100; $x++) {
            $solutionArray[$x] = 1;
        }
        $_SESSION['currentSolution'] = $solutionArray;

    }
}

// if mode is 1 don't display the grid and wait for input.'
if ($_SESSION['mode'] == 1) {
    // Set messages to echo in the menu box.
    $hintOrReset = 'Would you like to play a 10x10 puzzle with a hint?';
    $tutorialOrQuit = 'Would you like to see a tutorial?';
    ?>

    <!-- Show explanation of project at start 
    Dont show if you click on tutorial though. -->

    <?php if ($tutorial != 1) { ?>
        <div class="directions">

        <h2>PHPicross</h2>
        <h3>CSCI 116 - Victoria King</h3>

            <p>This allows you to play a logic puzzle called 'Picross', otherwise known as a 'Nonogram'.
            If you are unfamiliar with Picross, please select the 'Tutorial' button to show directions.
            Otherwise to play, use the 'mode toggle' buttons to change how you interact with the grid. 
            Click the grid to put marks on each square. When the solution for the puzzle is correct, it 
            will toggle into a mode where it simply shows an unclickable version of the grid and tells you that 
            the solution is correct.</p>

            <p>To begin, click yes to have it load a puzzle and fill in a random row for you as a hint. Click no to play a puzzle without a hint. 
            It will pick one of three randomly selected puzzles, so it may not be the same every time.</p>

            <p>Some variables use $_SESSION to persist as POST destroys variable values. To reset these sticky variable values, hit 'Quit' or close the browser.</p>

            <p>Good luck. Can you solve it?</p>
        </div>
        <br>


    <?php
    }
}

//Puzzle grid mode
if ($_SESSION['mode'] == 2) {
    // Set messages to echo in the during puzzle menu box
    $hintOrReset = 'Would you like to reset your current puzzle and start over?';
    $tutorialOrQuit = 'Do you want to quit your current puzzle?';

    //This is the 10x10 grid

    ?>

    <div class="grid_container">
                <div class="info_block">
                    <h3>Picross Puzzle</h3>
                    <p><label>Click to toggle modes:</label><br>
                    <form action="" method="post">
                        <input type="submit" class="xmode" name="action" value="X">
                        <input type="submit" class="fillmode" name="action" value="Fill">
                        <input type="submit" class="clearmode" name="action" value="Clear">
                    </form></p>
                    <?php if (isset($_SESSION['modeToggle'])) { ?>
                    <p>Current mode: <?php echo $_SESSION['modeToggle']; ?> </p>
                    <?php } ?>
                </div>

                <div class="grid">
                    <div class="grid_button">
                        <form action="" method="post">
                            <!-- top answer key -->
                            <div class="top_key_10">
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][0] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][1] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][2] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][3] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][4] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][5] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][6] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][7] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][8] ?></span>
                                <span class="top_key_text"><?php echo $_SESSION['topKeys'][9] ?></span>
                            </div>

                            <!-- grid rows go left to right for ids with a key for that row to the left -->
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][0] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][0]; ?>" name="action" value="1"></li><!-- --> <!-- force remove whitespace -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][1]; ?>" name="action" value="2"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][2]; ?>" name="action" value="3"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][3]; ?>" name="action" value="4"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][4]; ?>" name="action" value="5"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][5]; ?>" name="action" value="6"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][6]; ?>" name="action" value="7"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][7]; ?>" name="action" value="8"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][8]; ?>" name="action" value="9"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][9]; ?>" name="action" value="10"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][1] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][10]; ?>" name="action" value="11"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][11]; ?>" name="action" value="12"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][12]; ?>" name="action" value="13"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][13]; ?>" name="action" value="14"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][14]; ?>" name="action" value="15"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][15]; ?>" name="action" value="16"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][16]; ?>" name="action" value="17"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][17]; ?>" name="action" value="18"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][18]; ?>" name="action" value="19"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][19]; ?>" name="action" value="20"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][2] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][20]; ?>" name="action" value="21"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][21]; ?>" name="action" value="22"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][22]; ?>" name="action" value="23"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][23]; ?>" name="action" value="24"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][24]; ?>" name="action" value="25"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][25]; ?>" name="action" value="26"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][26]; ?>" name="action" value="27"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][27]; ?>" name="action" value="28"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][28]; ?>" name="action" value="29"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][29]; ?>" name="action" value="30"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][3] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][30]; ?>" name="action" value="31"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][31]; ?>" name="action" value="32"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][32]; ?>" name="action" value="33"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][33]; ?>" name="action" value="34"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][34]; ?>" name="action" value="35"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][35]; ?>" name="action" value="36"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][36]; ?>" name="action" value="37"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][37]; ?>" name="action" value="38"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][38]; ?>" name="action" value="39"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][39]; ?>" name="action" value="40"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][4] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][40]; ?>" name="action" value="41"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][41]; ?>" name="action" value="42"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][42]; ?>" name="action" value="43"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][43]; ?>" name="action" value="44"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][44]; ?>" name="action" value="45"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][45]; ?>" name="action" value="46"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][46]; ?>" name="action" value="47"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][47]; ?>" name="action" value="48"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][48]; ?>" name="action" value="49"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][49]; ?>" name="action" value="50"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][5] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][50]; ?>" name="action" value="51"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][51]; ?>" name="action" value="52"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][52]; ?>" name="action" value="53"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][53]; ?>" name="action" value="54"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][54]; ?>" name="action" value="55"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][55]; ?>" name="action" value="56"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][56]; ?>" name="action" value="57"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][57]; ?>" name="action" value="58"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][58]; ?>" name="action" value="59"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][59]; ?>" name="action" value="60"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][6] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][60]; ?>" name="action" value="61"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][61]; ?>" name="action" value="62"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][62]; ?>" name="action" value="63"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][63]; ?>" name="action" value="64"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][64]; ?>" name="action" value="65"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][65]; ?>" name="action" value="66"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][66]; ?>" name="action" value="67"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][67]; ?>" name="action" value="68"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][68]; ?>" name="action" value="69"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][69]; ?>" name="action" value="70"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][7] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][70]; ?>" name="action" value="71"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][71]; ?>" name="action" value="72"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][72]; ?>" name="action" value="73"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][73]; ?>" name="action" value="74"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][74]; ?>" name="action" value="75"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][75]; ?>" name="action" value="76"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][76]; ?>" name="action" value="77"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][77]; ?>" name="action" value="78"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][78]; ?>" name="action" value="79"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][79]; ?>" name="action" value="80"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][8] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][80]; ?>" name="action" value="81"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][81]; ?>" name="action" value="82"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][82]; ?>" name="action" value="83"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][83]; ?>" name="action" value="84"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][84]; ?>" name="action" value="85"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][85]; ?>" name="action" value="86"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][86]; ?>" name="action" value="87"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][87]; ?>" name="action" value="88"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][88]; ?>" name="action" value="89"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][89]; ?>" name="action" value="90"></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"><?php echo $_SESSION['sideKeys'][9] ?></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][90]; ?>" name="action" value="91"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][91]; ?>" name="action" value="92"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][92]; ?>" name="action" value="93"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][93]; ?>" name="action" value="94"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][94]; ?>" name="action" value="95"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][95]; ?>" name="action" value="96"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][96]; ?>" name="action" value="97"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][97]; ?>" name="action" value="98"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][98]; ?>" name="action" value="99"></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][99]; ?>" name="action" value="100"></li><!-- -->
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>
    <br>
    <?php
}

//player wins mode - buttons can't be clicked and just shows the puzzle pattern.
//Menu only has Quit button.
if ($_SESSION['mode'] == 3) {
    $tutorialOrQuit = 'Press Quit to return to the start.';
    //This is the 10x10 grid

    ?>

    <div class="grid_container">
                <div class="info_block">
                    <h3>Congrats! You solved it!</h3>
                    <!-- tells you what the picture is supposed to be. Set when the puzzle is loaded. -->
                    <p><label>It seems to be a <?php echo $_SESSION['puzzleImgDesc'] ?>.</label><br>
                    </p>
                </div>

                <div class="grid">
                    <div class="grid_button">
                        <form action="" method="post">

                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][0]; ?>" name="playerwin" value="1" disabled></li><!-- --> <!-- force remove whitespace -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][1]; ?>" name="playerwin" value="2" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][2]; ?>" name="playerwin" value="3" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][3]; ?>" name="playerwin" value="4" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][4]; ?>" name="playerwin" value="5" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][5]; ?>" name="playerwin" value="6" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][6]; ?>" name="playerwin" value="7" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][7]; ?>" name="playerwin" value="8" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][8]; ?>" name="playerwin" value="9" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][9]; ?>" name="playerwin" value="10" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][10]; ?>" name="playerwin" value="11" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][11]; ?>" name="playerwin" value="12" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][12]; ?>" name="playerwin" value="13" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][13]; ?>" name="playerwin" value="14" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][14]; ?>" name="playerwin" value="15" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][15]; ?>" name="playerwin" value="16" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][16]; ?>" name="playerwin" value="17" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][17]; ?>" name="playerwin" value="18" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][18]; ?>" name="playerwin" value="19" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][19]; ?>" name="playerwin" value="20" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][20]; ?>" name="playerwin" value="21" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][21]; ?>" name="playerwin" value="22" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][22]; ?>" name="playerwin" value="23" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][23]; ?>" name="playerwin" value="24" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][24]; ?>" name="playerwin" value="25" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][25]; ?>" name="playerwin" value="26" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][26]; ?>" name="playerwin" value="27" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][27]; ?>" name="playerwin" value="28" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][28]; ?>" name="playerwin" value="29" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][29]; ?>" name="playerwin" value="30" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][30]; ?>" name="playerwin" value="31" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][31]; ?>" name="playerwin" value="32" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][32]; ?>" name="playerwin" value="33" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][33]; ?>" name="playerwin" value="34" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][34]; ?>" name="playerwin" value="35" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][35]; ?>" name="playerwin" value="36" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][36]; ?>" name="playerwin" value="37" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][37]; ?>" name="playerwin" value="38" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][38]; ?>" name="playerwin" value="39" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][39]; ?>" name="playerwin" value="40" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][40]; ?>" name="playerwin" value="41" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][41]; ?>" name="playerwin" value="42" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][42]; ?>" name="playerwin" value="43" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][43]; ?>" name="playerwin" value="44" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][44]; ?>" name="playerwin" value="45" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][45]; ?>" name="playerwin" value="46" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][46]; ?>" name="playerwin" value="47" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][47]; ?>" name="playerwin" value="48" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][48]; ?>" name="playerwin" value="49" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][49]; ?>" name="playerwin" value="50" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][50]; ?>" name="playerwin" value="51" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][51]; ?>" name="playerwin" value="52" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][52]; ?>" name="playerwin" value="53" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][53]; ?>" name="playerwin" value="54" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][54]; ?>" name="playerwin" value="55" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][55]; ?>" name="playerwin" value="56" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][56]; ?>" name="playerwin" value="57" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][57]; ?>" name="playerwin" value="58" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][58]; ?>" name="playerwin" value="59" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][59]; ?>" name="playerwin" value="60" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][60]; ?>" name="playerwin" value="61" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][61]; ?>" name="playerwin" value="62" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][62]; ?>" name="playerwin" value="63" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][63]; ?>" name="playerwin" value="64" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][64]; ?>" name="playerwin" value="65" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][65]; ?>" name="playerwin" value="66" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][66]; ?>" name="playerwin" value="67" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][67]; ?>" name="playerwin" value="68" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][68]; ?>" name="playerwin" value="69" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][69]; ?>" name="playerwin" value="70" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][70]; ?>" name="playerwin" value="71" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][71]; ?>" name="playerwin" value="72" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][72]; ?>" name="playerwin" value="73" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][73]; ?>" name="playerwin" value="74" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][74]; ?>" name="playerwin" value="75" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][75]; ?>" name="playerwin" value="76" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][76]; ?>" name="playerwin" value="77" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][77]; ?>" name="playerwin" value="78" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][78]; ?>" name="playerwin" value="79" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][79]; ?>" name="playerwin" value="80" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][80]; ?>" name="playerwin" value="81" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][81]; ?>" name="playerwin" value="82" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][82]; ?>" name="playerwin" value="83" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][83]; ?>" name="playerwin" value="84" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][84]; ?>" name="playerwin" value="85" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][85]; ?>" name="playerwin" value="86" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][86]; ?>" name="playerwin" value="87" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][87]; ?>" name="playerwin" value="88" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][88]; ?>" name="playerwin" value="89" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][89]; ?>" name="playerwin" value="90" disabled></li><!-- -->
                                </ul>
                            </div>
                            <div class="grid_row">
                                <ul>
                                    <div class="side_key_10"></div>
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][90]; ?>" name="playerwin" value="91" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][91]; ?>" name="playerwin" value="92" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][92]; ?>" name="playerwin" value="93" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][93]; ?>" name="playerwin" value="94" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][94]; ?>" name="playerwin" value="95" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][95]; ?>" name="playerwin" value="96" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][96]; ?>" name="playerwin" value="97" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][97]; ?>" name="playerwin" value="98" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][98]; ?>" name="playerwin" value="99" disabled></li><!-- -->
                                    <li><input type="submit" class="<?php echo $_SESSION['gridButtonClass'][99]; ?>" name="playerwin" value="100" disabled></li><!-- -->
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
    </div>
    <br>
    <?php
}

//If tutorial is set to 1, show some helpful information on how to solve a puzzle.
if ($tutorial == 1) { ?>
    <div class="video_container">
        <h2>Tutorial</h2>
        <div class="video">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/d-I5Ng2oYyM" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><!-- -->
        </div>
        <div class="directions">
            <ul>
                <li><a href="https://en.wikipedia.org/wiki/Nonogram">About Nonograms (Wikipedia)</a></li>
                <li>When you select to play with a hint, one random row will be filled in to start.</li>
                <li>If you opt for no hint, nothing will be filled in when you start.</li>
                <li>The puzzle is randomly selected out of a small pool of possible puzzles.</li>
                <li>By default, the mode is 'fill', which marks the grid black.</li>
                <li>To mark an X, click the button labelled 'X' to toggle into 'X' mode. This is provided to help you remember what cannot possibly be filled.
                This mode works by turning a square red as a reminder that it is definitely a square that could not possibly be filled based on the clues.</li>
                <li>If you want to clear a square, toggle into 'clear' mode. This will remove black and red marks.</li>
                <li>If you are sure there is a box in a location, toggle into 'fill' mode and click on the grid to mark it black.</li>
                <li>You win when you have filled the correct boxes in the grid for the puzzle solution.</li>
            </ul>
        </div>
    </div>
<?php
}

// This is the box that asks if you want a hint if you are not in a puzzle or to reset if you are in a puzzle.
// It also asks (if you haven't started) if you want to play the tutoral, or if you want to quit your current puzzle (you you did start).
?>

    <div class="main_content">

    <!-- Messages based on value of $_SESSION['mode']
        1 - user has quit puzzle or just loaded in (default)
        2 - 10x10 grid
        
        if 1 hintOrReset asks user to play with a hint OR if in puzzle if they want to quit 
        if mode 3 don't show yes or no -->
    <form action="index.php" method="post">
    <div class="input_button">
    <?php if ($_SESSION['mode'] != 3) { ?>
        <h2><?php echo $hintOrReset ?></h2>
        <input type="submit" name="action" value="Yes">
    </div>
    <br>
    <?php } ?>

    <!-- if in grid mode don't show 'no' to reset. -->
    <?php if ($_SESSION['mode'] != 2 && $_SESSION['mode'] != 3) { ?>
        <div class="input_button">
            <input type="submit" name="action" value="No">
        </div>
    <?php } ?>

    <!-- Message varies based on mode toggle
        if user is not in a puzzle, it will ask them iff they want to see a Tutorial
        which will send them to an excellent youtube video tutorial on nonograms.
        If user is in the 10x10 puzzle mode, it will quit the puzzle and return mode to '1'
        
        Additionally the button text is different to capture the behaviours using switch statement.-->

    <?php if ($_SESSION['mode'] == 1 && $tutorial != 1) { ?>
        <h2><?php echo $tutorialOrQuit ?></h2>
        <div class="input_button">
            <input type="submit" name="action" value="Tutorial">
        </div>
    <?php }

    elseif ($_SESSION['mode'] != 1 && $tutorial != 1) { ?>
        <h2><?php echo $tutorialOrQuit ?></h2>
        <div class="input_button">
            <input type="submit" name="action" value="Quit">
        </div>
        <br>
    <?php } ?>
    </form>
    <br>
    <p><a href="../index.html">Return to Index</a></p>
    <p><a href="https://validator.w3.org/nu/?doc=https://chelan.highline.edu/~victoki/116/finalProject/index.php" target="_blank">Validate</a></p>
</div>

</body>
</html>