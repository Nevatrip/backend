id: 7
source: 1
name: modEStartTimeForm
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

if(!empty($input)){
    $times = explode(',', $input);
    
    foreach($times as &$time){
        $time = $modx->parseChunk('tpl_FormOption', array(
           'title' => $time,
           'value' => $time
        ));
    }
    
    return $modx->parseChunk('tpl_FormEStartTime', array(
       'options' => implode('', $times)
    ));
}