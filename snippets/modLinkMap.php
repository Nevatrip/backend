id: 15
source: 1
name: modLinkMap
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

$result = $input;
$tpl = $options;

preg_match('/\[(.*?)\]\((.*?)\)/', $result, $matches);

return $modx->parseChunk('tpl_ВнешняяСсылкаКарта', array(
   'title' => $matches[1],
   'url' => $matches[2]
));