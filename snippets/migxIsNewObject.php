id: 49
name: migxIsNewObject
category: MIGX
properties: null

-----

if (isset($_REQUEST['object_id']) && $_REQUEST['object_id']=='new'){
    return 1;
}