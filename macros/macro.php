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
		//on each iteration, get the innermost macro call
		while ($this->getCalls($text, $matches)) {
			//start from the end to keep valid offsets
			foreach (array_reverse($matches) as $call) {
				$message = null;
				try {
					$name = $call[1][0];
					$macro = new ReflectionMethod(static::class, $name);
					if (!$macro->isProtected())
						throw new Exception(
							"$name isn't protected, hence not a macro");
					$result = $this->$name($this->getArguments($call));
				}
				//TODO special exception for bad arguments, auto name
				catch (ReflectionException $e) {
					//let's avoid parentheses in the message...
					$message = "the $name macro doesn't exist";
				}
				catch (Exception $e) {
					$message = $e->getMessage();
				}
				if ($message) {
					$result = "<span class=\"macroerror\">$message</span>";
					$errors[] = $message;
				}
				$text = substr_replace($text, $result,
					$call[0][1], strlen($call[0][0]));
			}
		}
		return $text;
	}
}

class MyMacroProcessor extends MacroProcessor {
	protected function macro($_) {
		return 'xxx';
	}
}

$mp = new MyMacroProcessor();
$errors = array();
echo $mp->process(file_get_contents('page.html'), $errors);
