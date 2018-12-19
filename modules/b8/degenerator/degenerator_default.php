<?php

#   Copyright (C) 2006-2012 Tobias Leupold <tobias.leupold@web.de>
#
#   This file is part of the b8 package
#
#   This program is free software; you can redistribute it and/or modify it
#   under the terms of the GNU Lesser General Public License as published by
#   the Free Software Foundation in version 2.1 of the License.
#
#   This program is distributed in the hope that it will be useful, but
#   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
#   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
#   License for more details.
#
#   You should have received a copy of the GNU Lesser General Public License
#   along with this program; if not, write to the Free Software Foundation,
#   Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.

/**
 * Copyright (C) 2006-2012 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL 2.1
 * @access public
 * @package b8
 * @author Tobias Leupold
 */

class b8_degenerator_default
{

	public $config = array(
		'multibyte' => FALSE,
		'encoding'  => 'UTF-8'
	);
	
	public $degenerates = array();
	
	/**
	 * Constructs the degenerator.
	 *
	 * @access public
	 * @return void
	 */
	
	function __construct($config)
	{
	
		# Validate config data
		
		foreach ($config as $name => $value) {
		
			switch($name) {
			
				case 'multibyte':
					$this->config[$name] = (bool) $value;
					break;
					
				case 'encoding':
					$this->config[$name] = (string) $value;
					break;
					
				default:
					throw new Exception("b8_degenerator_default: Unknown configuration key: \"$name\"");
			
			}
			
		}
		
	}
	
	/**
	 * Generates a list of "degenerated" words for a list of words.
	 *
	 * @access public
	 * @param array $tokens
	 * @return array An array containing an array of degenerated tokens for each token
	 */
	
	public function degenerate(array $words)
	{
	
		$degenerates = array();
		
		foreach($words as $word)
			$degenerates[$word] = $this->_degenerate_word($word);
		
		return $degenerates;
		
	}
	
	/**
	 * Remove duplicates from a list of degenerates of a word.
	 *
	 * @access private
	 * @param array $list The list to process
	 * @return array The list without duplicates
	 */
	 
	protected function _delete_duplicates($word, $list) 
	{
	
		$list_processed = array();
		
		# Check each upper/lower version
		foreach($list as $alt_word) {
			if($alt_word != $word)
				array_push($list_processed, $alt_word);
		}
		
		return $list_processed;
		
	}
	 
	/**
	 * Builds a list of "degenerated" versions of a word.
	 *
	 * @access private
	 * @param string $word
	 * @return array An array of degenerated words
	 */
	
	protected function _degenerate_word($word)
	{
	
		# Check for any stored words so the process doesn't have to repeat
		if(isset($this->degenerates[$word]) === TRUE)
			return $this->degenerates[$word];
		
		# Add different versions of upper and lower case and ucfirst
		
		$upper_lower = array();
		
		if($this->config['multibyte'] === FALSE) {
			# The standard upper/lower versions
			$lower = strtolower($word);
			$upper = strtoupper($word);
			$first = substr($upper, 0, 1) . substr($lower, 1, strlen($word));
		}
		elseif($this->config['multibyte'] === TRUE) {
			# The multibyte upper/lower versions
			$lower = mb_strtolower($word, $this->config['encoding']);
			$upper = mb_strtoupper($word, $this->config['encoding']);
			$first = mb_substr($upper, 0, 1, $this->config['encoding']) . mb_substr($lower, 1, mb_strlen($word), $this->config['encoding']);
		}
		
		# Add the versions
		array_push($upper_lower, $lower);
		array_push($upper_lower, $upper);
		array_push($upper_lower, $first);
		
		# Delete duplicate upper/lower versions
		$degenerate = $this->_delete_duplicates($word, $upper_lower);
		
		# Append the original word
		array_push($degenerate, $word);
		
		# Degenerate all versions
		
		foreach($degenerate as $alt_word) {
		
			# Look for stuff like !!! and ???
			
			if(preg_match('/[!?]$/', $alt_word) > 0) {
			
				# Add versions with different !s and ?s
				
				if(preg_match('/[!?]{2,}$/', $alt_word) > 0) {
					$tmp = preg_replace('/([!?])+$/', '$1', $alt_word);
					array_push($degenerate, $tmp);
				}
				
				$tmp = preg_replace('/([!?])+$/', '', $alt_word);
				array_push($degenerate, $tmp);
				
			}
			
			# Look for "..." at the end of the word
			
			$alt_word_int = $alt_word;
			
			while(preg_match('/[\.]$/', $alt_word_int) > 0) {
				$alt_word_int = substr($alt_word_int, 0, strlen($alt_word_int) - 1);
				array_push($degenerate, $alt_word_int);
			}
			
		}
		
		# Some degenerates are the same as the original word. These don't have
		# to be fetched, so we create a new array with only new tokens
		$degenerate = $this->_delete_duplicates($word, $degenerate);
		
		# Store the list of degenerates for the token to prevent unnecessary re-processing
		$this->degenerates[$word] = $degenerate;
		
		return $degenerate;
		
	}
	
}

?>