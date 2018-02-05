id: 6
source: 1
name: NewTripOrder
category: 'Trip Orders'
properties: 'a:0:{}'

-----

include_once MODX_CORE_PATH . 'components/nevatravel/model/nevatravel/nevatravel.class.php';

$table = 'modx_trip_orders';

$data = $_POST;

$data['ticket_1_adult_c']    = '';
$data['ticket_1_discount_c'] = '';
$data['ticket_1_child_c']    = '';
$data['ticket_2_adult_c']    = '';
$data['ticket_2_discount_c'] = '';
$data['ticket_2_child_c']    = '';

if ( isset( $data['trip_points_nevatravel'] ) ) {
  $data['trip_points_nevatravel'] = implode(", ", $data['trip_points_nevatravel']);
}

$isFullDiscount = false;

function checkIsFullDiscount($promocode, $resource_id, &$discount, &$modx){
  $discount = 0;
    /*
     * Check promocode
     */
    if(!empty($promocode)){
        // Get promocodes by resource id
      $e_promocodes = $modx->getObject('modTemplateVarResource', array(
        'tmplvarid' => 28,
        'contentid' => $resource_id
      ));

      if($e_promocodes){
        $e_promocodes = mb_strtolower($e_promocodes->get('value'), 'UTF-8');
        preg_match('/\[' . $promocode . '\]\(([0-9]+)\)/', $e_promocodes, $matches);
        if(isset($matches[1])){
          $discount = $matches[1];
          if($matches[1] == 100){
            return true;
          }
        }
      }
    }
    
    return false;
  }

  function genCode( $length, $onlyNumbers = false ) {
    $characters = !$onlyNumbers ? '0123456789abcdefghijklmnopqrstuvwxyz' : '0123456789';
    $string = '';
    $randto = strlen($characters) - 1;
    for($p = 0; $p < $length; $p++){
      $string .= $characters[mt_rand(0, $randto)];
    }
    
    return $string;
  }

  function apiCode( $trip_id, $trip_date, $points = null, $tickets = [], &$modx ) {
    $partner_api = NTCRestPartnerApi::instance();
    $partner_api->setAuthKey( 'API-098dcc3f7ac5a1dfd0d32cf0699b18ea-1470922460' );
//  $partner_api->setAuthKey( 'API-b12430747caa88bcdd00651ab5d277f7-1481182680' );
//  $partner_api->setSandboxMode();

    $schedule_date = $trip_id == '26' ? date("Y-m-d", strtotime( $trip_date . ' -1 day' ) ) : date("Y-m-d", strtotime( $trip_date ) );

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

/*
 * Check total sum
 */
if(empty($data['sum']) || !empty($data['promocode'])){
  $isFullDiscount = checkIsFullDiscount(mb_strtolower($data['promocode'], 'UTF-8'), $data['trip_id'], $discount, $modx);

  if(empty($data['sum']) && !$isFullDiscount){
    $realSum = 0;
    foreach($data['tickets'] as $ticket){
      $realSum += $ticket['price'] * $ticket['c'];
    }
    $realSum = ceil(($realSum * (100 - $discount)) / 100);
    $data['sum'] = $realSum;
  }
  if($isFullDiscount){
    $data['sum'] = 0;
  }

}
/*
 * Transform tickets to another format
 */
foreach($data['tickets'] as $name => $ticket){
  if($ticket['c'] > 0){
    $data[$name . '_price'] = $ticket['price'];
    $data[$name . '_c'] = $ticket['c'];
  }
}
unset($data['tickets']);

/*
 * Add more information
 */
$data['customerNumber'] = $data['cps_email'];
$data['created'] = date('Y-m-d H:i:s');

if ($data['sum'] == 0 && checkIsFullDiscount(mb_strtolower($data['promocode'], 'UTF-8'), $data['trip_id'], $discount, $modx)) {
  $data['status'] = 'promo';
  $data['code'] = $code = $modx->getObject('modResource', $data['trip_id'])->getTVValue('program_id')
  ? apiCode( $data['trip_id'], $data['trip_date'], $data['trip_points'], [ 'full'=>$data['ticket_1_adult_c'], 'half'=>$data['ticket_1_discount_c'], 'children'=>$data['ticket_1_child_c'], 'attendant'=>0 ], $modx )
  : genCode(6, true);


} else {
  $data['status'] = 'new';
}

/*
 * Save to DB
 */
$fields = implode(',', array_keys($data));

$forSave = array();
foreach($data as $field => $value){
  $forSave[':' . $field] = $value;
}

$ticketsTemplate = [
  [ "Взрослый билет на водную прогулку", "adult" ],
  [ "Льготный билет на водную прогулку", "discount" ],
  [ "Детский билет на водную прогулку", "child" ]
];

$items = array();

foreach ($ticketsTemplate as $key => $value) {
  if ( !empty( $data[ "ticket_1_" . $value[ 1 ] . "_c" ] ) ) {
    $items[] = array(
      "text" => $value[ 0 ] . " «" . $modx->getObject('modResource', $data["trip_id"])->get("pagetitle") . "»",
      "tax" => 1,
      "quantity" => (int)$data["ticket_1_" . $value[ 1 ] . "_c"],
      "price" => array(
        "amount" => (int)$data["ticket_1_" . $value[ 1 ] . "_price"]
      )
    );
  }
}

$ym_merchant_receipt = array(
  "customerContact" => "+79219653404",
  "items" => $items
);

$data["ym_merchant_receipt"] = json_encode( $ym_merchant_receipt );

$values = implode(',', array_keys($forSave));

$sql = "INSERT INTO modx_trip_orders(" . $fields . ") VALUES(" . $values . ");";

$query = new xPDOCriteria($modx, $sql, $forSave);

if ($query->prepare() && $query->stmt->execute()){
  /*
   * If success save show form for redirect to payment system
   */
  $data['orderNumber'] = $modx->lastInsertId();
  if ($data['status'] == 'promo') {

    $data['trip_time'] = '';
    $data['date_ago'] = '';
    $data['e_from'] = $modx->getObject('modTemplateVarResource', array('tmplvarid' => 9, 'contentid' => $data['trip_id']))->get('value');
    $data['total_tickets_c'] = $data['ticket_1_adult_c']+$data['ticket_1_discount_c']+$data['ticket_1_child_c']+$data['ticket_2_adult_c']+$data['ticket_2_discount_c']+$data['ticket_2_child_c'];

    $tripObject = $modx->getObject('modResource', $data['trip_id']);
    $tripTitle = $tripObject->get('pagetitle');

    foreach ($data as $key => $value) {
      if (is_null($value)) {
        $data[$key] = "";
      }
    }

    $messageToManager = $modx->getChunk('tpl_email_SuccessPayment_operator', $data);

    $modx->getService('mail', 'mail.modPHPMailer');
    $modx->mail->set(modMail::MAIL_BODY, $messageToManager);
    $modx->mail->set(modMail::MAIL_FROM, $modx->getOption('emailsender'));
    $modx->mail->set(modMail::MAIL_FROM_NAME, $modx->getOption('site_name'));
    $modx->mail->set(modMail::MAIL_SUBJECT, 'Заказ билетов на «' . $tripTitle . '» № НТ' . $data['code']);
    $modx->mail->address('to', 'info@nevatrip.ru');
    $modx->mail->setHTML(true);
    if (!$modx->mail->send()) {
      $modx->log(modX::LOG_LEVEL_ERROR, 'An error occurred while trying to send the email: '.$modx->mail->mailer->ErrorInfo);
    }
    $modx->mail->reset();

    return 'Ваша заявка по промокоду успешно принята. Номер заказа: № ' . $data['code'] . '. Спасибо, что выбрали нас. <a href="/payment/success?orderNumber=' . $data['orderNumber'] . '">Открыть билет</a>.';
  } else {
    unset($data['status']);
    unset($data['created']);

    $inputs = '';
    foreach($data as $name => $value){
      $inputs .= $modx->getChunk('tpl_FormHiddenInput', compact('name', 'value'));
    }
    $form = $modx->getChunk('tpl_FormYaOrder', compact('inputs'));

    return $form;
  }
}