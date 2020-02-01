<a href="coho_meals-view_entry.php?
{if $mealtype eq 'regular'}id
{else}recurrenceId
{/if}={$mealId}&mealdatetime={$mealdatetime}">
{if $mealtype eq 'regular'}
  <h1>Editing: {$meal.title|escape} on {$mealdatetime|tiki_date_format:"%a, %b %e, %Y"} at {$mealdatetime|tiki_date_format:"%I:%M %p"}</h1>
{else}
  <h1>Editing: Recurring {$meal.title|escape} on {$mealdatetime|tiki_date_format:"%A"}</h1>
{/if}
</a>
{if $allowed_to_edit eq false}
    Please get a crew member to edit the meal.
{else}

    <form action="coho_meals-edit_meal_handler.php" method="post">
    <input type="hidden" name="id" value={$mealId} />
    <input type="hidden" name="mealtype" value={$mealtype} />

    <p><b>Meal title:</b>
    <input type="text" size="30" maxlength="150" name="newtitle" value="{$meal.title|escape}"/>
    </p>

    {if $mealtype eq 'regular'}
    <p><b>Date:</b>
    {html_select_date prefix="mealdate_" time=$mealdatetime}
    </p>
    {/if}

    <p><b>Time:</b>
    {html_select_time prefix="mealtime_" time=$mealdatetime display_seconds=false use_24_hours=false}
    </p>

    <p><b>Signup deadline: </b>
    <input type="text" name="deadline" size="2" maxlength="2" value="{$meal.signup_deadline}"/> days before meal date
    </p>

    {if $is_meal_admin}
    <p><b>Price: </b>(If you change the price, please email coho-social to notify diners.)
    $<input type="text" name="price_dollars" size="3" maxlength="3" value="{$olddollars}" />.<input type="text" name="price_cents" size="2" maxlength="2" value="{$oldcents}"/> 
    </p>
    {/if}

    <p><b>Menu:</b><br>
    <textarea vertical-align="top" name="menu" rows="5" cols="40">{$meal.menu|escape}</textarea>
    </p>

    {if $mealtype eq 'regular'}
    <p><b>Notes:</b><br>
    <textarea vertical-align="top" name="notes" rows="5" cols="40">{$meal.notes|escape}</textarea>
    </p>


    <p><b>Crew:</b><br>
    <table class="finhistory"><tr><th>Job</th><th>Filled by</th></tr>
    {foreach item=cf from=$crew_filled}
    	     <tr><td>{$cf.job|escape}</td><td>{$cf.person|escape}</td></tr>
    {/foreach}
    {foreach item=co from=$crew_open}
    	     <tr><td><input type="text" name="{$co.id|escape}-{$co.jobID|escape}" size="30" maxlength="70" value="{$co.job|escape}"/></td><td>No one</td></tr>
    {/foreach}
    {for $j=0 to 2}
    	     <tr><td><input type="text" name=newcrew[]" size="30" maxlength="70"/></td><td>Enter new crew descriptions</td></tr>
    {/for}
    </table>
    </p>
    {/if}

    <p><input class="btn btn-default" type="submit" value="Submit changes" /></p>    

{/if} {* end allowed to edit *}