<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="styles/main_style.css" type="text/css">
</head>
<title>Final Project</title>
<body>
  <h2>Final Project</h2>
  <h3>Team: DOLPHIN</h3>
  <h3>Dennis Lee, Gary Webb, Prashant Shrestha, Tyler Thrash</h3>
  <br><br><br>
  <div class="main-container">
  <div class="header">
    <a href="./index.php"><img class="logo" src="./pic/logo.png"></img></a>
    <div class="imageDiv">
	  <a href="./list_puzzles.php"><input class="headerButton" type="image" src="./pic/list.png"></a>
	  <a href="./add_puzzle.php"><input class="headerButton" type="image" src="./pic/addPuzzle.png"></a>
      <a href="./addWordPair.php"><input class="headerButton" type="image" src="./pic/addWord.png"></a>
      <a href="./login.php"><input class="headerButton" type="image" src="./pic/login.png"></a>
    </div>
    <div class="divTitle"><font class="font">Name in Synonyms</font></div>
    <br>
  </div>
	<font class="crumb">Name in Synonym <img src="./pic/arrow.png"/> Add Puzzle</font>
	<?php
 		require_once('create_puzzle.php');
	?> 
	<?PHP
		$input = "";
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			if (isset($_POST["puzzleWord"])) {	// User submited a word
				$input = validate_input($_POST["puzzleWord"]);	//
				if (strlen($input) > 0) {
					echo create_puzzle_table(strlen($input), $input);
				}
				else {
					echo '<script>alert("Invalid Input!");</script>' . create_word_input();
				}
				
			}
			else if(isset($_POST["word"])) {	// User submited puzzle
				$name = $size = "";
				$list = array();
				if(empty($_POST["word"]) && empty($_POST["size"])) {
					//should not happen
				} else {
					$name = strtolower(validate_input($_POST["word"]));
					$size = validate_input($_POST["size"]);
					$puzzleflag = FALSE;
					$errorflag = FALSE;
					for ($j = 0; $j < $size; $j++) {
						$tempWord = "word". $j;
						$tempClue = "clue" . $j;
						if(empty($_POST[$tempWord]) && empty($_POST[$tempClue])) {
							// left one of the Synonym or Clues empty
							// let user know of error
							if ($errorflag == FALSE) {
								echo create_puzzle_table($size, $name);
								echo display_error("Please give every synonym and clue a value!");
								$errorflag = TRUE;
							}
						}
						else {
							// valid input
							$word1 = strtolower(validate_input($_POST[$tempWord]));
							$word2 = strtolower(validate_input($_POST[$tempClue]));
							//echo "words: " . $word1. $word2;
							$char = substr($name, $j, 1);
							//echo "char: " . $char;
							$index = strpos($word1, $char);
							//echo "index: " . $index;
							if ($index === false){
								echo display_error("Char not found in word!");
							} else {
								if ($puzzleflag === FALSE) {
									// add to puzzle
									insertIntoPuzzle($name);
									$puzzleflag = TRUE;
								}
								// add to words
								insertIntoWords($word1, $word2);
								
								// add to char
								insertIntoCharacters(getMaxWordId($word1));
								insertIntoCharacters(getMaxWordId($word2));
								// add to puzzle words
									insertIntoPuzzleWords(getMaxPuzzleId($name), getMaxWordId($word1), $j);
								//array_push($list, $word1, $word2); // just for testing
							}
						}
					}
				}
				echo createHeader(validate_input($_POST["word"]));
				echo '<table class="main-tables" id="puzzle_table"><tr><th>Clue</th><th>Synonym</th></tr>';
				puzzleAddedTable();
				echo "</table>";
				echo createFooter();
			}
		}
		else {
			echo create_word_input();
		}
	?>
  </div>
</body>
</html>