<?php

echo 'Starting...' . "\n";

$last_run = 1264731040;
$remove_all = false;

if( $remove_all )
{
	//handler remove all
}

$events = eZFunctionHandler::execute( 'content', 'tree', array( 'parent_node_id'     => 1,
                                                                'class_filter_type'  => 'include',
                                                                'class_filter_array' => array( 'calendar2event' ),
                                                                'limitation'         => array(),
                                                                'as_object'          => true ) ); 

if( count( $events ) )
{
	calendar2EventHandler::remove_all();
	
	foreach( $events as $event )
	{
		echo ' Event: '. $event->attribute( 'name' ) . "\n";
		calendar2EventHandler::add( $event );
	}
}
?>