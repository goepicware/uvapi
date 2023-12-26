<?php

/************************************************************************************************************
 * This function has been placed in the public domain as detailed at:                                       *
 * http://www.braemoor.co.uk/software/index.shtml                                                           *
 *                                                                                                          *
 * "You are welcome to download and use any of this software, but please note that:                         *
 * All software is provided as freeware for personal or commercial use without obligation by either party.  *
 * The author will not accept responsibility for any problems that may be incurred by use of this software, *
 * although any errors reported will be corrected as soon as possible.                                      *
 * Re-distribution of this software is NOT permitted without explicit permission."                          *
 ************************************************************************************************************

This routine checks the credit card number. The following checks are made:

1. A number has been provided
2. The number is a right length for the card
3. The number has an appropriate prefix for the card
4. The number has a valid modulus 10 number check digit if required

If the validation fails an error is reported.

The structure of credit card formats was gleaned from a variety of sources on 
the web, although the best is probably on Wikepedia ("Credit card number"):

  http://en.wikipedia.org/wiki/Credit_card_number

Input parameters:
            cardnumber          number on the card
            cardname            name of card as defined in the card list below
Output parameters:
            cardnumber          number on the card
            cardname            name of card as defined in the card list below

Author:     John Gardner        webmister@braemoor.co.uk
Date:       4th January 2005
Updated:    26th February 2005  additional credit cards added
            1st July 2006       multiple definition of Discovery card removed
            27th Nov. 2006      Additional cards added from Wikipedia
            8th Dec 2007		    Problem with Solo card definition corrected
            18th Jan 2008		    Support for 18 digit Maestro cards added
            26th Nov 2008       Support for 19 digit Maestro cards added
            19th June 2009      Support for Laser debit cards
            11th September 2010 Improved support for Diner Club cards by Noe Leon
            27th October 2011   Minor updates by Neil Cresswell (neil@cresswell.net):
                                  * VISA now only 16 digits as 13 digits version withdrawn and no longer in circulation
                                  * Deprecated eregi replaced by preg_match in two places
                                  * Deprecated split replaced by explode in two places
            10th April 2012     New matches for Maestro, Diners Enroute and Switch
            17th October 2012   Diners Club prefix 38 not encoded
   
if (isset($_GET['submitted'])) {
  if (checkCreditCard ($_GET['CardNumber'], $_GET['CardType'], $ccerror, $ccerrortext)) {
    $ccerrortext = 'This card has a valid format';
  }
}

==============================================================================*/


function checkCreditCard ($cardnumber, $cardname, &$errornumber, &$errortext) {

  // Define the cards we support. You may add additional card types.
  
  //  Name:      As in the selection box of the form - must be same as user's
  //  Length:    List of possible valid lengths of the card number for the card
  //  prefixes:  List of possible prefixes for the card
  //  checkdigit Boolean to say whether there is a check digit
  
  // Don't forget - all but the last array definition needs a comma separator!
  
  $cards = array (  array ('name' => 'American Express', 
                          'length' => '15', 
                          'prefixes' => '34,37',
                          'checkdigit' => true
                         ),
                   array ('name' => 'Diners Club Carte Blanche', 
                          'length' => '14', 
                          'prefixes' => '300,301,302,303,304,305',
                          'checkdigit' => true
                         ),
                   array ('name' => 'Diners Club', 
                          'length' => '14,16',
                          'prefixes' => '36,38,54,55',
                          'checkdigit' => true
                         ),
                   array ('name' => 'Discover', 
                          'length' => '16', 
                          'prefixes' => '6011,622,64,65',
                          'checkdigit' => true
                         ),
                   array ('name' => 'Diners Club Enroute', 
                          'length' => '15', 
                          'prefixes' => '2014,2149',
                          'checkdigit' => true
                         ),
                   array ('name' => 'JCB', 
                          'length' => '16', 
                          'prefixes' => '35',
                          'checkdigit' => true
                         ),
                   array ('name' => 'Maestro', 
                          'length' => '12,13,14,15,16,18,19', 
                          'prefixes' => '5018,5020,5038,6304,6759,6761,6762,6763',
                          'checkdigit' => true
                         ),
                   array ('name' => 'MasterCard', 
                          'length' => '16', 
                          'prefixes' => '51,52,53,54,55',
                          'checkdigit' => true
                         ),
                   array ('name' => 'Solo', 
                          'length' => '16,18,19', 
                          'prefixes' => '6334,6767',
                          'checkdigit' => true
                         ),
                   array ('name' => 'Switch', 
                          'length' => '16,18,19', 
                          'prefixes' => '4903,4905,4911,4936,564182,633110,6333,6759',
                          'checkdigit' => true
                         ),
                   array ('name' => 'VISA', 
                          'length' => '16', 
                          'prefixes' => '4',
                          'checkdigit' => true
                         ),
                   array ('name' => 'VISA Electron', 
                          'length' => '16', 
                          'prefixes' => '417500,4917,4913,4508,4844',
                          'checkdigit' => true
                         ),
                   array ('name' => 'LaserCard', 
                          'length' => '16,17,18,19', 
                          'prefixes' => '6304,6706,6771,6709',
                          'checkdigit' => true
                         )
                );

  $ccErrorNo = 0;

  $ccErrors [0] = "Unknown card type";
  $ccErrors [1] = "No card number provided";
  $ccErrors [2] = "Credit card number has invalid format";
  $ccErrors [3] = "Credit card number is invalid";
  $ccErrors [4] = "Credit card number is wrong length";
               
  // Establish card type
  $cardType = -1;
  for ($i=0; $i<sizeof($cards); $i++) {

    // See if it is this card (ignoring the case of the string)
    if (strtolower($cardname) == strtolower($cards[$i]['name'])) {
      $cardType = $i;
      break;
    }
  }
  
  // If card type not found, report an error
  if ($cardType == -1) {
     $errornumber = 0;     
     $errortext = $ccErrors [$errornumber];
     return false; 
  }
   
  // Ensure that the user has provided a credit card number
  if (strlen($cardnumber) == 0)  {
     $errornumber = 1;     
     $errortext = $ccErrors [$errornumber];
     return false; 
  }
  
  // Remove any spaces from the credit card number
  $cardNo = str_replace (' ', '', $cardnumber);  
   
  // Check that the number is numeric and of the right sort of length.
  if (!preg_match("/^[0-9]{13,19}$/",$cardNo))  {
     $errornumber = 2;     
     $errortext = $ccErrors [$errornumber];
     return false; 
  }
       
  // Now check the modulus 10 check digit - if required
  if ($cards[$cardType]['checkdigit']) {
    $checksum = 0;                                  // running checksum total
    $mychar = "";                                   // next char to process
    $j = 1;                                         // takes value of 1 or 2
  
    // Process each digit one by one starting at the right
    for ($i = strlen($cardNo) - 1; $i >= 0; $i--) {
    
      // Extract the next digit and multiply by 1 or 2 on alternative digits.      
      $calc = $cardNo{$i} * $j;
    
      // If the result is in two digits add 1 to the checksum total
      if ($calc > 9) {
        $checksum = $checksum + 1;
        $calc = $calc - 10;
      }
    
      // Add the units element to the checksum total
      $checksum = $checksum + $calc;
    
      // Switch the value of j
      if ($j ==1) {$j = 2;} else {$j = 1;};
    } 
  
    // All done - if checksum is divisible by 10, it is a valid modulus 10.
    // If not, report an error.
    if ($checksum % 10 != 0) {
     $errornumber = 3;     
     $errortext = $ccErrors [$errornumber];
     return false; 
    }
  }  

  // The following are the card-specific checks we undertake.

  // Load an array with the valid prefixes for this card
  $prefix = explode(',',$cards[$cardType]['prefixes']);
      
  // Now see if any of them match what we have in the card number  
  $PrefixValid = false; 
  for ($i=0; $i<sizeof($prefix); $i++) {
    $exp = '/^' . $prefix[$i] . '/';
    if (preg_match($exp,$cardNo)) {
      $PrefixValid = true;
      break;
    }
  }
      
  // If it isn't a valid prefix there's no point at looking at the length
  if (!$PrefixValid) {
     $errornumber = 3;     
     $errortext = $ccErrors [$errornumber];
     return false; 
  }
    
  // See if the length is valid for this card
  $LengthValid = false;
  $lengths = explode(',',$cards[$cardType]['length']);
  for ($j=0; $j<sizeof($lengths); $j++) {
    if (strlen($cardNo) == $lengths[$j]) {
      $LengthValid = true;
      break;
    }
  }
  
  // See if all is OK by seeing if the length was valid. 
  if (!$LengthValid) {
     $errornumber = 4;     
     $errortext = $ccErrors [$errornumber];
     return false; 
  };   
  
  // The credit card is in the required format.
  return true;
}
/*============================================================================*/


/*
 This File licensed under the terms of the BSD License:

Copyright (c) 2007, Tux IT Services
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this
list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice,
this list of conditions and the following disclaimer in the documentation
and/or other materials provided with the distribution.
* Neither the name of the Tux IT Services nor the names of its contributors
may be used to endorse or promote products derived from this software
without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
		PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
		PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
		NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/


/**
 * Credit Card Functions
*
* This helper module contains functions which can be used to manipulate credit
* card numbers and related information.
*
* @package    CodeIgniter
* @subpackage    Helpers
* @category    Helpers
* @author    Jim O'Halloran
*/


/**
 * Truncates a card number retaining only the first 4 and the last 4 digits.  It then returns the truncated form.
*
* @param string The card number to truncate.
* @return string The truncated card number.
*/
function truncate_card($card_num) {
	$padsize = (strlen($card_num) < 7 ? 0 : strlen($card_num) - 7);
	return substr($card_num, 0, 4) . str_repeat('X', $padsize). substr($card_num, -3);
}


/**
 * Validates a card expiry date.  Finds the midnight on first day of the following
 * month and ensures that is greater than the current time (cards expire at the
 * end of the printed month).  Assumes basic sanity checks have already been performed
 * on month/year (i.e. length, numeric, etc).
 *
 * @param integer The expiry month shown on the card.
 * @param integer The expiry year printed on the card.
 * @return boolean Returns true if the card is still valid, false if it has expired.
 */
function card_expiry_valid($month, $year) {
	$expiry_date = mktime(0, 0, 0, ($month + 1), 1, $year);
	return ($expiry_date > time());
}


/**
 * Strips all non-numerics from the card number.
 *
 * @param string The card number to clean up.
 * @return string The stripped down card number.
 */
function card_number_clean($number) {
	return preg_replace("/[^0-9]/", "", $number);
}


/**
 * Uses the Luhn algorithm (aka Mod10) <http://en.wikipedia.org/wiki/Luhn_algorithm>
 * to perform basic validation of a credit card number.
 *
 * @param string The card number to validate.
 * @return boolean True if valid according to the Luhn algorith, false otherwise.
 */
function card_number_valid ($card_number) {
	$card_number = strrev(card_number_clean($card_number));
	$sum = 0;

	for ($i = 0; $i < strlen($card_number); $i++) {
		$digit = substr($card_number, $i, 1);

		// Double every second digit
		if ($i % 2 == 1) {
			$digit *= 2;
		}

		// Add digits of 2-digit numbers together
		if ($digit > 9)    {
			$digit = ($digit % 10) + floor($digit / 10);
		}

		$sum += $digit;
	}

	// If the total has no remainder it's OK
	return ($sum % 10 == 0);
}
?>