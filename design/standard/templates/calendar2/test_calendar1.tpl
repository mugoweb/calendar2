{ezcss_require( 'fullcalendar.css' )}
{ezcss_require( 'calendar2.css' )}

{def $categories = fetch( 'content', 'tree', hash( 'parent_node_id'     , 43,
                                                   'class_filter_type'  , 'include',
                                                   'class_filter_array' , array( 'calendar2category' )
                                                  ))}

<h2>Test Calendar 1<h2>

<div id="calendar2-filter">
	<h3>Search Events</h3>
	
	{foreach $categories as $category}
		<div class="calendar2category-{$category.node_id}">
			<label>{$category.name|wash}</lable>
			<input type="checkbox" class="calendar2category" value="{$category.node_id}" />
		</div>
	{/foreach}
	
	<button id="calendar2-refetch">Go</button>
</div>

<br />
<br />
<br />
<br />

<div id="calendar"></div>
<div id="loading">Loading...</div>

<script type="text/javascript">

var web_service_url = {'calendar2/fetch'|ezurl( 'single', 'full' )};
var calendar_node_id = 240;

{literal}
$(document).ready(function() {

	$('#calendar').fullCalendar({
		editable: false,
		header: {
			left: 'prev,next today',
			center: 'title',
			right: 'month,agendaWeek,agendaDay,basicWeekBackup,basicWeekTest',
		},

		events: function( eventStart, eventEnd, reportEventsAndPop )
		{
			var params = {};
			params[ 'start' ]            = Math.round( eventStart.getTime() / 1000 );
			params[ 'end' ]              = Math.round( eventEnd.getTime() / 1000 );
			params[ 'calendar_node_id' ] = calendar_node_id;
			params[ 'categories' ]       = '';
			
			//adding categories
			$('.calendar2category:checked').each( function()
			{
				params[ 'categories' ] += $(this).val() + '-';
			});

			// not available in current scope
			//pushLoading();
			
			$.ajax({
				url: web_service_url,
				dataType: 'json',
				data: params,
				success: reportEventsAndPop
			});
		},
		firstDay: 0,
		
		loading: function(bool)
		{
			if (bool) $('#loading').show();
			else $('#loading').hide();
		}
				
	})
});

$('#calendar2-refetch').bind("click", function(e)
{
	$('#calendar').fullCalendar( 'refetchEvents' );
	return false;
});

{/literal}
</script>