{ezscript_require( 'ezjsc::jquery' )}
{ezscript_require( 'jquery-ui-1.7.2-tabs.custom.min.js' )}
{ezcss_require( 'ui-lightness/jquery-ui-1.7.2.custom.css' )}
{default attribute_base='ContentObjectAttribute'}

{def $scope  = concat( $attribute_base, '_serialize_', $attribute.id )
     $locale = fetch( 'content', 'locale' )
     $type = $attribute.content.type|int()}

<input id="selected-tab-value" type="hidden" name="{$scope}[type]" value="{$type}" />
<input id="recurring-toggle" type="checkbox" name="{$scope}[enabled]" value="1" {if $attribute.content.enabled}checked="checked"{/if} />

<label style="display: inline">Enable</label>

<div id="recurring-enabled" {if $attribute.content.enabled|not()}style="display: none"{/if}>
	<div id="tabs" class="ui-tabs">
		<ul class="ui-tabs-nav">
			<li><a href="#tabs-0">Daily</a></li>
			<li><a href="#tabs-1">Weekly</a></li>
			<li><a href="#tabs-2">Monthly</a></li>
			<li><a href="#tabs-3">Yearly</a></li>
		</ul>
	
        <div class="ui-tabs-panel">
            <div id="tabs-0">
                <p>
                    <input type="radio" name="{$scope}[0][option]" value="1" {if eq( $attribute.content.0.option, 1)}checked="checked"{/if} />
                    <label style="display: inline">Every</label>
                    <input type="text" name="{$scope}[0][factor]" value="{$attribute.content.0.factor}" size="3" />
                    day(s)
                </p>
                <p>
                    <input type="radio" name="{$scope}[0][option]" value="2" {if eq( $attribute.content.0.option, 2)}checked="checked"{/if} />
                    <label style="display: inline">Every weekday</label>
                </p>
            </div>
            <div id="tabs-1">
                <p>
                    <label style="display: inline">Recur every</label>
                    <input type="text" value="{$attribute.content.1.factor}" name="{$scope}[1][factor]" size="3" />
                    week(s) on:
                </p>
                <p>
                    {foreach $locale.weekday_name_list as $key => $name}
                        <input type="checkbox" name="{$scope}[1][day][{$key}]" value="1" {if $attribute.content.1.day[ $key ]}checked="checked"{/if} />
                        <label style="display: inline">{$name}</label>
                    {/foreach}
                </p>
            </div>
            <div id="tabs-2">
                <p>
                    <label style="display: inline">Day</label>
                    <input type="text" name="{$scope}[2][day]" value="{$attribute.content.2.day}" size="3" />
                    <label style="display: inline">of every</label>
                    <input type="text" name="{$scope}[2][factor]" value="{$attribute.content.2.factor}" size="3" />
                    month(s)
                </p>
            </div>
            <div id="tabs-3">
                <p>
                    <label style="display: inline">Recur every</label>
                    <input type="text" name="{$scope}[3][factor]" value="{$attribute.content.3.factor}" size="3" />
                    year(s)
                </p>
                <p>
                    <label style="display: inline">On</label>
                    <select name="{$scope}[3][month]">
                    {foreach $locale.month_name_list as $month sequence array( 0,1,2,3,4,5,6,7,8,9,10,11 ) as $key}
                        <option value="{$key}" {if eq( $key, $attribute.content.3.month)}selected="selected"{/if}>{$month}</option>
                    {/foreach}
                    </select>
                    <input type="text" name="{$scope}[3][day]" value="{$attribute.content.3.day}" size="3" />
                </p>
            </div>
        </div>
		<div>
			<hr />
			{*
			<div style="float: left; margin: 5px 30px;">
				<h4>Start</h4>
				<p>
					--Date Picker--
				</p>
			</div>
			*}
			<div style="float: left; margin: 5px 30px;">
				<h4>End</h4>
				<p>
					<input type="radio" name="{$scope}[end][option]" value="1" {if eq( $attribute.content.end.option, 1)}checked="checked"{/if} />
					<label style="display: inline">No end</label>
				</p>
				<p>
					<input type="radio" name="{$scope}[end][option]" value="2" {if eq( $attribute.content.end.option, 2)}checked="checked"{/if} />
					<label style="display: inline">End after: </label>
					<input type="text" name="{$scope}[end][factor]" value="{$attribute.content.end.factor}" size="3" />
					occurrences
				</p>
				<p>
					<input type="radio" name="{$scope}[end][option]" value="3" {if eq( $attribute.content.end.option, 3)}checked="checked"{/if} />
					<label style="display: inline">End on: </label>
					<input type="hidden" name="{$scope}[end][date]" value="{$attribute.content.end.date}" id="datepicker" />
					<input type="text" value="{$attribute.content.end.date}" id="datepicker-display" size="15" readonly="readonly" />
				</p>
			</div>
			
			<div style="clear: both"></div>
		</div>
	</div>
</div>

{/default}

<script type="text/javascript">

var calendar_icon = {'calendar_16.png'|ezimage()};

{literal}
$(document).ready(function()
{
	$("#tabs").tabs();
	$("#tabs").tabs( 'select', $('#selected-tab-value').val() );

	$('#tabs').bind('tabsselect', function(event, ui)
	{
		$('#selected-tab-value').val( ui.index );
	});

});

$(function()
{
	$("#datepicker").datepicker(
		{
			altField: '#datepicker-display',
			altFormat: 'mm/dd/yy',
			showOn: 'button',
			buttonImage: calendar_icon,
			buttonImageOnly: true
		}
	);
});

$('#recurring-toggle').bind("click", function(e)
{
	if( this.checked )
	{
		$('#recurring-enabled').show();
	}
	else
	{
		$('#recurring-enabled').hide();
	}
});

{/literal}
</script>
