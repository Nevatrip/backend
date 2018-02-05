id: 8
source: 1
name: modElement
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

$index = $options;
$delimiter = ',';

$elements = explode($delimiter, $input);

if(isset($elements[$options])){
    return $elements[$options];
}