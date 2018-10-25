<a href="coho_meals-view_entry.php?id={$mealId}&mealdatetime={$mealdatetime}">
<h1>Editing: {$meal.title} on {$mealdatetime|tiki_date_format:"%a, %b %e, %Y"} at {$mealdatetime|tiki_date_format:"%I:%M %p"}</h1>
</a>
{if $allowed_to_edit eq false}
    Please get a crew member to edit the meal.
{else}

    <form action="coho_meals-edit_meal_handler.php" method="post">
    <input type="hidden" name="id" value={$mealId} />

    <p><b>Meal title:</b>
    <input type="text" size="30" maxlength="150" name="newtitle" value="{$meal.title}"/>
    </p>

    <p><b>Date:</b>
    {html_select_date prefix="mealdate_" time=$mealdatetime}
    </p>

    <p><b>Time:</b>
    {html_select_time prefix="mealtime_" time=$mealdatetime display_seconds=false use_24_hours=false}
    </p>

    <p><b>Menu:</b><br>
    <textarea vertical-align="top" name="menu" rows="5" cols="40">{$meal.menu}</textarea>
    </p>

    <p><b>Notes:</b><br>
    <textarea vertical-align="top" name="notes" rows="5" cols="40">{$meal.notes}</textarea>
    </p>

    <p><b>Crew:</b><br>
    <table class="finhistory"><tr><th>Job</th><th>Filled by</th></tr>
    {foreach item=cf from=$crew_filled}
    	     <tr><td>{$cf.job}</td><td>{$cf.person}</td></tr>
    {/foreach}
    {foreach item=co from=$crew_open}
    	     <tr><td><input type="text" name="{$co.id}-{$co.jobID}" size="30" maxlength="70" value="{$co.job}"/></td><td>No one</td></tr>
    {/foreach}
    {for $j=0 to 2}
    	     <tr><td><input type="text" name=newcrew[]" size="30" maxlength="70"/></td><td>Enter new crew descriptions</td></tr>
    {/for}
    </table>
    </p>

    <p><input class="btn btn-default" type="submit" value="Submit changes" /></p>    

{/if} {* end allowed to edit *}