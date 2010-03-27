<?php
  /**
   * Fake: Fixture generator plugin for cAKEphp.
   *
   * FakeShell code used BakeShell code as reference.
   */
  /**
   * FakeShell code license:
   *
   * @copyright   Copyright (C) 2010 by 101000code/101000LAB
   * @since       CakePHP(tm) v 1.2
   * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
   */
  /**
   * BakeShell code license:
   *
   * Command-line code generation utility to automate programmer chores.
   *
   * Bake is CakePHP's code generation script, which can help you kickstart
   * application development by writing fully functional skeleton controllers,
   * models, and views. Going further, Bake can also write Unit Tests for you.
   *
   * PHP versions 4 and 5
   *
   * CakePHP(tm) :  Rapid Development Framework (http://www.cakephp.org)
   * Copyright 2005-2010, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
   *
   * Licensed under The MIT License
   * Redistributions of files must retain the above copyright notice.
   *
   * @filesource
   * @copyright   Copyright 2005-2010, Cake Software Foundation, Inc. (http://www.cakefoundation.org)
   * @link        http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
   * @package         cake
   * @subpackage      cake.cake.console.libs
   * @since       CakePHP(tm) v 1.2.0.5012
   * @version         $Revision$
   * @modifiedby      $LastChangedBy$
   * @lastmodified  $Date$
   * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
   */
class FakeShell extends Shell {

    var $tasks = array('DbConfig', 'Project', 'FakeFixture');

    function main() {
        if (!is_dir($this->DbConfig->path)) {
            if ($this->Project->execute()) {
                $this->DbConfig->path = $this->params['working'] . DS . 'config' . DS;
            }
        }

        if (!config('database')) {
            $this->out(__("Your database configuration was not found. Take a moment to create one.", true));
            $this->args = null;
            return $this->DbConfig->execute();
        }
        $this->out('Interactive Fake Shell');
        $this->hr();
        $this->out('[F]ixture');
        $this->out('[Q]uit');

        $choice = strtoupper($this->in(__('Would you like to Fake?', true), array('F', 'Q')));
        switch ($choice) {
        case 'F':
            $this->FakeFixture->execute();
            break;
        case 'Q':
            exit(0);
            break;
        default:
            $this->out(__('You have made an invalid selection. Please choose a command to execute by entering F or Q.', true));
        }
        $this->hr();
        $this->main();
    }

    function all() {
            $this->FakeFixture->executeAll();
    }

    function help() {
        $this->out('Fake');
        $this->hr();
        $this->out('Fixture generator plugin for cAKEphp.');
        $this->hr();
        $this->out("Usage: cake fake <command>");
        $this->hr();
        $this->out('Commands:');
        $this->out("\n\tfake all\n\t\tgenerate all model fixture.");
        $this->out("");
    }
  }
?>