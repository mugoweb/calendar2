<?php

class calendar2EventHandler
{
    
    static public function fetch( $filter )
    {
        /*
         * build where statement
         */ 
        $where  = 'WHERE ';
        
        // handle calendar id
        $where .= 'calendar_node_id = ' . (int)$filter[ 'calendar_node_id' ] . ' AND ';
        
        // handle start/end date
        // This is to provide a list of events between a certain date
        if( isset( $filter['start'] ) && isset( $filter['end'] ) )
        {
            $where .= 'end >= ' . (int)$filter[ 'start' ] . ' AND ';
            $where .= 'start <= '  . (int)$filter[ 'end' ]   . ' AND ';
            $orderBy = '';
        }
        // This is to provide a list of upcoming events
        else
        {
            $where .= 'end >= ' . strtotime( 'now -1 day' ) . ' AND ';
            $orderBy = 'ORDER BY start ASC ';
        }
        

        //handle categories
        if( count( $filter[ 'categories' ] ) )
        {
            $where .= 'calendar2event_category.category_id IN ( ' . implode( ',', $filter[ 'categories' ] ) . ' ) AND ';
        }
        
        // close where statemente
        $where .= '1=1 ';
        
        if( isset( $filter['limit'] ) )
        {
            $limit = 'LIMIT ' . intval( $filter['limit'] );
        }
        else
        {
            $limit = '';
        }
        
        /*
         * run query
         */
        $db = eZDB::instance();

        $query  = 'SELECT calendar2event.*, GROUP_CONCAT( calendar2event_category.category_id ) AS categories FROM calendar2event ';
        $query .= 'LEFT JOIN calendar2event_category ON ( calendar2event_category.event_id = calendar2event.node_id ) ';
        $query .= $where;
        $query .= 'GROUP BY calendar2event.node_id, calendar2event.start ';
        $query .= $orderBy;
        $query .= $limit;

        return $db->arrayQuery( $query );
    }

    static public function remove_all()
    {
        // reset events table
        $db = eZDB::instance();
        $query = 'DELETE FROM calendar2event';
        $db->query( $query );
        
        // reset category table
        $query = 'DELETE FROM calendar2event_category';
        $db->query( $query );
    }

    static public function remove( $event )
    {
        if( is_object( $event ) )
        {
            // reset events table
            $db = eZDB::instance();
            $query = 'DELETE FROM calendar2event WHERE node_id = ' . (int) $event->attribute( 'node_id' );
            $db->query( $query );
            
            // reset category table
            $query = 'DELETE FROM calendar2event_category WHERE event_id = ' . (int) $event->attribute( 'node_id' );
            $db->query( $query );
        }
    }
    
    static public function add( $event )
    {
        $data_map = $event->attribute( 'data_map' );
        
        if( calendar2EventHandler::is_recurring( $event ) )
        {
            calendar2EventHandler::add_recurring_event( $event );
        }
        else
        {
            calendar2EventHandler::add_single_event( $event );
        }
    }

    static public function add_single_event( $event )
    {
        $row = array();
        $data_map = $event->attribute( 'data_map' );
        
        $row = calendar2EventHandler::build_row( $event );
        
        $row[ 'start' ]       = $data_map[ 'start' ]->toString();
        $row[ 'end' ]         = $data_map[ 'end' ]->toString();
        
        calendar2EventHandler::store( $row );
        
        calendar2EventHandler::store_categories( $event );
    }

    static public function add_recurring_event( $event )
    {
        $data_map = $event->attribute( 'data_map' );
        $content = $data_map[ 'recurring' ]->attribute( 'content' );
        
        switch( $content[ 'type' ] )
        {
            //daily events
            case '0':
            {
                calendar2EventHandler::add_daily_recurring_event( $event );
            }
            break;

            // weekly events
            case '1':
            {
                calendar2EventHandler::add_weekly_recurring_event( $event );
            }
            break;

            case '2':
            {
                calendar2EventHandler::add_monthly_recurring_event( $event );
            }
            break;

            case '3':
            {
                calendar2EventHandler::add_yearly_recurring_event( $event );
            }
            break;
            
            default:
        }
        
        calendar2EventHandler::store_categories( $event );
    }
    
    static private function build_calendar_content( $data_map )
    {
        $return = 'my hardcoded text';
        
        return $return;
    }
    
    static private function store( $row )
    {
        $db = eZDB::instance();
        
        foreach( $row as $key => $value )
        {
            switch( $key )
            {
                case 'title':
                case 'content':
                {
                    $row[ $key ] = '"' . $db->escapeString( $value ) . '"' ;
                }
                break;
                
                default:
                {
                    $row[ $key ] = (int)$value;
                }
            }
        }
        
        
        $sql  = 'INSERT INTO calendar2event ( ' . implode( ',', array_keys( $row ) ) . ') ';
        $sql .= 'VALUES ( ' . implode( ',', $row ) . ')';
        
        //echo $sql;
        $db->query( $sql );
    }
    
    static public function is_recurring( $event )
    {
        $data_map = $event->attribute( 'data_map' );
        
        $content = $data_map[ 'recurring' ]->attribute( 'content' );

        return $content[ 'enabled' ] ? true : false;
    }
    
    static private function add_weekly_recurring_event( $event )
    {
        // basic vars
        $data_map   = $event->attribute( 'data_map' );
        $content    = $data_map[ 'recurring' ]->attribute( 'content' );
        $definition = $content[ '1' ];
        $factor     = (int) $definition[ 'factor' ] ? (int) $definition[ 'factor' ] : 1;

        $single_start = $data_map[ 'start' ]->toString();
        $single_end   = $data_map[ 'end' ]->toString();

        $row = calendar2EventHandler::build_row( $event );

        // get end condition
        $max_iterations = calendar2EventHandler::get_max_iterations( $content );
        $max_date       = calendar2EventHandler::get_max_date( $content );
        
        // do the magic loop
        $start_week = calendar2EventHandler::get_iterator_start_date( $single_start, 'week' );

        $i = 0;
        while( $i < $max_iterations )
        {
            $iteration_week = strtotime( '+' . ( $i * $factor ) . 'week', $start_week );
            
            foreach( $definition[ 'day' ] as $day_nr => $value )
            {
                $start_diff_text = '+' . $day_nr . 'day +' . date( 'G', $single_start ) . ' hour +' . date( 'i', $single_start ) . ' minute +' . date( 's', $single_start ) . ' second';
                $end_diff_text   = '+' . $day_nr . 'day +' . date( 'G', $single_end )   . ' hour +' . date( 'i', $single_end )   . ' minute +' . date( 's', $single_end )   . ' second';
                $start_date      = strtotime( $start_diff_text, $iteration_week );
                $end_date        = strtotime( $end_diff_text, $iteration_week );
                
                if( $start_date >= $single_start && $start_date <= $max_date )
                {
                    $row[ 'start' ]       = $start_date;
                    $row[ 'end' ]         = $end_date;
                    
                    calendar2EventHandler::store( $row );
                }
            }
            
            $i++;
        }
    }
    
    static private function add_daily_recurring_event( $event )
    {
        // basic vars
        $data_map     = $event->attribute( 'data_map' );
        $content      = $data_map[ 'recurring' ]->attribute( 'content' );
        $definition   = $content[ '0' ];
        $single_start = $data_map[ 'start' ]->toString();
        $single_end   = $data_map[ 'end' ]->toString();
        $row          = calendar2EventHandler::build_row( $event );
        
        // get factor
        $factor = 1;
        if( $definition[ 'option' ] == 1 )
        {
            $factor = (int)$definition[ 'factor' ] ? (int)$definition[ 'factor' ] : 1;
        }
        
        // weekday filter
        $week_day_filter = array( 0,1,2,3,4,5,6 );
        if( $definition[ 'option' ] == 2 )
        {
            $week_day_filter = array( 1,2,3,4,5 );
        }
        
        // get end condition
        $max_iterations = calendar2EventHandler::get_max_iterations( $content );
        $max_date       = calendar2EventHandler::get_max_date( $content );
        
        // do the magic loop
        $start_iteration_date = calendar2EventHandler::get_iterator_start_date( $single_start, 'day' );

        $i = 0;
        while( $i < $max_iterations )
        {
            $iteration_date = strtotime( '+' . ( $i * $factor ) . 'day', $start_iteration_date );
            
            $start_diff_text = '+' . date( 'G', $single_start ) . ' hour +' . date( 'i', $single_start ) . ' minute +' . date( 's', $single_start ) . ' second';
            $end_diff_text   = '+' . date( 'G', $single_end )   . ' hour +' . date( 'i', $single_end )   . ' minute +' . date( 's', $single_end )   . ' second';
            $start_date      = strtotime( $start_diff_text, $iteration_date );
            $end_date        = strtotime( $end_diff_text, $iteration_date );
            
            //echo date( 'r', $start_date) . '--' . date('r', $single_start) . '--' . date('r', $max_date) . "\n";
            
            if( in_array( date( 'w', $start_date), $week_day_filter ) )
            {
                if( $start_date >= $single_start && $start_date <= $max_date )
                {
                    $row[ 'start' ]       = $start_date;
                    $row[ 'end' ]         = $end_date;
                    
                    calendar2EventHandler::store( $row );
                }
            }
                        
            $i++;
        }
    }

    static private function add_monthly_recurring_event( $event )
    {
        // basic vars
        $data_map     = $event->attribute( 'data_map' );
        $content      = $data_map[ 'recurring' ]->attribute( 'content' );
        $definition   = $content[ '2' ];
        $single_start = $data_map[ 'start' ]->toString();
        $single_end   = $data_map[ 'end' ]->toString();
        $row          = calendar2EventHandler::build_row( $event );
        
        // get factor
        $factor = (int) $definition[ 'factor' ] ? (int) $definition[ 'factor' ] : 1;

        // get end condition
        $max_iterations = calendar2EventHandler::get_max_iterations( $content );
        $max_date       = calendar2EventHandler::get_max_date( $content );
        
        // do the magic loop
        $start_iteration_date = calendar2EventHandler::get_iterator_start_date( $single_start, 'month' );

        // day of month - zero indexed
        $day_of_month = $definition[ 'day' ] - 1;
        
        $i = 0;
        while( $i < $max_iterations )
        {
            $iteration_date = strtotime( '+' . ( $i * $factor ) . 'month', $start_iteration_date );

            // clean up some strange php behaviour
            $iteration_date = calendar2EventHandler::get_iterator_start_date( ( $iteration_date ), 'month' );
                        
            $start_diff_text = '+' . $day_of_month . 'day +' . date( 'G', $single_start ) . ' hour +' . date( 'i', $single_start ) . ' minute +' . date( 's', $single_start ) . ' second';
            $end_diff_text   = '+' . $day_of_month . 'day +' . date( 'G', $single_end )   . ' hour +' . date( 'i', $single_end )   . ' minute +' . date( 's', $single_end )   . ' second';

            $start_date      = strtotime( $start_diff_text, $iteration_date );
            $end_date        = strtotime( $end_diff_text, $iteration_date );
            
            if( $start_date >= $single_start && $start_date <= $max_date )
            {
                $row[ 'start' ]       = $start_date;
                $row[ 'end' ]         = $end_date;
                
                calendar2EventHandler::store( $row );
            }
                        
            $i++;
        }
    }

    static private function add_yearly_recurring_event( $event )
    {
        // basic vars
        $data_map     = $event->attribute( 'data_map' );
        $content      = $data_map[ 'recurring' ]->attribute( 'content' );
        $definition   = $content[ '3' ];
        $single_start = $data_map[ 'start' ]->toString();
        $single_end   = $data_map[ 'end' ]->toString();
        $row          = calendar2EventHandler::build_row( $event );
        
        // get factor
        $factor = (int) $definition[ 'factor' ] ? (int) $definition[ 'factor' ] : 1;

        // get end condition
        $max_iterations = calendar2EventHandler::get_max_iterations( $content );
        $max_date       = calendar2EventHandler::get_max_date( $content );
        
        // do the magic loop
        $start_iteration_date = calendar2EventHandler::get_iterator_start_date( $single_start, 'year' );

        // day of month - zero indexed
        $day_of_month = $definition[ 'day' ] - 1;
        $month        = $definition[ 'month' ];
        
        $i = 0;
        while( $i < $max_iterations )
        {
            $iteration_date = strtotime( '+' . ( $i * $factor ) . 'year', $start_iteration_date );

            $start_diff_text = '+' . $month . 'month +' . $day_of_month . 'day +' . date( 'G', $single_start ) . ' hour +' . date( 'i', $single_start ) . ' minute +' . date( 's', $single_start ) . ' second';
            $end_diff_text   = '+' . $month . 'month +' . $day_of_month . 'day +' . date( 'G', $single_end )   . ' hour +' . date( 'i', $single_end )   . ' minute +' . date( 's', $single_end )   . ' second';

            $start_date      = strtotime( $start_diff_text, $iteration_date );
            $end_date        = strtotime( $end_diff_text, $iteration_date );
            
            if( $start_date >= $single_start && $start_date <= $max_date )
            {
                $row[ 'start' ]       = $start_date;
                $row[ 'end' ]         = $end_date;
                
                calendar2EventHandler::store( $row );
            }
                        
            $i++;
        }
    }
    
    static private function get_week_start_date( $timestamp )
    {
        $diff_text  = '-' . date( 'w', $timestamp ) . 'day -' . date( 'G', $timestamp ) . ' hour -' . date( 'i', $timestamp ) . ' minute -' . date( 's', $timestamp ) . ' second';

        return strtotime( $diff_text, $timestamp );
    }
    
    static private function get_iterator_start_date( $timestamp, $type )
    {
        switch( $type )
        {
            case 'day':
            {
                $diff_text  = '-' . date( 'G', $timestamp ) . ' hour -' . date( 'i', $timestamp ) . ' minute -' . date( 's', $timestamp ) . ' second';
            }
            break;
            
            case 'week':
            {
                $diff_text  = '-' . date( 'w', $timestamp ) . 'day -' . date( 'G', $timestamp ) . ' hour -' . date( 'i', $timestamp ) . ' minute -' . date( 's', $timestamp ) . ' second';
            }
            break;

            case 'month':
            {
                $diff_text  = '-' . ( date( 'j', $timestamp ) - 1 ) . 'day -' . date( 'G', $timestamp ) . ' hour -' . date( 'i', $timestamp ) . ' minute -' . date( 's', $timestamp ) . ' second';
                //echo date( 'r', $timestamp ) . '-' .$diff_text . "\n";
            }
            break;

            case 'year':
            {
                $diff_text  = '-' . date( 'z', $timestamp ) . 'day -' . date( 'G', $timestamp ) . ' hour -' . date( 'i', $timestamp ) . ' minute -' . date( 's', $timestamp ) . ' second';
            }
            break;
            
            default:
        }

        return strtotime( $diff_text, $timestamp );
    }
    
    static private function get_calendar_node_id( $node )
    {
        $return = 2;
        
        if( $node->attribute( 'node_id') != 2 )
        {
            $parent_node = $node->attribute( 'parent' );
            
            if( $parent_node->attribute( 'class_identifier' ) == 'calendar2' )
            {
                $return = $parent_node->attribute( 'node_id' );
            }
            else
            {
                $return = calendar2EventHandler::get_calendar_node_id( $parent_node );
            }
        }
        return $return;
    }
    
    static private function build_row( $event )
    {
        $data_map = $event->attribute( 'data_map' );
        
        $row = array();
        $row[ 'node_id' ]          = $event->attribute( 'node_id' );
        $row[ 'calendar_node_id' ] = calendar2EventHandler::get_calendar_node_id( $event );
        $row[ 'title' ]            = $data_map[ 'display_name' ]->toString();
        $row[ 'content' ]          = calendar2EventHandler::build_calendar_content( $data_map );
        $row[ 'all_day' ]          = $data_map[ 'all_day' ]->toString() ? 1 : 0;

        return $row;
    }
    
    static private function store_categories( $event )
    {
        $category_node_ids = array();
        
        $data_map = $event->attribute( 'data_map' );
        
        $categories = $data_map[ 'categories' ]->attribute( 'content' );
        $categories = $categories[ 'relation_list' ];
        
        if( count( $categories ) )
        {
            foreach( $categories as $category )
            {
                $category_node_ids[] = $category[ 'node_id' ];
            }
        
            $db = eZDB::instance();
            
            foreach( $category_node_ids as $node_id )
            {
                $sql  = 'INSERT INTO calendar2event_category ( event_id, category_id ) ';
                $sql .= 'VALUES ( '. $event->attribute( 'node_id' ) . ',' . $node_id . ' )';

                //echo $sql;
                $db->query( $sql );
            }
        }
    }
    
    static private function get_max_iterations( $content )
    {
        $return = 100;
        
        if( $content[ 'end' ][ 'option' ] == 2 )
        {
            $return = (int) $content[ 'end' ][ 'factor' ];
        }
        
        return $return;
    }

    static private function get_max_date( $content )
    {
        $return = strtotime( '+10 year', time() );
        
        if( $content[ 'end' ][ 'option' ] == 3 )
        {
            $return = strtotime( $content[ 'end' ][ 'date' ] . '+1 day' );
        }
        
        return $return;
    }
}

?>