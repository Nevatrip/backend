id: 2
source: 1
name: modPoints
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

if(empty($input)){
    return;
}
$result = $input;
$tpl = $options;
$result = explode("\n", $result);

for($i = 0; $i < count($result); $i++){
    $result[$i] = $modx->getChunk($tpl, array(
       'title' => $result[$i],
    ));
}
return implode('', $result);