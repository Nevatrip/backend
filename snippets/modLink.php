id: 4
source: 1
name: modLink
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

$result = $input;
$tpl = empty($options) ? 'tpl_ВнешняяСсылка' : $options;

preg_match('/\[(.*?)\]\((.*?)\)/', $result, $matches);

if(empty($matches[2])){
    return $input;
}

return $modx->parseChunk($tpl, array(
   'title' => $matches[1],
   'url' => $matches[2]
));