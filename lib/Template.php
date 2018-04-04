<?php

class Template {
	public  $vars       = [];
	public  $blocks     = [];
	private $pagevars   = [];
	private $tpl_path   = NULL;
	private $cache_path = NULL;

	public function __construct ($tpl_path, $cache_path, array $pagevars) {
		if(!file_exists($tpl_path)){
			throw new Exception('Error templates folder not found.');
		} else if (!file_exists($cache_path)) {
			throw new Exception('Error cache folder not found.');
		} else {
			$this->tpl_path   = $tpl_path;
			$this->cache_path = $cache_path;
		}

		$this->pagevars = $pagevars;
	}

	public function assign ($vars, $value = null) {
		if (is_array($vars)) {
			$this->vars = array_merge($this->vars, $vars);
		} else if ($value !== null) {
			$this->vars[$vars] = $value;
		}
	}

	public function blockAssign ($name, $array) {
		$this->blocks[$name][] = (array)$array;
	}

	private function compileVars ($var) {
		$newvar = $this->compileVar($var[1]);
		return "<?php echo isset(" . $newvar . ") ? " . $newvar . " : '{" . $var[1] . "}' ?>";
	}

	private function minify_css ($input) {
		if(trim($input) === "") return $input;
		// Force white-space(s) in `calc()`
		if(strpos($input, 'calc(') !== false) {
			$input = preg_replace_callback('#(?<=[\s:])calc\(\s*(.*?)\s*\)#', function($matches) {
				return 'calc(' . preg_replace('#\s+#', "\x1A", $matches[1]) . ')';
			}, $input);
		}
		return preg_replace(
			array(
				// Remove comment(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
				// Remove unused white-space(s)
				'#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~+]|\s*+-(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
				// Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
				'#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
				// Replace `:0 0 0 0` with `:0`
				'#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
				// Replace `background-position:0` with `background-position:0 0`
				'#(background-position):0(?=[;\}])#si',
				// Replace `0.6` with `.6`, but only when preceded by a white-space or `=`, `:`, `,`, `(`, `-`
				'#(?<=[\s=:,\(\-]|&\#32;)0+\.(\d+)#s',
				// Minify string value
				'#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][-\w]*?)\2(?=[\s\{\}\];,])#si',
				'#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
				// Minify HEX color code
				'#(?<=[\s=:,\(]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
				// Replace `(border|outline):none` with `(border|outline):0`
				'#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
				// Remove empty selector(s)
				'#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s',
				'#\x1A#'
			),
			array(
				'$1',
				'$1$2$3$4$5$6$7',
				'$1',
				':0',
				'$1:0 0',
				'.$1',
				'$1$3',
				'$1$2$4$5',
				'$1$2$3',
				'$1:0',
				'$1$2',
				' '
			),
		$input);
	}

	private function minify_html ($input) {
		if(trim($input) === "") return $input;
		// Remove extra white-space(s) between HTML attribute(s)
		$input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
		return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
		}, str_replace("\r", "", $input));
		// Minify inline CSS declaration(s)
		if(strpos($input, ' style=') !== false) {
			$input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
				return '<' . $matches[1] . ' style=' . $matches[2] . $this->minify_css($matches[3]) . $matches[2];
			}, $input);
		}
		return preg_replace(
			[
				// t = text
				// o = tag open
				// c = tag close
				// Keep important white-space(s) after self-closing HTML tag(s)
				'#<(img|input)(>| .*?>)#s',
				// Remove a line break and two or more white-space(s) between tag(s)
				'#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
				'#(<!--.*?-->)|(?<!\= >)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
				'#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
				'#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
				'#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
				'#<(img|input)(>| .*?>)<\/\1\x1A>#s', // reset previous fix
				'#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
				// Force line-break with `&#10;` or `&#xa;`
				'#&\#(?:10|xa);#',
				// Force white-space with `&#32;` or `&#x20;`
				'#&\#(?:32|x20);#',
				// Remove HTML comment(s) except IE comment(s)
				'#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
			],
			[
				"<$1$2</$1\x1A>",
				'$1$2$3',
				'$1$2$3',
				'$1$2$3$4$5',
				'$1$2$3$4$5$6$7',
				'$1$2$3',
				'<$1$2',
				'$1 ',
				"\n",
				' ',
				""
			],
		$input);
	}

	private function compileVar ($var) {
		if (strpos($var, '.') === false) {
			$var = '$this->vars[\'' . $var . '\']';
		} else {
			$vars = explode('.', $var);
			if (!isset($this->blocks[$vars[0]]) && isset($this->vars[$var[0]]) && gettype($this->vars[$var[0]]) == 'array') {
				$var = '$this->vars[\'' . $vars[0] . '\'][\'' . $vars[1] . '\']';
			} else {
				$var = preg_replace("#(.*)\.(.*)#", "\$_$1['$2']", $var);
			}
		}
		return $var;
	}

	private function compileTags ($match) {
		switch ($match[1]) {
			case 'INCLUDE':
				return "<?php echo \$this->compile('" . $match[2] . "'); ?>";
				break;

			case 'INCLUDEPHP':
				return "<?php echo include(" . PATH . $match[2] . "'); ?>";
				break;

			case 'IF':
				return $this->compileIf($match[2], false);
				break;

			case 'ELSEIF':
				return $this->compileIf($match[2], true);
				break;

			case 'ELSE':
				return "<?php } else { ?>";
				break;

			case 'ENDIF':
				return "<?php } ?>";
				break;

			case 'BEGIN':
				return "<?php if (isset(\$this->blocks['" . $match[2] . "'])) { foreach (\$this->blocks['" . $match[2] . "'] as \$_" . $match[2] . ") { ?>";
				break;

			case 'BEGINELSE':
				return "<?php } } else { { ?>";
				break;

			case 'END':
				return "<?php } } ?>";
				break;
		 }
	}

	private function compileIf ($code, $elseif) {
		$ex = explode(' ', trim($code));
		$code = '';

		foreach ($ex as $value) {
			$chars = strtolower($value);

			switch ($chars) {
				case 'and':
				case '&&':
				case 'or':
				case '||':
				case '==':
				case '!=':
				case '!==':
				case '>':
				case '<':
				case '>=':
				case '<=':
				case '0':
				case is_numeric($value):
					$code .= $value;
					break;

				case 'not':
					$code .= '!';
					break;

				default:
					if (preg_match('/^[A-Za-z0-9_\-\.]+$/i', $value)) {
						$var = $this->compileVar($value);
						$code .= "(isset(" . $var . ") ? " . $var . " : '')";
					} else {
						$code .= '\'' . preg_replace("#(\\\\|\'|\")#", '', $value) . '\'';
					}
					break;
			}
			$code .= ' ';
		}

		return '<?php ' . (($elseif) ? '} else ' : '') . 'if (' . trim($code) . ") { ?>";
	}

	private function compile ($file) {
		$abs_file = $this->tpl_path.'/'.$file;
		$cache_file = $this->cache_path.'/tpl_'.str_replace(['templates/', '/'], ['', '_'], $this->tpl_path.$file).'.php';

		if (file_exists($cache_file) && filemtime($cache_file) > filemtime($abs_file)) {
			include $cache_file;
			return;
		}

		$tpl = $uncompiled = @file_get_contents($abs_file);
		$tpl = preg_replace("#<\?(.*)\?>#", '', $tpl);
		$tpl = preg_replace_callback("#<!-- ([A-Z]+) (.*)? ?-->#U", array($this, 'compileTags'), $tpl);
		$tpl = preg_replace_callback("#{([A-Za-z0-9_\-.]+)}#U", array($this, 'compileVars'), $tpl);

		//$tpl = $this->minify_html($tpl);

		if (eval(' ?>'.$tpl.'<?php ') === false) {
			$this->error();
		} else if ($tpl) {
			file_put_contents($cache_file, $tpl);
		}
	}

	public function error () {
		exit('Fehler im Template!');
	}

	public function render ($file, $data = NULL) {
		$this->assign($this->pagevars);

		if ($data !== NULL) {
			$this->assign($data);
		}

		$this->compile($file.'.tpl');
		exit();
	}
}