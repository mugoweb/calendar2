<?php

define( 'EZ_WORKFLOW_TYPE_UPDATEEVENTTABLE_ID', 'updateeventtable' );

class updateeventtableType extends eZWorkflowEventType
{
    function __construct() 
    {
        $this->eZWorkflowEventType( EZ_WORKFLOW_TYPE_UPDATEEVENTTABLE_ID, 'Update events table' );
        $this->setTriggerTypes( array( 'content' => array( 'publish' => array( 'after' ), 'hide' => array( 'after' ) ) ) );
    }

    function execute( $process, $event ) 
    {
        $parameters = $process->attribute( 'parameter_list' );
        // Array ( [object_id] => 1 [version] => 4 [workflow_id] => 1 [user_id] => 14 )
        
        if( isset( $parameters['object_id'] ) )
        {
            $ez_obj = eZContentObject::fetch( $parameters['object_id'] );
        }
        else
        {
            $node = eZContentObjectTreeNode::fetch( $parameters['node_id'] );
            $ez_obj = $node->attribute( 'object' );
        }
        
        if( 'calendar2event' == $ez_obj->attribute( 'class_identifier' ) )
        {
            //TODO:: should loop over all node assignments
            $event = $ez_obj->attribute( 'main_node' );

            calendar2EventHandler::remove( $event );
            // Only add it to the calendar if it's visible
            if( 0 == $event->attribute( 'is_invisible' ) )
            {
                calendar2EventHandler::add( $event );
            }
        }
        // object_id is the content object of a node in ezp
        
        return eZWorkflowType::STATUS_ACCEPTED;
    }        
}    

eZWorkflowEventType::registerEventType( EZ_WORKFLOW_TYPE_UPDATEEVENTTABLE_ID, 'updateeventtabletype' );

?>