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
	       <p>Credits only? (Does not work if filtering by meal date.) {html_radios name="filter_creditsonly" values=[true,false] output=["Yes","No"] selected={$creditsonly} separator=" "}</p>
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

{if $is_finance_admin eq true}
    {tab name="Community financial logs"}
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
       	       <p>Credits only? (Does not work if filtering by meal date.) {html_radios name="filter_creditsonly" values=[true,false] output=["Yes","No"] selected={$creditsonly} separator=" "}</p>
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
    {tab name="For the Treasurer"}
    	 <h2>Manual log entry</h2>
    	 <form role="form" action="coho_meals-manual_financial_handler.php" method="post" class="form-horizontal">
	       <p>Enter manual entry for billing group:
	       {html_options name='addentry-billinggroup' options=$allBillingGroups selected=$adminShowBG}</p>
	       <p>Amount: $<input type="text" name="addentry-dollars" size="3" maxlength="3"/>.<input type="text" name="addentry-cents" size="2" maxlength="2"/></p>
	       <p>Transaction type: {html_radios name='addentry-type' values=["credit","debit"] output=["Credit","Debit"] selected=credit separator="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"}</p>
	       <p>Brief description: <input type="text" name="addentry-description" size="40" maxlength="80"/></p>
	       <p>Associated meal id (optional): <input type="text" name="addentry-mealId" size="5" maxlength="5" /></p>
	       <p>Notes (optional): <br><textarea vertical-align="bottom" name="addentry-notes" rows="5" cols="40"></textarea></p>
	       <p><input class="btn btn-default" type="submit" value="Submit entry" /></p>
	  </form>
<hr class="finhistory">
	<h2>Delinquent accounts</h2>
	<table class="finhistory">
	<tr><th>Billing group</th><th>Balance</th></tr>
	{foreach item=acct from=$delinquencies}
		 <tr class={cycle values="even,odd"}"><td>{$acct.billingGroup}</td><td>{$acct.balance}</td></tr>
	{/foreach}
	</table>
    {/tab}
{/if}
{if $is_meal_admin eq true}
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
	 <table class="finhistory">
	 	{foreach item=pbg from=$people_billingGroups}
		<tr class="{cycle values="even,odd"}"><td>{$pbg.billingGroup}</td><td>{$pbg.names}</td></tr>
		{/foreach}
	 </table>
    {/tab}
{/if} {* end if user admin *}

{/tabset}