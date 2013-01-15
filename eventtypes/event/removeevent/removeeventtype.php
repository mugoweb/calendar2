<?php

class removeeventType extends eZWorkflowEventType
{
    const WORKFLOW_TYPE_ID = 'removeevent';

    function __construct()
    {
        parent::__construct( removeeventType::WORKFLOW_TYPE_ID, 'Remove event' );
        $this->setTriggerTypes( array( 'content' => array( 'delete' => array( 'before' ) ) ) );
    }

    function execute( $process, $event )
    {
        $db = eZDB::instance();

        $parameters = $process->attribute( 'parameter_list' );
        
        foreach( $parameters['node_id_list'] as $nodeID )
        {
            $node = eZContentObjectTreeNode::fetch( $nodeID );
        
            if( 'calendar2event' == $node->attribute( 'class_identifier' ) )
            {
                calendar2EventHandler::remove( $node );
            }
        }

        return eZWorkflowType::STATUS_ACCEPTED;
    }
}

eZWorkflowEventType::registerEventType( removeeventType::WORKFLOW_TYPE_ID, 'removeeventtype' );

?>