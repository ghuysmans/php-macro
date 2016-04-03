<?php
abstract class MacroProcessor {
	private function getArguments($args) {
		//TODO split
		//TODO strings lexing?
		return $args;
	}

	private function getCalls($text, &$calls) {
		$f = PREG_OFFSET_CAPTURE | PREG_SET_ORDER;
		return preg_match_all('/\$([^ (]+)\(([^()]*)\)/', $text, $calls, $f);
	}

	public function process($text, &$errors) {
		$matches = array();
		$methods = get_class_methods(static::class);
		$banned = array('process', 'getArguments', 'getCalls');
		//on each iteration, get the innermost macro call
		while ($this->getCalls($text, $matches)) {
			foreach (array_reverse($matches) as $call) {
				try {
					$name = $call[1][0];
					if (in_array($name, $banned) || !in_array($name, $methods))
						throw new Exception("non-existing macro: $name");
					$result = $this->$name($this->getArguments($call));
				}
				catch (Exception $e) {
					$m = $e->getMessage();
					$result = "<span class=\"macroerror\">$m</span>";
					$errors[] = $e;
				}
				$text = substr_replace($text, $result,
					$call[0][1], strlen($call[0][0]));
			}
		}
		return $text;
	}
}

class MyMacroProcessor extends MacroProcessor {
	public function macro($_) {
		return 'xxx';
	}
}

$mp = new MyMacroProcessor();
$errors = array();
echo $mp->process(file_get_contents('page.html'), $errors);
