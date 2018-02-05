id: 19
source: 1
name: modPromocodes
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

$promocodes = explode(',', $input);

if(!count($promocodes)){
    return '';
}

$output = array();
foreach($promocodes as $p){
    preg_match('/\[(.+?)\]\(([0-9]+)\)/', $p, $matches);
    
    if(empty($matches[2])){
        continue;
    }
    
    $output[] = $modx->parseChunk('tpl_PromocodeScript', array(
       'title' => mb_strtolower($matches[1],'UTF-8'),
       'value' => $matches[2]
    ));
}

return implode(',', $output);