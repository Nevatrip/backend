id: 51
name: getStartTimeAPI
description: '[[!getStartTimeAPI? &id=`[[*id]]`]]'
properties: 'a:0:{}'

-----

include_once MODX_CORE_PATH . 'components/nevatravel/model/nevatravel/nevatravel.class.php';

function apiCode( $trip_id, $trip_date, $tickets = [], &$modx ) {
  $partner_api = NTCRestPartnerApi::instance();
  $partner_api->setAuthKey( 'API-098dcc3f7ac5a1dfd0d32cf0699b18ea-1470922460' );

  $schedule_date = date("Y-m-d", strtotime( $trip_date ) );
  $program_id = $modx->getObject( 'modTemplateVarResource', array( 'tmplvarid' => 48, 'contentid' => $trip_id ) )->get( 'value' );
  $pier_departure_id = $modx->getObject( 'modTemplateVarResource', array('tmplvarid' => 49, 'contentid' => $trip_id ) )->get( 'value' );

  $cruises = $partner_api->getCruisesInfo( $schedule_date, $program_id, $pier_departure_id );
  $request = $partner_api->requestOrder( $tickets, '', $cruises[0]->id, $schedule_date, $program_id, $pier_departure_id );
  $approve = $partner_api->approveOrder( $request->ticket_token, false );

  return $approve->number;
}

return $id;