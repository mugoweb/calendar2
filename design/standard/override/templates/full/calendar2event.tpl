{def $days_of_week = ezini( 'Calendar', 'DaysOfWeek', 'calendar2.ini' )}
{def $month_names = ezini( 'Calendar', 'Months', 'calendar2.ini' )}
{def $recurring = $node.data_map.recurring}
{if and( is_set( $recurring.content.enabled ), eq( 1, $recurring.content.enabled ) )}
    {def $is_recurring = true()}
{else}
    {def $is_recurring = false()}
{/if}
<div class="calendar2-event">
    {foreach $node.path as $path_element}
        {if eq( 'calendar2', $path_element.class_identifier )}
            <div class="calendar2-back">
                <a href={$path_element.url_alias|ezurl()}>Back to the calendar</a>
            </div>
            {break}
        {/if}
    {/foreach}
    <h1>{$node.name|wash()}</h1>
    
    <div class="calendar2-event-starts-ends">
        {* If it starts and ends on the same day, is an all-day event, and doesn't recur just show the date *}
        {if and( not( $is_recurring ), eq( 1, $node.data_map.all_day.value ), eq( $node.data_map.start.content.timestamp|datetime( 'custom', '%Y%m%d' ), $node.data_map.end.content.timestamp|datetime( 'custom', '%Y%m%d' ) ) )}
            <p>
                {$node.data_map.start.content.timestamp|datetime( 'custom', '%l %F %j, %Y' )}
            </p>
        {else}
            <p>
                Starts:
                {if eq( 1, $node.data_map.all_day.value )}
                    {$node.data_map.start.content.timestamp|datetime( 'custom', '%l %F %j, %Y' )}
                {else}
                    {$node.data_map.start.content.timestamp|datetime( 'custom', '%l %F %j, %Y at %g:%i%a' )}
                {/if}
            </p>
            {if not( $is_recurring )}
                <p>
                    Ends:
                    {if eq( 1, $node.data_map.all_day.value )}
                        {$node.data_map.end.content.timestamp|datetime( 'custom', '%l %F %j, %Y' )}
                    {else}
                        {$node.data_map.end.content.timestamp|datetime( 'custom', '%l %F %j, %Y at %g:%i%a' )}
                    {/if}
                </p>
            {/if}
        {/if}
    </div>
    {if $is_recurring}
        <div class="calendar2-event-recurring">
            Recurs
            {switch match=$recurring.content.type}
                {* Daily *}
                {case match=0}
                    daily,
                    {if eq( 1, $recurring.content.0.option )}
                        every {$recurring.content.0.factor} day{if $recurring.content.0.factor|gt( 1 )}s{/if}
                    {elseif eq( 2, $recurring.content.0.option )}
                        every weekday
                    {/if}
                {/case}
                {* Weekly *}
                {case match=1}
                    weekly,
                    every {$recurring.content.1.factor} week{if $recurring.content.1.factor|gt( 1 )}s{/if} on
                    {if count( $recurring.content.1.day )}
                        {foreach $recurring.content.1.day as $day => $value}
                            {$days_of_week.$day}{delimiter},{/delimiter}
                        {/foreach}
                    {/if}
                {/case}
                {* Monthly *}
                {case match=2}
                    monthly,
                    every {$recurring.content.2.factor} month{if $recurring.content.2.factor|gt( 1 )}s{/if}
                    on day #{$recurring.content.2.day}
                {/case}
                {* Yearly *}
                {case match=3}
                    yearly,
                    every {$recurring.content.3.factor} year{if $recurring.content.3.factor|gt( 1 )}s{/if}
                    on {$month_names[$recurring.content.3.month]} {$recurring.content.3.day}
                {/case}
            {/switch}
            <p>
                Ends:
                {if or( is_unset( $recurring.content.end.option ), eq( 1, $recurring.content.end.option ) )}
                    no end
                {elseif eq( 2, $recurring.content.end.option )}
                    after {$recurring.content.end.factor} occurrence{if $recurring.content.end.factor|gt( 1 )}s{/if}
                {elseif eq( 3, $recurring.content.end.option )}
                    on {$recurring.content.end.date}
                {/if}
            </p>
        </div>
    {/if}

    <div class="calendar2-event-description">
        {attribute_view_gui attribute=$node.data_map.description}
    </div>
    
    {if $node.data_map.categories.has_content}
        {def $category = false()}
        <div class="calendar2-event-categories">
            Categories: 
            {foreach $node.data_map.categories.content.relation_list as $category_relation}
                {set $category = fetch( 'content', 'node', hash( 'node_id', $category_relation.node_id ) )}
                {$category.name|wash()}
                {delimiter},{/delimiter}
            {/foreach}
        </div>
    {/if}
</div>