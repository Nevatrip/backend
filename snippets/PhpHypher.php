id: 50
source: 1
name: PhpHypher
description: 'PhpHypher for MODX Revolution'
properties: 'a:1:{s:7:"exclude";a:7:{s:4:"name";s:7:"exclude";s:4:"desc";s:17:"phphypher.exclude";s:4:"type";s:9:"textfield";s:7:"options";s:0:"";s:5:"value";s:107:"!(((title|alt|href|src|value|action|name)=(\"|'')(.*?)(\"|''))|(<pre(.*?)</pre>)|(<option(.*?)</option>))!ise";s:7:"lexicon";s:20:"phphypher:properties";s:4:"area";s:0:"";}}'
static_file: core/components/phphypher/elements/snippets/snippet.phphypher.php

-----

if (!isset($modx->phpHypher) || !($modx->phpHypher instanceof phpHypher)) {
	$path = $modx->getOption('phphypher.core_path',null,$modx->getOption('core_path').'components/phphypher/');
	require_once $path . 'model/phphypher/phphypher.class.php';
	$modx->phpHypher = new phpHypher($path . 'model/phphypher/hyph_ru_RU.conf');
}

// Exclude elements from processing by regular expression
if (!empty($exclude)) {
	preg_match_all($exclude, $input, $matches);
	$keys = array();
	foreach ($matches[0] as $k => $v) {
		$keys[] = '{{{'.$k.'}}}';
	}
	if (!empty($keys)) {
		$input = str_replace($matches[0], $keys, $input);
	}
}

// Text cut, if set
if (isset($options) && $options > 0) {
	if (mb_strlen($input, 'UTF-8') > $options) {
		$input = mb_substr($input, 0, $options, 'UTF-8').'...';
	}
}

// Hyphenate
$input = $modx->phpHypher->hyphenate($input, 'UTF-8');

// Include unprocessed elements
if (!empty($keys)) {
	$input = str_replace($keys, $matches[0], $input);
}

return $input;