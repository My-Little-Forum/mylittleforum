<?php

#   Copyright (C) 2010-2012 Tobias Leupold <tobias.leupold@web.de>
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
 * Functions used by all storage backends
 * Copyright (C) 2010-2012 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL 2.1
 * @access public
 * @package b8
 * @author Tobias Leupold
 */

abstract class b8_storage_base
{

	public $connected = FALSE;
	
	protected $degenerator = NULL;
	
	const INTERNALS_TEXTS     = 'b8*texts';
	const INTERNALS_DBVERSION = 'b8*dbversion';
	
	/**
	 * Checks if a b8 database is used and if it's version is okay.
	 *
	 * @access protected
	 * @return void throws an exception if something's wrong with the database
	 */
	
	protected function check_database()
	{
	
		$internals = $this->get_internals();
		
		if(isset($internals['dbversion'])) {
			if($internals['dbversion'] == b8::DBVERSION)
				return;
		}
		
		throw new Exception('b8_storage_base: The connected database is not a b8 v' . b8::DBVERSION . ' database.');
		
	}
	
	/**
	 * Get the database's internal variables.
	 *
	 * @access public
	 * @return array Returns an array of all internals.
	 */
	
	public function get_internals()
	{
	
		$internals = $this->_get_query(
			array(
				self::INTERNALS_TEXTS,
				self::INTERNALS_DBVERSION
			)
		);
		
		# Just in case this is called by check_database() and
		# it's not yet clear if we actually have a b8 database
		
		$texts_ham = NULL;
		$texts_spam = NULL;
		$dbversion = NULL;
		
		if(isset($internals[self::INTERNALS_TEXTS]['count_ham']))
			$texts_ham = (int) $internals[self::INTERNALS_TEXTS]['count_ham'];
			
		if(isset($internals[self::INTERNALS_TEXTS]['count_spam']))
			$texts_spam = (int) $internals[self::INTERNALS_TEXTS]['count_spam'];
			
		if(isset($internals[self::INTERNALS_DBVERSION]['count_ham']))
			$dbversion = (int) $internals[self::INTERNALS_DBVERSION]['count_ham'];
			
		return array(
			'texts_ham'  => $texts_ham,
			'texts_spam' => $texts_spam,
			'dbversion'  => $dbversion
		);
		
	}
	
	/**
	 * Get all data about a list of tags from the database.
	 *
	 * @access public
	 * @param array $tokens
	 * @return mixed Returns FALSE on failure, otherwise returns array of returned data in the format array('tokens' => array(token => count), 'degenerates' => array(token => array(degenerate => count))).
	 */
	
	public function get($tokens)
	{
	
		# First we see what we have in the database.
		$token_data = $this->_get_query($tokens);
		
		# Check if we have to degenerate some tokens
		
		$missing_tokens = array();
		
		foreach($tokens as $token) {
			if(!isset($token_data[$token]))
				$missing_tokens[] = $token;
		}
		
		if(count($missing_tokens) > 0) {
		
			# We have to degenerate some tokens
			$degenerates_list = array();
			
			# Generate a list of degenerated tokens for the missing tokens ...
			$degenerates = $this->degenerator->degenerate($missing_tokens);
			
			# ... and look them up
			foreach($degenerates as $token => $token_degenerates)
				$degenerates_list = array_merge($degenerates_list, $token_degenerates);
			
			$token_data = array_merge($token_data, $this->_get_query($degenerates_list));
			
		}
		
		# Here, we have all available data in $token_data.
		
		$return_data_tokens = array();
		$return_data_degenerates = array();
		
		foreach($tokens as $token) {
		
			if(isset($token_data[$token]) === TRUE) {
				# The token was found in the database
				$return_data_tokens[$token] = $token_data[$token];
			}
			
			else {
			
				# The token was not found, so we look if we
				# can return data for degenerated tokens
				
				foreach($this->degenerator->degenerates[$token] as $degenerate) {
					if(isset($token_data[$degenerate]) === TRUE) {
						# A degeneration of the token way found in the database
						$return_data_degenerates[$token][$degenerate] = $token_data[$degenerate];
					}
				}
				
			}
			
		}
		
		# Now, all token data directly found in the database is in $return_data_tokens
		# and all data for degenerated versions is in $return_data_degenerates, so
		return array(
			'tokens'      => $return_data_tokens,
			'degenerates' => $return_data_degenerates
		);
		
	}
	
	/**
	 * Stores or deletes a list of tokens from the given category.
	 *
	 * @access public
	 * @param array $tokens
	 * @param const $category Either b8::HAM or b8::SPAM
	 * @param const $action Either b8::LEARN or b8::UNLEARN
	 * @return void
	 */
	
	public function process_text($tokens, $category, $action)
	{
	
		# No matter what we do, we first have to check what data we have.
		
		# First get the internals, including the ham texts and spam texts counter
		$internals = $this->get_internals();
		
		# Then, fetch all data for all tokens we have
		$token_data = $this->_get_query(array_keys($tokens));
		
		# Process all tokens to learn/unlearn
		
		foreach($tokens as $token => $count) {
		
			if(isset($token_data[$token])) {
			
				# We already have this token, so update it's data
				
				# Get the existing data
				$count_ham  = $token_data[$token]['count_ham'];
				$count_spam = $token_data[$token]['count_spam'];
				
				# Increase or decrease the right counter
				
				if($action === b8::LEARN) {
					if($category === b8::HAM)
						$count_ham += $count;
					elseif($category === b8::SPAM)
						$count_spam += $count;
				}
				
				elseif($action == b8::UNLEARN) {
					if($category === b8::HAM)
						$count_ham -= $count;
					elseif($category === b8::SPAM)
						$count_spam -= $count;
				}
				
				# We don't want to have negative values
				
				if($count_ham < 0)
					$count_ham = 0;
				
				if($count_spam < 0)
					$count_spam = 0;
				
				# Now let's see if we have to update or delete the token
				if($count_ham != 0 or $count_spam != 0)
					$this->_update($token, array('count_ham' => $count_ham, 'count_spam' => $count_spam));
				else
					$this->_del($token);
					
			}
			
			else {
			
				# We don't have the token. If we unlearn a text, we can't delete it
				# as we don't have it anyway, so just do something if we learn a text
				
				if($action === b8::LEARN) {
				
					if($category === b8::HAM)
						$data = array('count_ham' => $count, 'count_spam' => 0);
					elseif($category === b8::SPAM)
						$data = array('count_ham' => 0, 'count_spam' => $count);
						
					$this->_put($token, $data);
					
				}
				
			}
			
		}
		
		# Now, all token have been processed, so let's update the right text
		
		if($action === b8::LEARN) {
			if($category === b8::HAM)
				$internals['texts_ham']++;
			elseif($category === b8::SPAM)
				$internals['texts_spam']++;
		}
		elseif($action == b8::UNLEARN) {
			if($category === b8::HAM) {
				if($internals['texts_ham'] > 0)
					$internals['texts_ham']--;
			}
			elseif($category === b8::SPAM) {
				if($internals['texts_spam'] > 0)
					$internals['texts_spam']--;
			}
		}
		
		$this->_update(self::INTERNALS_TEXTS, array('count_ham' => $internals['texts_ham'], 'count_spam' => $internals['texts_spam']));
		
		# We're done and can commit all changes to the database now
		$this->_commit();
		
	}
	
}

?>