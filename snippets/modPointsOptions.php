id: 9
source: 1
name: modPointsOptions
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

// print_r($input);
$delimiter = ',';
$points = explode($delimiter, $input);
if ($points[0] === "СПб-Петергоф") {
  $points[] = implode(' &mdash; ', $points);
}

$out = '';

foreach($points as $point){
    $out .= $modx->parseChunk('tpl_FormOption', array(
       'title' => $point,
       'value' => $point
    ));
}

return $out;