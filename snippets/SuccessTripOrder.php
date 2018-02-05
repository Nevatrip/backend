id: 14
source: 1
name: SuccessTripOrder
category: 'Trip Orders'
properties: 'a:0:{}'

-----

$log_target = array(
    'target'=>'FILE',
    'options' => array(
        'filename'=>'ya_success.log'
    )
); 
$modx->log(modX::LOG_LEVEL_INFO, json_encode($_GET), $log_target);


$id = $_GET['orderNumber'];


$sql = "SELECT * FROM modx_trip_orders WHERE id=$id;";

$query = new xPDOCriteria($modx, $sql, array());

if (!$query->prepare() || !$query->stmt->execute()){
    return;
}

$result = $query->stmt->fetch(PDO::FETCH_ASSOC);

if(empty($result)){
    return;
}

return $modx->getChunk('tpl_SuccessPayment', $result);