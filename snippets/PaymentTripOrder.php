id: 12
source: 1
name: PaymentTripOrder
category: 'Trip Orders'
properties: 'a:0:{}'

-----

include_once MODX_CORE_PATH . 'components/nevatravel/model/nevatravel/nevatravel.class.php';

function genCode($length, $onlyNumbers = false){
  $characters = !$onlyNumbers ? '0123456789abcdefghijklmnopqrstuvwxyz' : '0123456789';
  $string = '';
  $randto = strlen($characters) - 1;
  for($p = 0; $p < $length; $p++){
    $string .= $characters[mt_rand(0, $randto)];
  }

  return $string;
}

function apiCode( $trip_id, $trip_date, $points = null, $tickets = [], $cruise_id = null, &$modx ) {
  $partner_api = NTCRestPartnerApi::instance();
  $partner_api->setAuthKey( 'API-098dcc3f7ac5a1dfd0d32cf0699b18ea-1470922460' );
//  $partner_api->setAuthKey( 'API-b12430747caa88bcdd00651ab5d277f7-1481182680' );
//  $partner_api->setSandboxMode();

  $schedule_date = $trip_id == '26' ? date("Y-m-d", strtotime( $trip_date . ' -1 day' ) ) : date("Y-m-d", strtotime( $trip_date ) );

  if ( isset( $cruise_id ) ) {
    $cruise_id = explode( "," , preg_replace( "/\s/", "", $cruise_id ) );
    $request = $partner_api->requestOrder( $tickets, $cruise_id[1], $cruise_id[0] );
  } else {
    $res = $modx->getObject('modResource', $trip_id);
    $program_id_arr = explode( "," , preg_replace( "/\s/", "", $res->getTVValue( "program_id" ) ) );

    $program_id = $points == "Петергоф-СПб" ? $program_id_arr[1] : $program_id_arr[0];

    $pier_departure_id_arr = explode( "," , preg_replace( "/\s/", "", $res->getTVValue( "pier_departure_id" ) ) );

    $pier_departure_id = $points == "Петергоф-СПб" ? $pier_departure_id_arr[1] : $pier_departure_id_arr[0];

    $cruises = $partner_api->getCruisesInfo( $schedule_date, $program_id, $pier_departure_id );

    $backTrip = $points == "СПб-Петергоф — Петергоф-СПб" ? $cruises[0]->back_cruises[0]->program_id : '';
    
    if ( $res->getTVValue( 'fixed-time' ) ) {
      $request = $partner_api->requestOrder( $tickets, $backTrip, $cruises[0]->id );
    } else {
      $request = $partner_api->requestOrderOpenTime( $tickets, $schedule_date, $program_id, $pier_departure_id, $backTrip );
    }
  }

  $number='¯\_(ツ)_/¯';
  
  if ( $request->back_ticket_token ) {
    $approve = $partner_api->approveOrder( $request->ticket_token, false );
    $number1 = $approve->number;
    $approveBack = $partner_api->approveOrder( $request->back_ticket_token, false );
    $number2 = $approveBack->number;
    $number = $number1 . ', ' . $number2;
  } else {
    $approve = $partner_api->approveOrder( $request->ticket_token, false );
    $number = $approve->number;
  }

  return $number;
}

$id = $_POST['orderNumber'];
$status = $_POST['action'];
$invoiceId = $_POST['invoiceId'];

$shopId = 30518;
$scid = 21984;
$performedDatetime = date(DATE_ATOM);


if(empty($status) && $status != 'paymentAviso'){
  return '<checkOrderResponse code="200" shopId="' . $shopId . '"/>';
}

$query = new xPDOCriteria($modx, "SELECT * FROM modx_trip_orders WHERE id=$id;", array());

if (!$query->prepare() || !$query->stmt->execute()){
  return '<checkOrderResponse code="200" shopId="' . $shopId . '"/>';
}

$result = $query->stmt->fetch(PDO::FETCH_ASSOC);

if($result['status'] == 'paymentAviso'){
  return '<paymentAvisoResponse performedDatetime="' . $performedDatetime . '" code="0" invoiceId="' . $invoiceId . '" shopId="' . $shopId . '"/>';
}

$modified = date('Y-m-d H:i:s');
$code = $modx->getObject('modResource', $result['trip_id'])->getTVValue('program_id')
  ? apiCode( $result['trip_id'], $result['trip_date'], $result['trip_points'], [ 'full'=>$result['ticket_1_adult_c'], 'half'=>$result['ticket_1_discount_c'], 'children'=>$result['ticket_1_child_c'], 'attendant'=>0 ], $result['trip_points_nevatravel'], $modx )
  : genCode(6, true);

$query = new xPDOCriteria($modx, "UPDATE modx_trip_orders SET `status`='paymentAviso', `modified`='$modified', `code`='$code' WHERE id=$id;", array());

if ($query->prepare() && $query->stmt->execute()){
  $modx->runSnippet('PaymentTripOrder_SuccessEmails', compact('id'));
  return '<paymentAvisoResponse performedDatetime="' . $performedDatetime . '" code="0" invoiceId="' . $invoiceId . '" shopId="' . $shopId . '"/>';
} else{
  return '<paymentAvisoResponse performedDatetime="' . $performedDatetime . '" code="100" invoiceId="' . $invoiceId . '" shopId="' . $shopId . '"/>';
}