id: 11
source: 1
name: CheckTripOrder
category: 'Trip Orders'
properties: 'a:0:{}'

-----

$log_target = array(
    'target'=>'FILE',
    'options' => array(
        'filename'=>'ya_check.log'
    )
); 
$modx->log(modX::LOG_LEVEL_INFO, json_encode($_POST), $log_target); 

$shopId = 30518;
//$scid = 59376;
$scid = 21984;

$performedDatetime = date(DATE_ATOM);

if(empty($_POST['action']) && $_POST['action'] != 'checkOrder'){
    $output = '<checkOrderResponse code="200" performedDatetime="' . $performedDatetime . '" shopId="' . $shopId . '"/>';
    $modx->log(modX::LOG_LEVEL_INFO, $output, $log_target); 
    return $output;
}

$forSave = array();
$id = $_POST['orderNumber'];
$status = 'checkOrder';
$invoiceId = $_POST['invoiceId'];
$modified = date('Y-m-d H:i:s');

$sql = "UPDATE modx_trip_orders SET `status`='$status', `invoiceId`=$invoiceId, `modified`='$modified' WHERE id=$id;";

$query = new xPDOCriteria($modx, $sql, $forSave);

if ($query->prepare() && $query->stmt->execute()){
    $output = '<checkOrderResponse code="0" performedDatetime="' . $performedDatetime . '" invoiceId="' .$invoiceId . '" shopId="' . $shopId . '"/>';
    $modx->log(modX::LOG_LEVEL_INFO, $output, $log_target); 
    return $output;
} else{
    $output = '<checkOrderResponse code="100" performedDatetime="' . $performedDatetime . '" invoiceId="' . $invoiceId . '" shopId="' . $shopId . '"/>';
    $modx->log(modX::LOG_LEVEL_INFO, $output, $log_target); 
    return $output;
}