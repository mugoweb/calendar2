{def $days_of_week = ezini( 'Calendar', 'DaysOfWeek', 'calendar2.ini' )}
{def $month_names = ezini( 'Calendar', 'Months', 'calendar2.ini' )}
{if and( is_set( $attribute.content.enabled ), eq( 1, $attribute.content.enabled ) )}
    {switch match=$attribute.content.type}
        {* Daily *}
        {case match=0}
            Daily,
            {if eq( 1, $attribute.content.0.option )}
                every {$attribute.content.0.factor} day{if $attribute.content.0.factor|gt( 1 )}s{/if}
            {elseif eq( 2, $attribute.content.0.option )}
                every weekday
            {/if}
        {/case}
        {* Weekly *}
        {case match=1}
            Weekly,
            every {$attribute.content.1.factor} week{if $attribute.content.1.factor|gt( 1 )}s{/if} on
            {if count( $attribute.content.1.day )}
                {foreach $attribute.content.1.day as $day => $value}
                    {$days_of_week.$day}{delimiter},{/delimiter}
                {/foreach}
            {/if}
        {/case}
        {* Monthly *}
        {case match=2}
            Monthly,
            every {$attribute.content.2.factor} month{if $attribute.content.2.factor|gt( 1 )}s{/if}
            on day #{$attribute.content.2.day}
        {/case}
        {* Yearly *}
        {case match=3}
            Yearly,
            every {$attribute.content.3.factor} year{if $attribute.content.3.factor|gt( 1 )}s{/if}
            on {$month_names[$attribute.content.3.month]} {$attribute.content.3.day}
        {/case}
    {/switch}
    <p>
        Ends:
        {if or( is_unset( $attribute.content.end.option ), eq( 1, $attribute.content.end.option ) )}
            no end
        {elseif eq( 2, $attribute.content.end.option )}
            after {$attribute.content.end.factor} occurrence{if $attribute.content.end.factor|gt( 1 )}s{/if}
        {elseif eq( 3, $attribute.content.end.option )}
            on {$attribute.content.end.date}
        {/if}
    </p>
{else}
    Not enabled
{/if}
{*$attribute.content|attribute('show',2)*}