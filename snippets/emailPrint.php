id: 32
name: emailPrint
category: 'Trip Orders'
properties: 'a:0:{}'

-----

if( isset( $_GET['trip'] ) && isset( $_GET['hash'] ) ) {
  $id = $_GET['trip'];
  $hash = $_GET['hash'];

  $sql = "SELECT * FROM modx_trip_orders WHERE id=$id;";

  $query = new xPDOCriteria($modx, $sql, array());

  if (!$query->prepare() || !$query->stmt->execute()){
    return;
  }

  $result = $query->stmt->fetch(PDO::FETCH_ASSOC);

  if( md5( strtolower( $result['cps_email'] ) ) === $_GET['hash'] ) {

    $result['total_tickets_c'] = 
        $result['ticket_1_adult_c'] 
      + $result['ticket_1_discount_c'] 
      + $result['ticket_1_child_c'] 
      + $result['ticket_2_adult_c'] 
      + $result['ticket_2_discount_c'] 
      + $result['ticket_2_child_c'];

    $result['e_from']     = '';
    $result['map_static'] = '';
    $result['date_ago']   = '';

    $trip_object = $modx->getObject('modResource', $result['trip_id']);
    $trip_name   = $trip_object->get('pagetitle');
    $trip_parent = $trip_object->get('parent');

    $result['from_photo'] = $modx->getOption('site_url') . $trip_object->getTVValue('e_from_photo');
    $e_firm_name = $trip_object->getTVValue('e_firm_name');

    if ($result['trip_id'] === '14' && $result['trip_points'] === 'Петергоф-СПб') {
      $result['e_from']     = 'Нижний парк Петергофа, в конце центральной аллеи (касса № 1 «Нева Тревел»)';
      $result['map_link']   = 'https://maps.yandex.ru/?um=iLmRusRXjj6-QdtlkbZzCD4BinElWdma&l=map';
      $result['from_photo'] = $modx->getOption('site_url') . 'assets/img/e_from_photo/petergof-spb.jpg';
    }

    if ($trip_parent === 9 && $result['trip_id'] != '11' && $result['trip_id'] != '12' && $result['trip_id'] != '83' && $result['trip_id'] != '92' && $result['trip_id'] != '10' && $result['trip_id'] != '99' && $result['trip_id'] != '100' && $result['trip_id'] != '103') {
      $trip_date = DateTime::createFromFormat('d.m.Y', $result['trip_date']);
      date_sub($trip_date, date_interval_create_from_date_string('1 day'));
      $result['date_ago'] = $trip_date->format('d.m.Y');
    }

    foreach ($result as $key => $value) {
      if (is_null($value)) {
        $result[$key] = "";
      }
    }

    return $modx->getChunk('tpl_email_SuccessPayment_client', $result);

  } else {
    return 'Ошибка контрольной суммы';
  }

} else {
  return 'Не передан параметр';
}