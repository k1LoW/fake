<?php

class FakeShell extends Shell {

    var $tasks = array('DbConfig', 'FakeFixture');

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

    }

    function help() {

    }
}
?>