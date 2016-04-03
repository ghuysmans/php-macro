<?php
require_once('MacroProcessor.php');

class MyMacroProcessor extends MacroProcessor {
	protected function macro($_) {
		return 'xxx';
	}
}

$mp = new MyMacroProcessor();
$errors = array();
echo $mp->process(file_get_contents('page.html'), $errors);
