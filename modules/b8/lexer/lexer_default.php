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
 * @author Oliver Lillie (aka buggedcom) (original PHP 5 port)
 */

class b8_lexer_default
{

	const LEXER_TEXT_NOT_STRING = 'LEXER_TEXT_NOT_STRING';
	const LEXER_TEXT_EMPTY      = 'LEXER_TEXT_EMPTY';
	
	public $config = array(
		'min_size'      => 3,
		'max_size'      => 30,
		'allow_numbers' => FALSE,
		'get_uris'      => TRUE,
		'old_get_html'  => TRUE,
		'get_html'      => FALSE,
		'get_bbcode'    => FALSE
	);
	
	private $_tokens         = NULL;
	private $_processed_text = NULL;
	
	# The regular expressions we use to split the text to tokens
	public $regexp = array(
		'raw_split' => '/[\s,\.\/"\:;\|<>\-_\[\]{}\+=\)\(\*\&\^%]+/',
		'ip'        => '/([A-Za-z0-9\_\-\.]+)/',
		'uris'      => '/([A-Za-z0-9\_\-]*\.[A-Za-z0-9\_\-\.]+)/',
		'html'      => '/(<.+?>)/',
		'bbcode'    => '/(\[.+?\])/',
		'tagname'   => '/(.+?)\s/',
		'numbers'   => '/^[0-9]+$/'
	);
	
	/**
	 * Constructs the lexer.
	 *
	 * @access public
	 * @return void
	 */
	
	function __construct($config)
	{
	
		# Validate config data
		
		foreach ($config as $name=>$value) {
		
			switch($name) {
			
				case 'min_size':
				case 'max_size':
					$this->config[$name] = (int) $value;
					break;
					
				case 'allow_numbers':
				case 'get_uris':
				case 'old_get_html':
				case 'get_html':
				case 'get_bbcode':
					$this->config[$name] = (bool) $value;
					break;
					
				default:
					throw new Exception("b8_lexer_default: Unknown configuration key: \"$name\"");
					
			}
			
		}
		
	}
	
	/**
	 * Splits a text to tokens.
	 *
	 * @access public
	 * @param string $text
	 * @return mixed Returns a list of tokens or an error code
	 */
	
	public function get_tokens($text)
	{
	
		# Check if we actually have a string ...
		if(is_string($text) === FALSE)
			return self::LEXER_TEXT_NOT_STRING;
		
		# ... and if it's empty
		if(empty($text) === TRUE)
			return self::LEXER_TEXT_EMPTY;
		
		# Re-convert the text to the original characters coded in UTF-8, as
		# they have been coded in html entities during the post process
		$this->_processed_text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
		
		# Reset the token list
		$this->_tokens = array();
		
		if($this->config['get_uris'] === TRUE) {
			# Get URIs
			$this->_get_uris($this->_processed_text);
		}
		
		if($this->config['old_get_html'] === TRUE) {
			# Get HTML - the old way without removing the found tags
			$this->_old_get_html($this->_processed_text);
		}
		
		if($this->config['get_html'] === TRUE) {
			# Get HTML
			$this->_get_markup($this->_processed_text, $this->regexp['html']);
		}
		
		if($this->config['get_bbcode'] === TRUE) {
			# Get BBCode
			$this->_get_markup($this->_processed_text, $this->regexp['bbcode']);
		}
		
		# We always want to do a raw split of the (remaining) text, so:
		$this->_raw_split($this->_processed_text);
		
		# Be sure not to return an empty array
		if(count($this->_tokens) == 0)
			$this->_tokens['b8*no_tokens'] = 1;
		
		# Return a list of all found tokens
		return $this->_tokens;
		
	}
	
	/**
	 * Validates a token.
	 *
	 * @access private
	 * @param string $token The token string.
	 * @return boolean Returns TRUE if the token is valid, otherwise returns FALSE.
	 */
	
	private function _is_valid($token)
	{
	
		# Just to be sure that the token's name won't collide with b8's internal variables
		if(substr($token, 0, 3) == 'b8*')
			return FALSE;
			
		# Validate the size of the token
		
		$len = strlen($token);
		
		if($len < $this->config['min_size'] or $len > $this->config['max_size'])
			return FALSE;
		
		# We may want to exclude pure numbers
		if($this->config['allow_numbers'] === FALSE) {
			if(preg_match($this->regexp['numbers'], $token) > 0)
				return FALSE;
		}
		
		# Token is okay
		return TRUE;
		
	}
	
	/**
	 * Checks the validity of a token and adds it to the token list if it's valid.
	 *
	 * @access private
	 * @param string $token
	 * @param bool $remove When set to TRUE, the string given in $word_to_remove is removed from the text passed to the lexer.
	 * @param string $word_to_remove
	 * @return void
	 */
	
	private function _add_token($token, $remove, $word_to_remove)
	{
	
		# Check the validity of the token
		if($this->_is_valid($token) === FALSE)
			return;
		
		# Add it to the list or increase it's counter
		if(isset($this->_tokens[$token]) === FALSE)
			$this->_tokens[$token] = 1;
		else
			$this->_tokens[$token] += 1;
			
		# If requested, remove the word or it's original version from the text
		if($remove === TRUE)
			$this->_processed_text = str_replace($word_to_remove, '', $this->_processed_text);
			
	}
	
	/**
	 * Gets URIs.
	 *
	 * @access private
	 * @param string $text
	 * @return void
	 */
	
	private function _get_uris($text)
	{
	
		# Find URIs
		preg_match_all($this->regexp['uris'], $text, $raw_tokens);
		
		foreach($raw_tokens[1] as $word) {
		
			# Remove a possible trailing dot
			$word = rtrim($word, '.');
			
			# Try to add the found tokens to the list
			$this->_add_token($word, TRUE, $word);
			
			# Also process the parts of the found URIs
			$this->_raw_split($word);
			
		}
		
	}
	
	/**
	 * Gets HTML or BBCode markup, depending on the regexp used.
	 *
	 * @access private
	 * @param string $text
	 * @param string $regexp
	 * @return void
	 */
	
	private function _get_markup($text, $regexp)
	{
	
		# Search for the markup
		preg_match_all($regexp, $text, $raw_tokens);
		
		foreach($raw_tokens[1] as $word) {
		
			$actual_word = $word;
			
			# If the tag has parameters, just use the tag itself
			if(strpos($word, ' ') !== FALSE) {
				preg_match($this->regexp['tagname'], $word, $match);
				$actual_word = $match[1];
				$word = "$actual_word..." . substr($word, -1);
			}
			
			# Try to add the found tokens to the list
			$this->_add_token($word, TRUE, $actual_word);
			
		}
		
	}
	
	/**
	 * The function to get HTML code used til b8 0.5.2.
	 *
	 * @access private
	 * @param string $text
	 * @return void
	 */
	
	private function _old_get_html($text)
	{
	
		# Search for the markup
		preg_match_all($this->regexp['html'], $text, $raw_tokens);
		
		foreach($raw_tokens[1] as $word) {
		
			# If the tag has parameters, just use the tag itself
			if(strpos($word, ' ') !== FALSE) {
				preg_match($this->regexp['tagname'], $word, $match);
				$word = "{$match[1]}...>";
			}
			
			# Try to add the found tokens to the list
			$this->_add_token($word, FALSE, NULL);
			
		}
		
	}
	
	/**
	 * Does a raw split.
	 *
	 * @access private
	 * @param string $text
	 * @return void
	 */
	
	private function _raw_split($text)
	{
		foreach(preg_split($this->regexp['raw_split'], $text) as $word) {
			# Check the word and add it to the token list if it's valid
			$this->_add_token($word, FALSE, NULL);
		}
	}
	
}

?>