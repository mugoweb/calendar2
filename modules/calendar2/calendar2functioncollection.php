<?php

class Calendar2FunctionCollection
{
    public static function eventList( $calendarNodeID = 2, $categories = array(), $limit = 5 )
    {
        $filter = array();
        $filter['calendar_node_id'] = intval( $calendarNodeID );
        $filter['limit'] = intval( $limit );
        
        if( is_array( $categories ) )
        {
            $filter['categories'] = $categories;
        }
        else
        {
            $filter['categories'] = array( $categories );
        }

        $result = calendar2EventHandler::fetch( $filter );
        
        // Add the actual content node object to the result
        foreach( $result as $i => $row )
        {
            $result[$i]['node'] = eZContentObjectTreeNode::fetch( $row['node_id'] );
        }

        return array( 'result' => $result );
    }
}

?>