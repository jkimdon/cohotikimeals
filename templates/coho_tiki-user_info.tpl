{* $Id: coho_tiki-user_info.tpl *}

{if $userwatch ne $user}
  {title help="Meal+Preferences"}{tr}Meal Program Account:{/tr} {$userwatch}{/title}
{else}
  {title help="Meal+Preferences"}{tr}Meal Program Account:{/tr}{/title}
{/if}



{cycle values="odd,even" print=false}
{tabset name="coho_meals_user_preference"}

{tab name="Meal Account Finances"}
     <h1>Financial Log for {$billingName}</h1><br>
	 <form role="form" action="coho_meals-user_info.php" method="post" class="form-horizontal">
	       <br><u>Filter history (optional):</u><br>
	       Sort by:<br>
	       {html_radios name='sortbymeal' values=[false, true] output=["Transaction date","Meal date"] selected={$sortbymeal} separator="<br/>"}
	       Show entries from meals between
	       {html_select_date prefix="finfilter_start_" time=$filterstart field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
	       and {html_select_date prefix="finfilter_end_" time=$filterend field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
	       <p><input class="btn btn-default" type="submit" value="Submit filter" /></p>
	 </form>
	 (Display limited to 100 entries)<br><br>
     <table class="finhistory">
     <tr><th>Transaction date</th><th>Description</th><th>Associated meal</th><th>Notes</th><th>Amount</th><th>Running balance</th></tr>
     {foreach item=logline from=$finlog}
        <tr class="{cycle values="even,odd"}"><td>{$logline.cal_timestamp}</td><td>{$logline.cal_description}</td><td><a href=coho_meals-view_entry.php?id={$logline.cal_meal_id}&mealdatetime={$logline.mealdatetime}>{$logline.mealtitle}</a><br>(on {$logline.mealdatetime|tiki_date_format:"%a, %b %e, %Y"})</td><td>{$logline.cal_text}</td><td>{($logline.cal_amount/100)|string_format:"\$%.2f"}</td><td>{($logline.cal_running_balance/100)|string_format:"\$%.2f"}</tr>
     {/foreach}
     </table>     
{/tab}

{tab name="Food preferences"}
There is nothing to see here
{/tab}

{tab name="Buddies"}
<b>Your buddies:</b><br>
     {foreach item=buddy from=$buddies}
     {$buddy.realName} (multiplier: {$buddy.multiplier})<br>
     {/foreach}
{/tab}

{tab name="Recurring participation"}
There is nothing to see here
{/tab}

{if $is_meal_admin eq true}
    {tab name="Admin finances"}
    	 <h1>Financial logs</h1><br>
	 <form role="form" action="coho_meals-user_info.php" method="post" class="form-horizontal">
	       <br><u>Filter history (optional):</u><br>
	       Show entries for billing group:
	       {html_options name='showbillinggroup' options=$allBillingGroups selected=$adminShowBG}
	       <br><br>Sort by:<br>
	       {html_radios name='sortbymeal' values=[false, true] output=["Transaction date","Meal date"] selected={$sortbymeal} separator="<br/>"}
	       Show entries from meals between
	       {html_select_date prefix="finfilter_start_" time=$filterstart field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
	       and {html_select_date prefix="finfilter_end_" time=$filterend field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
	       <p><input class="btn btn-default" type="submit" value="Submit filter" /></p>
	 </form>
	 <br><p>(Display limited to 100 entries)</p>
     	 <table class="finhistory">
     	    <tr><th>Transaction date</th><th>Billing group</th><th>Description</th><th style="width:200px">Associated meal</th><th>Notes</th><th>Amount</th><th>Running balance</th></tr>
     	    {foreach item=logline from=$adminfinlog}
               <tr class="{cycle values="even,odd"}"><td>{$logline.cal_timestamp}</td><td>{$logline.billingGroup}</td><td>{$logline.cal_description}</td><td><a href=coho_meals-view_entry.php?id={$logline.cal_meal_id}&mealdatetime={$logline.mealdatetime}>{$logline.mealtitle}</a><br>(on {$logline.mealdatetime|tiki_date_format:"%a, %b %e, %Y"})</td><td>{$logline.cal_text}</td><td>{($logline.cal_amount/100)|string_format:"\$%.2f"}</td><td>{($logline.cal_running_balance/100)|string_format:"\$%.2f"}</tr>
     	    {/foreach}
     </table>     

    {/tab}
    {tab name="Other admin"}
    	 <table class="finhistory">
	 	<tr><th>Meals with diners_charged=NULL</th></tr>
		{foreach item=dm from=$uncharged}
		   <tr class="{cycle values="even,odd"}"><td><a href=coho_meals-view_entry.php?id={$dm.cal_meal_id}&mealdatetime={$dm.mealdatetime}>{$dm.mealtitle}</a><br>(on {$dm.mealdatetime|tiki_date_format:"%a, %b %e, %Y"}) &nbsp;&nbsp;&nbsp; {button href="coho_meals-charge_meal.php?id={$dm.cal_meal_id}" _text="Charge"}</td></tr>
		{/foreach}
	 </table>
    	 <table class="finhistory">
	 	<tr><th>Meals with paperwork_done=NULL</th></tr>
		{foreach item=pm from=$nopaperwork}
		   <tr class="{cycle values="even,odd"}"><td><a href=coho_meals-view_entry.php?id={$pm.cal_meal_id}&mealdatetime={$pm.mealdatetime}>{$pm.mealtitle}</a><br>(on {$pm.mealdatetime|tiki_date_format:"%a, %b %e, %Y"})&nbsp;&nbsp;&nbsp; {button href="coho_meals-edit_meal_summary.php?id={$pm.cal_meal_id}&mealtype=regular&mealdatetime={$pm.mealdatetime}" _text="Start"}</td></tr>
		{/foreach}
	 </table>
	 <table class="finhistory">
		<tr><th><form action="coho_meals-user_info.php" method="post" class="form-horizontal">
			Meals improperly charged from:<br> {html_select_date display_days=false prefix="finfilter_start_" time=$filterstart field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
			<input class="btn btn-default" type="submit" value="Change" />
			</form>
		</th></tr>
		{foreach item=bc from=$badcharged}
		    <tr class="{cycle values="even,odd"}"><td><a href=coho_meals-view_entry.php?id={$bc.cal_meal_id}&mealdatetime={$bc.mealdatetime}>{$bc.mealtitle}</a> (diff: {$bc.chargediff})<br>(on {$bc.mealdatetime|tiki_date_format:"%a, %b %e, %Y"}) &nbsp;&nbsp;&nbsp; {button href="coho_meals-charge_meal.php?id={$bc.cal_meal_id}" _text="Recharge"}</td></tr>
		{/foreach}
	 </table>
    {/tab}
{/if} {* end if user admin *}

{/tabset}