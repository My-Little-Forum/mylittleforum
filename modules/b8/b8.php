<?php

#   Copyright (C) 2006-2019 Tobias Leupold <tobias.leupold@web.de>
#
#   b8 - A statistical ("Bayesian") spam filter written in PHP 5
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
 * Copyright (C) 2006-2019 Tobias Leupold <tobias.leupold@web.de>
 *
 * @license LGPL 2.1
 * @access public
 * @package b8
 * @author Tobias Leupold
 * @author Oliver Lillie (original PHP 5 port)
 */

class b8
{

    const DBVERSION = 3;

    public $config = array(
        'lexer'        => 'default',
        'degenerator'  => 'default',
        'storage'      => 'dba',
        'use_relevant' => 15,
        'min_dev'      => 0.2,
        'rob_s'        => 0.3,
        'rob_x'        => 0.5
    );

    public $storage     = null;
    public $lexer       = null;
    public $degenerator = null;

    private $_token_data = null;

    const SPAM    = 'spam';
    const HAM     = 'ham';
    const LEARN   = 'learn';
    const UNLEARN = 'unlearn';

    const CLASSIFYER_TEXT_MISSING = 'CLASSIFYER_TEXT_MISSING';

    const TRAINER_TEXT_MISSING     = 'TRAINER_TEXT_MISSING';
    const TRAINER_CATEGORY_MISSING = 'TRAINER_CATEGORY_MISSING';
    const TRAINER_CATEGORY_FAIL    = 'TRAINER_CATEGORY_FAIL';

    /**
     * Constructs b8
     *
     * @access public
     * @return void
     */
    function __construct(
        $config             = array(),
        $config_storage     = array(),
        $config_lexer       = array(),
        $config_degenerator = array()
    )
    {
        # Validate config data
        foreach ($config as $name => $value) {
            switch ($name) {
                case 'min_dev':
                case 'rob_s':
                case 'rob_x':
                    $this->config[$name] = (float) $value;
                    break;
                case 'use_relevant':
                    $this->config[$name] = (int) $value;
                    break;
                case 'lexer':
                case 'degenerator':
                case 'storage':
                    $this->config[$name] = (string) $value;
                    break;
                default:
                    throw new Exception("b8: Unknown configuration key: \"$name\"");
            }
        }

        # Setup the degenerator class
        $class = $this->_loadClass('degenerator', $this->config['degenerator']);
        if ($class === false) {
            throw new Exception(
                "b8: Could not load class definition file for degenerator " .
                "\"{$this->config['degenerator']}\""
            );
        }
        $this->degenerator = new $class($config_degenerator);

        # Setup the lexer class
        $class = $this->_loadClass('lexer', $this->config['lexer']);
        if ($class === false) {
            throw new Exception(
                "b8: Could not load class definition file for lexer \"{$this->config['lexer']}\""
            );
        }
        $this->lexer = new $class($config_lexer);

        # Setup the storage backend
        $class = $this->_loadClass('storage', 'base');
        if ($class === false) {
            throw new Exception("b8: Could not load class definition file for the storage base class");
        }
        $class = $this->_loadClass('storage', $this->config['storage']);
        if ($class === false) {
            throw new Exception(
                "b8: Could not load class definition file for storage backend " .
                "\"{$this->config['storage']}\""
            );
        }
        $this->storage = new $class($config_storage, $this->degenerator);
    }

    /**
     * Load a class file if a class has not been defined yet.
     *
     * @access private
     * @return boolean Returns true if everything is okay, otherwise false.
     */
    private function _loadClass($class_type, $class_name)
    {
        $complete_class_name = "b8_{$class_type}_{$class_name}";
        $class_file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $class_type . DIRECTORY_SEPARATOR . "{$class_type}_{$class_name}.php";

        if (class_exists($complete_class_name, false) === false) {
            # Check if the requested file actually exists
            if (is_file($class_file) !== true or is_readable($class_file) !== true) {
                return false;
            }

            # Include it
            $included = require_once($class_file);

            if ($included === false or class_exists($complete_class_name, false) === false) {
                return false;
            }
        }

        return $complete_class_name;
    }

    /**
     * Classifies a text
     *
     * @access public
     * @param string $text
     * @return mixed float The rating between 0 (ham) and 1 (spam) or an error code
     */
    public function classify($text = null)
    {
        # Let's first see if the user called the function correctly
        if ($text === null) {
            return self::CLASSIFYER_TEXT_MISSING;
        }

        # Get the internal database variables, containing the number of ham and
        # spam texts so the spam probability can be calculated in relation to them
        $internals = $this->storage->getInternals();

        # Calculate the spamminess of all tokens

        # Get all tokens we want to rate
        $tokens = $this->lexer->getTokens($text);

        # Check if the lexer failed
        # (if so, $tokens will be a lexer error code, if not, $tokens will be an array)
        if (!is_array($tokens)) {
            return $tokens;
        }

        # Fetch all available data for the token set from the database
        $this->_token_data = $this->storage->get(array_keys($tokens));

        # Calculate the spamminess and importance for each token (or a degenerated form of it)

        $word_count = array();
        $rating     = array();
        $importance = array();

        foreach ($tokens as $word => $count) {
            $word_count[$word] = $count;

            # Although we only call this function only here ... let's do the
            # calculation stuff in a function to make this a bit less confusing ;-)
            $rating[$word] = $this->_getProbability(
                $word, $internals['texts_ham'], $internals['texts_spam']
            );

            $importance[$word] = abs(0.5 - $rating[$word]);
        }

        # Order by importance
        arsort($importance);
        reset($importance);

        # Get the most interesting tokens (use all if we have less than the given number)
        $relevant = array();
        for ($i = 0; $i < $this->config['use_relevant']; $i++) {
            if ($token = key($importance)) {
                # Important tokens remain

                # If the token's rating is relevant enough, use it
                if (abs(0.5 - $rating[$token]) > $this->config['min_dev']) {
                    # Tokens that appear more than once also count more than once
                    for ($x = 0, $l = $word_count[$token]; $x < $l; $x++) {
                        array_push($relevant, $rating[$token]);
                    }
                }
            } else {
                # We have less words as we want to use, so we
                # already use what we have and can break here
                break;
            }

            next($importance);
        }

        # Calculate the spamminess of the text (thanks to Mr. Robinson ;-)
        # We set both hamminess and spamminess to 1 for the first multiplying
        $hamminess  = 1;
        $spamminess = 1;

        # Consider all relevant ratings
        foreach ($relevant as $value) {
            $hamminess  *= (1.0 - $value);
            $spamminess *= $value;
        }

        # If no token was good for calculation, we really don't know how
        # to rate this text, so can return 0.5 without further calculations.
        if ($hamminess == 1 and $spamminess == 1) {
            return 0.5;
        }

        # Calculate the combined rating

        # Get the number of relevant ratings
        $n = count($relevant);

        # The actual hamminess and spamminess
        $hamminess  = 1 - pow($hamminess,  (1 / $n));
        $spamminess = 1 - pow($spamminess, (1 / $n));

        # Calculate the combined indicator
        $probability = ($hamminess - $spamminess) / ($hamminess + $spamminess);

        # We want a value between 0 and 1, not between -1 and +1, so ...
        $probability = (1 + $probability) / 2;

        # Alea iacta est
        return $probability;
    }

    /**
     * Calculate the spamminess of a single token also considering "degenerated" versions
     *
     * @access private
     * @param string $word
     * @param string $texts_ham
     * @param string $texts_spam
     * @return void
     */
    private function _getProbability($word, $texts_ham, $texts_spam)
    {
        # Let's see what we have!
        if (isset($this->_token_data['tokens'][$word]) === true) {
            # The token is in the database, so we can use it's data as-is
            # and calculate the spamminess of this token directly
            return $this->_calcProbability(
                $this->_token_data['tokens'][$word], $texts_ham, $texts_spam
            );
        }

        # The token was not found, so do we at least have similar words?
        if (isset($this->_token_data['degenerates'][$word]) === true) {
            # We found similar words, so calculate the spamminess for each one
            # and choose the most important one for the further calculation

            # The default rating is 0.5 simply saying nothing
            $rating = 0.5;

            foreach ($this->_token_data['degenerates'][$word] as $degenerate => $count) {
                # Calculate the rating of the current degenerated token
                $rating_tmp = $this->_calcProbability($count, $texts_ham, $texts_spam);

                # Is it more important than the rating of another degenerated version?
                if(abs(0.5 - $rating_tmp) > abs(0.5 - $rating)) {
                    $rating = $rating_tmp;
                }
            }

            return $rating;
        } else {
            # The token is really unknown, so choose the default rating
            # for completely unknown tokens. This strips down to the
            # robX parameter so we can cheap out the freaky math ;-)
            return $this->config['rob_x'];
        }
    }

    /**
     * Do the actual spamminess calculation of a single token
     *
     * @access private
     * @param array $data
     * @param string $texts_ham
     * @param string $texts_spam
     * @return void
     */
    private function _calcProbability($data, $texts_ham, $texts_spam)
    {
        # Calculate the basic probability as proposed by Mr. Graham

        # But: consider the number of ham and spam texts saved instead of the
        # number of entries where the token appeared to calculate a relative
        # spamminess because we count tokens appearing multiple times not just
        # once but as often as they appear in the learned texts.

        $rel_ham = $data['count_ham'];
        $rel_spam = $data['count_spam'];

        if ($texts_ham > 0) {
            $rel_ham = $data['count_ham'] / $texts_ham;
        }

        if ($texts_spam > 0) {
            $rel_spam = $data['count_spam'] / $texts_spam;
        }

        $rating = $rel_spam / ($rel_ham + $rel_spam);

        # Calculate the better probability proposed by Mr. Robinson
        $all = $data['count_ham'] + $data['count_spam'];
        return (($this->config['rob_s'] * $this->config['rob_x']) + ($all * $rating)) /
               ($this->config['rob_s'] + $all);
    }

    /**
     * Check the validity of the category of a request
     *
     * @access private
     * @param string $category
     * @return void
     */
    private function _checkCategory($category)
    {
        return $category === self::HAM or $category === self::SPAM;
    }

    /**
     * Learn a reference text
     *
     * @access public
     * @param string $text
     * @param const $category Either b8::SPAM or b8::HAM
     * @return mixed void or an error code
     */
    public function learn($text = null, $category = null)
    {
        # Let's first see if the user called the function correctly
        if ($text === null) {
            return self::TRAINER_TEXT_MISSING;
        }
        if ($category === null) {
            return self::TRAINER_CATEGORY_MISSING;
        }

        return $this->_processText($text, $category, self::LEARN);
    }

    /**
     * Unlearn a reference text
     *
     * @access public
     * @param string $text
     * @param const $category Either b8::SPAM or b8::HAM
     * @return mixed void or an error code
     */
    public function unlearn($text = null, $category = null)
    {
        # Let's first see if the user called the function correctly
        if ($text === null) {
            return self::TRAINER_TEXT_MISSING;
        }
        if ($category === null) {
            return self::TRAINER_CATEGORY_MISSING;
        }

        return $this->_processText($text, $category, self::UNLEARN);
    }

    /**
     * Does the actual interaction with the storage backend for learning or unlearning texts
     *
     * @access private
     * @param string $text
     * @param const $category Either b8::SPAM or b8::HAM
     * @param const $action Either b8::LEARN or b8::UNLEARN
     * @return mixed void or an error code
     */
    private function _processText($text, $category, $action)
    {
        # Look if the request is okay
        if ($this->_checkCategory($category) === false) {
            return self::TRAINER_CATEGORY_FAIL;
        }

        # Get all tokens from $text
        $tokens = $this->lexer->getTokens($text);

        # Check if the lexer failed
        # (if so, $tokens will be a lexer error code, if not, $tokens will be an array)
        if (!is_array($tokens)) {
            return $tokens;
        }

        # Pass the tokens and what to do with it to the storage backend
        return $this->storage->processText($tokens, $category, $action);
    }

}

?>