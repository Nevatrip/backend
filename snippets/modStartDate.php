id: 20
source: 1
name: modStartDate
category: 'Output Modifiers'
properties: 'a:0:{}'

-----

$date = explode('.', $input);
$date = array($date[2], $date[1], $date[0]);
$date = implode('-', $date);

$restrictionTime = '23:59';
$restrictionDayOffset = 0;

$id = $options;
$restrictions = $modx->getObject('modTemplateVarResource', array(
  'tmplvarid' => 31,
  'contentid' => $id
));
if($restrictions){
    $restrictions = $restrictions->get('value');
}

if(!empty($restrictions)){
    preg_match('/([0-9]{2}:[0-9]{2})\(([0-9]+)\)/', $restrictions, $restrictions_matches);
    
    if(isset($restrictions_matches[1])){
        $restrictionTime = $restrictions_matches[1];
    }
    if(isset($restrictions_matches[2])){
        $restrictionDayOffset = $restrictions_matches[2];
    }
}


$currentDate = $restrictionDayOffset ? date('Y-m-d', strtotime('+' . $restrictionDayOffset . ' day')) : date('Y-m-d');

if($currentDate < $date){
    return $input;
}

$currentTime = date('H:i');
if($currentTime < $restrictionTime){
    return date('d.m.Y', strtotime($currentDate));
}

return date('d.m.Y', strtotime('+1 day', strtotime($currentDate)));