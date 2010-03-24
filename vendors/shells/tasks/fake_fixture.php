<?php
  /**
   * Fake: Fixture generator plugin for cAKEphp.
   *
   * FakeFixture code used ModelTask code as reference.
   */
  /**
   * FakeFixtureTask code license:
   *
   * @copyright   Copyright (C) 2010 by 101000code/101000LAB
   * @since       CakePHP(tm) v 1.2
   * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
   */
  /**
   * ModelTask code license:
   *
   * The ModelTask handles creating and updating models files.
   *
   * Long description for file
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
   * @subpackage      cake.cake.console.libs.tasks
   * @since       CakePHP(tm) v 1.2
   * @version         $Revision$
   * @modifiedby      $LastChangedBy$
   * @lastmodified  $Date$
   * @license         http://www.opensource.org/licenses/mit-license.php The MIT License
   */
class FakeFixtureTask extends Shell{

    var $path = MODELS;

    var $tasks = array('DbConfig', 'Model');

    function execute() {
        if (empty($this->args)) {
            $this->__interactive();
        }

        if (!empty($this->args[0])) {
            $model = Inflector::camelize($this->args[0]);
            $this->useDbConfig = 'default';
            if ($this->bake($model)) {
                if ($this->_checkUnitTest()) {
                    $this->bakeTest($model);
                }
            }
        }
    }

    function __interactive() {
        $this->hr();
        $this->out(sprintf("Fake Model\nPath: %s", $this->path));
        $this->hr();
        $this->interactive = true;

        $useTable = null;
        $primaryKey = 'id';

        $useDbConfig = 'default';
        $configs = get_class_vars('DATABASE_CONFIG');

        if (!is_array($configs)) {
            return $this->DbConfig->execute();
        }

        $connections = array_keys($configs);
        if (count($connections) > 1) {
            $useDbConfig = $this->in(__('Use Database Config', true) .':', $connections, 'default');
        }
        $this->useDbConfig = $useDbConfig;

        $currentModelName = $this->Model->getName($useDbConfig);
        $db =& ConnectionManager::getDataSource($useDbConfig);
        $useTable = Inflector::tableize($currentModelName);
        $fullTableName = $db->fullTableName($useTable, false);
        $tableIsGood = false;

        if (array_search($useTable, $this->Model->__tables) === false) {
            $this->out('');
            $this->out(sprintf(__("Given your model named '%s', Cake would expect a database table named %s", true), $currentModelName, $fullTableName));
            $tableIsGood = $this->in(__('Do you want to use this table?', true), array('y','n'), 'y');
        }

        if (strtolower($tableIsGood) == 'n' || strtolower($tableIsGood) == 'no') {
            $useTable = $this->in(__('What is the name of the table (enter "null" to use NO table)?', true));
        }

        while ($tableIsGood == false && strtolower($useTable) != 'null') {
            if (is_array($this->Model->__tables) && !in_array($useTable, $this->Model->__tables)) {
                $fullTableName = $db->fullTableName($useTable, false);
                $this->out($fullTableName . ' does not exist.');
                $useTable = $this->in(__('What is the name of the table (enter "null" to use NO table)?', true));
                $tableIsGood = false;
            } else {
                $tableIsGood = true;
            }
        }

        if (in_array($useTable, $this->Model->__tables)) {
            App::import('Model');
            $tempModel = new Model(array('name' => $currentModelName, 'table' => $useTable, 'ds' => $useDbConfig));

            $fields = $tempModel->schema();
            if (!array_key_exists('id', $fields)) {
                foreach ($fields as $name => $field) {
                    if (isset($field['key']) && $field['key'] == 'primary') {
                        break;
                    }
                }
                $primaryKey = $this->in(__('What is the primaryKey?', true), null, $name);
            }
        }

        $this->out('');
        $this->hr();
        $this->out(__('The following Model will be created:', true));
        $this->hr();
        $this->out("Name:       " . $currentModelName);

        if ($useDbConfig !== 'default') {
            $this->out("DB Config:  " . $useDbConfig);
        }
        if ($fullTableName !== Inflector::tableize($currentModelName)) {
            $this->out("DB Table:   " . $fullTableName);
        }
        if ($primaryKey != 'id') {
            $this->out("Primary Key: " . $primaryKey);
        }

        $this->hr();
        $looksGood = $this->in(__('Look okay?', true), array('y','n'), 'y');

        if (strtolower($looksGood) == 'y' || strtolower($looksGood) == 'yes') {
            if ($this->fixture($currentModelName, $useTable)) {

            }
        } else {
            return false;
        }
    }

    function fixture($model, $useTable = null) {
        if (!class_exists('CakeSchema')) {
            App::import('Model', 'Schema');
        }
        $out = "\nclass {$model}Fixture extends CakeTestFixture {\n";

        if (!$useTable) {
            $useTable = Inflector::tableize($model);
        } else {
            //$out .= "\tvar \$table = '$useTable';\n";
        }
        $schema = new CakeSchema();
        $data = $schema->read(array('models' => false, 'connection' => $this->useDbConfig));

        if (!isset($data['tables'][$useTable])) {
            return false;
        }

        $out .= "\tvar \$name = '$model';\n";
        $out .= "\tvar \$import = array('table' => '{$useTable}', 'records' => true, 'connection' => '{$this->useDbConfig}');\n";

        $tempModel = ClassRegistry::init($model);
        $tempModel->recursive = -1;
        $count = $tempModel->find('count');

        $response = '';
        $example = 'Q';
        while ($response == '') {
            $response = $this->in($count . " record exists. Set limit number?"
                                  . "\nLimit number or [A]ll"
                                  . "\n[Q]uit", null, $example);
            if (strtoupper($response) === 'Q') {
                $this->out('Fake Aborted');
                $this->_stop();
            }
        }

        if (!is_numeric($response) && strtoupper($response) !== 'A') {
            $this->out(__('You have made an invalid selection. Please choose a command to execute by entering A or Q or number.', true));
            $this->execute();
        }

        if (is_numeric($response) && $response > $count) {
            $this->out(__('The number that you selected is more than the number of records. Please try again.', true));
            $this->execute();
        }

        $query = array();

        if (is_numeric($response)) {
            $query['limit'] = $response;
        }

        $results = $tempModel->find('all', $query);

        $records = array();
        foreach ($results as $result) {
            foreach ($result[$model] as $field => $value) {
                $record[] = "\t\t'$field' => '$value'";
            }
            $records[] = "array(\n" . implode(",\n", $record) . ")";
        }

        $records = implode(",\n", $records);
        $out .= "\tvar \$records = array(\n$records\n\t);\n";
        $out .= "}\n";


        $path = TESTS . DS . 'fixtures' . DS;
        if (isset($this->plugin)) {
            $pluginPath = 'plugins' . DS . Inflector::underscore($this->plugin) . DS;
            $path = APP . $pluginPath . 'tests' . DS . 'fixtures' . DS;
        }
        $filename = Inflector::underscore($model) . '_fixture.php';
        $header = '$Id';
$content = "<?php \n/* SVN FILE: $header$ */\n/* " . $model . " Fixture generated on: " . date('Y-m-d H:i:s') . " : " . time() . "*/\n{$out}?>";
        $this->out("\nBaking test fixture for $model...");
        if ($this->createFile($path . $filename, $content)) {
            return str_replace("\t\t", "\t\t\t", $records);
        }
        return false;
    }
  }