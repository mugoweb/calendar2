<?php
$FunctionList = array();

$FunctionList['list'] = array(
                           'name' => 'list',
                           'call_method' => array( 
                                                  'include_file' => 'extension/calendar2/modules/calendar2/calendar2functioncollection.php',
                                                  'class' => 'Calendar2FunctionCollection',
                                                  'method' => 'eventList' ),
                           'parameter_type' => 'standard',
                           'parameters' => array(
                                                 array(  'name'     => 'calendar_node_id',
                                                         'type'     => 'integer',
                                                         'required' => true
                                                 ),
                                                 array(  'name'     => 'categories',
                                                         'type'     => 'mixed',
                                                         'default'  => array(),
                                                         'required' => false
                                                 ),
                                                 array(  'name'     => 'limit',
                                                         'type'     => 'integer',
                                                         'default'  => 5,
                                                         'required' => false
                                                 )
                                                 )
                                     );
?>