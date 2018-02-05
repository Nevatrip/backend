id: 10
source: 1
name: TripOrders
category: 'Trip Orders'
properties: 'a:0:{}'

-----

$start = microtime( true );

$date_type = empty( $_GET[ 'date_type' ] )
           ? 'created'
           : $_GET[ 'date_type' ];

$date_from = empty( $_GET[ 'date_from' ] )
           ? date( 'Y-m-d' )
           : date( 'Y-m-d', strtotime( $_GET[ 'date_from' ] ) );

$date_to   = empty( $_GET[ 'date_to' ] )
           ? date( 'Y-m-d', strtotime( '+1 day' ) )
           : date( 'Y-m-d', strtotime( '+1 day', strtotime( $_GET[ 'date_to' ] ) ) );

$sort_by         = 'id';
$trip_selected   = 'Все экскурсии';
$status_pay_name = 'любой';
$date_type_name  = '';

$trip_id   = empty( $_GET[ 'trip_id' ] )
           ? 'all'
           : $_GET[ 'trip_id' ];

$status_payment = empty( $_GET[ 'status_payment' ] )
                ? 'all'
                : $_GET[ 'status_payment' ];

switch ( $date_type ) {
  case 'trip_date':
    $condition = "`trip_date` != '' AND DATE_FORMAT( STR_TO_DATE( trip_date, '%d.%m.%Y' ), '%Y-%m-%d' ) >= '" . $date_from . "' AND DATE_FORMAT( STR_TO_DATE( trip_date, '%d.%m.%Y' ), '%Y-%m-%d' ) < '" . $date_to . "'";
    $sort_by   = "STR_TO_DATE( trip_date, '%d.%m.%Y' )";
    $date_type_name = 'по дате поездки';
    break;
  default:
    $condition = "`created` >= '" . $date_from . "' AND `created` < '" . $date_to . "'";
    $sort_by   = 'id';
    $date_type_name = 'по дате создания';
    break;
}

if ( $trip_id != 'all' ) {
  $condition    .= " AND `trip_id` = '" . $trip_id . "'";
  $trip_selected = $modx->getObject( 'modResource', $trip_id )->get( 'pagetitle' );
}

switch ( $status_payment ) {
  case 'paid':
    $condition .= " AND `status` = 'paymentAviso' OR `status` = 'promocode' AND `code` != ''";
    $status_pay_name = 'оплаченные';
    break;
  case 'unpaid':
    $condition .= " AND `status` != 'paymentAviso' OR `code` = ''";
    $status_pay_name = 'неоплаченные';
    break;
  case 'promo':
    $condition .= " AND `status` = 'promo' AND `code` != ''";
    $status_pay_name = 'промокод';
    break;
  default:
    break;
}

$sql = "SELECT * FROM modx_trip_orders WHERE " . $condition . " ORDER BY " . $sort_by . " DESC;";

$q = $modx->prepare( $sql );
if ( $q->execute() ) {
  $orders = $q->fetchAll( PDO::FETCH_ASSOC );
  $out    = '';
  $idx              = 1; // количество заказов
  $total_sum        = 0; // общая сумма всех билетов
  $total_tickets_1a = 0; // общее количество взрослых билетов
  $total_tickets_1d = 0; // общее количество льготных билетов
  $total_tickets_1c = 0; // общее количество детских билетов
  $total_tickets_2a = 0; // общее количество взрослых билетов 2-го типа
  $total_tickets_2d = 0; // общее количество льготных билетов 2-го типа
  $total_tickets_2c = 0; // общее количество детских билетов 2-го типа
  $total_tickets    = 0; // общее количество билетов

  foreach( $orders as $order ){
    $order[ 'idx' ]       = $idx++;
    $order[ 'order_id' ]  = $order[ 'id' ];
    $total_sum           += intval( $order[ 'sum' ] );
    $total_tickets_1a    += intval( $order[ 'ticket_1_adult_c'] );
    $total_tickets_1d    += intval( $order[ 'ticket_1_discount_c'] );
    $total_tickets_1c    += intval( $order[ 'ticket_1_child_c'] );
    $total_tickets_2a    += intval( $order[ 'ticket_2_adult_c'] );
    $total_tickets_2d    += intval( $order[ 'ticket_2_discount_c'] );
    $total_tickets_2c    += intval( $order[ 'ticket_2_child_c'] );

    $trip_object = $modx->getObject( 'modResource', $order[ 'trip_id' ] );
    
    $to_remove = array( '&nbsp;' );
    $order[ 'trip_name' ] = str_replace( $to_remove,
      '',
      $trip_object->get( 'pagetitle' ) );

    $order[ 'trip_link' ] = $modx->makeUrl( $order[ 'trip_id' ], '', '', 'full' );
    $order[ 'trip_mgr' ]  = '//nevatrip.ru/manager/?a=resource/update&id=' . $order[ 'trip_id' ];
    
    switch ( $order[ 'status' ] ) {
        case 'new':
          $order[ 'status' ] = 'новый';
          break;
        case 'paymentAviso':
          $order[ 'status' ] = 'Aviso';
          break;
        case 'promo':
          $order[ 'status' ] = 'Промокод';
          break;
        case 'checkOrder':
          $order[ 'status' ] = 'checkOrder';
          break;
        default:
          $order[ 'status' ] = 'неизвестно (' . $order[ 'status' ] . ')';
          break;
    }
    
    foreach ( $order as $key => $value ) {
      if ( is_null( $value ) ) {
        $order[ $key ] = '';
      }
    }
    
    $out .= $modx->getChunk( $tpl, $order );
  }

  $total_tickets = $total_tickets_1a + $total_tickets_1d + $total_tickets_1c + $total_tickets_2a + $total_tickets_2d + $total_tickets_2c;

  $modx->setPlaceholders(array(
    'date.from'        => date( 'd.m.Y', strtotime( $date_from ) )
  , 'date.to'          => date( 'd.m.Y', strtotime( '-1 day', strtotime( $date_to ) ) )
  , 'trip.id'          => $trip_id
  , 'trip_selected'    => $trip_selected
  , 'status_pay_name'  => $status_pay_name
  , 'date_type_name'   => $date_type_name
  , 'count'            => count( $orders )
  , 'timer'            => sprintf( '%.4F', microtime( true ) - $start )
  , 'date_type'        => $date_type
  , 'status_payment'   => $status_payment
  , 'total_sum'        => $total_sum
  , 'total_tickets_1a' => $total_tickets_1a
  , 'total_tickets_1d' => $total_tickets_1d
  , 'total_tickets_1c' => $total_tickets_1c
  , 'total_tickets_2a' => $total_tickets_2a
  , 'total_tickets_2d' => $total_tickets_2d
  , 'total_tickets_2c' => $total_tickets_2c
  , 'total_tickets'    => $total_tickets
  ),'');
  
  $ready = $modx->getChunk( $tplWrapper, array( 'output' => $out ) );
  if ($_GET['format'] == 'csv') {
      $ready = iconv('UTF-8', 'CP1251//TRANSLIT', $ready);
  }
  return $ready;
}