<?php
/**
  isPrime

  Tests whether or not the passed in value, $num, is a prime number.

  Function implementation is based on Timmetje's answer from StackOverflow.
  http://stackoverflow.com/questions/21726538/php-function-isprime-not-working

  IN: $num integer
  OUT: boolean
  THROWS typeerror if $num is not an int.
*/
function isPrime( int $num ) {
  if ( $num <= 1 )
    return false;

  if ( $num === 2 )
    return true;

  if ( $num % 2 === 0 )
    return false;

  for( $cnt = 3; $cnt <= ceil( sqrt( $num ) ); $cnt = $cnt + 2 ){
    if ( $num % $cnt === 0 )
      return false;
  }
  return true;
}

/**
  primesBelowNumber

  Returns the list of all
  IN: $num integer
  OUT: array of all prime numbers less than the passed in number.
  THROWS typeerror if $num is not an int.
*/
function primesBelowNumber( int $num ) {
  $ret = array();

  for( $cnt = 2; $cnt < $num; $cnt++ ) {
    if ( isPrime( $cnt ) )
      $ret[] = $cnt;
  }

  return $ret;
}

/**
  isCLI

  Tests to see if the user is using the command line.
  OUT: boolean, true when the user is using the command line.
*/
function isCLI() {
  return ( php_sapi_name() === 'cli' );
}

/**
  HTMLDisplay

  Show the results via HTML.
*/
function HTMLDisplay( $num, $list, $warning ) {
  echo "<html><body>";

  echo "<form method='get'>Integer: <input name='num' type='number' value='$num' /><input type='submit' /></form>";

  if ( $warning === '' && $num != '' ) {
    echo "<h2>Primes below $num</h2>";
    foreach( $list as $item ) {
        echo "$item<br />";
      }
  } else {
    echo "$warning<br />";
  }
  echo "</body></html>";
}

/**
  CLIDisplay

  Show the results via commmand line.
*/
function CLIDisplay( $num, $list, $warning ) {
  if ( $warning === '' && $num != '' ) {
    echo "Primes below $num\n";
    foreach( $list as $item ) {
        echo "$item\n";
      }
  } else {
    echo "$warning\n";
  }
}


$warning = "";
$num = "";
$list = null;

// Determine the passed in number, if given.
if ( isCLI() ) {
  if ( count( $argv ) > 1 ) {
    $num = $argv[1];
  } else {
    $warning = "Please supply an integer as a command line argument.";
  }
} else {
  if ( isset( $_REQUEST["num"] ) ) {
    $num = $_REQUEST["num"];
  }
}

// Calculate the list of primes below the given number.
if ( $num !== "" ) {
  if ( strval( intval( $num ) ) !== $num ) {
    $warning = "$num is not an integer. Please enter an integer.";
  } else {
    try {
      $num = intval( $num );
      $list = primesBelowNumber( $num );
    } catch ( TypeError $e ) {
      $warning = "$num is not an integer. Please enter an integer.";
      $num = "";
    }
  }
}

// Display the results
if ( isCLI() ) {
  CLIDisplay( $num, $list, $warning );
} else {
  HTMLDisplay( $num, $list, $warning );
}
