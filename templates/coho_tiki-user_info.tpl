{* $Id: coho_tiki-user_info.tpl *}

{if $userwatch ne $user}
  {title help="Meal+Preferences"}{tr}Personal Meal Account:{/tr} {$userwatch}{/title}
{else}
  {title help="Meal+Preferences"}{tr}Personal Meal Account:{/tr}{/title}
{/if}



{cycle values="odd,even" print=false}
{tabset name="coho_meals_user_preference"}

{tab name="Meal Account Finances"}
     <h1>Financial Log for {$billingName}</h1><br>
     (Most recent 100 entries)<br><br>
     <table class="finhistory">
     <tr><th>Transaction date</th><th>Description</th><th>Associated meal</th><th>Notes</th><th>Amount</th><th>Running balance</th></tr>
     {foreach item=logline from=$finlog}
        <tr class="{cycle values="even,odd"}"><td>{$logline.cal_timestamp}</td><td>{$logline.cal_description}</td><td>{$logline.cal_meal_id}</td><td>{$logline.cal_text}</td><td>{($logline.cal_amount/100)|string_format:"\$%.2f"}</td><td>{($logline.cal_running_balance/100)|string_format:"\$%.2f"}</tr>
     {/foreach}
     </table>     
{/tab}

{tab name="Food preferences"}
There is nothing to see here
{/tab}

{tab name="Buddies"}
There is nothing to see here
{/tab}

{tab name="Recurring participation"}
There is nothing to see here
{/tab}

{if $is_meal_admin eq true}
    {tab name="Admin finances"}
    	 <h1>Financial logs</h1><br>
	 (Most recent 100 entries)<br><br>
     	 <table class="finhistory">
     	    <tr><th>Transaction date</th><th>Billing group</th><th>Description</th><th>Associated meal</th><th>Notes</th><th>Amount</th><th>Running balance</th></tr>
     	    {foreach item=logline from=$adminfinlog}
               <tr class="{cycle values="even,odd"}"><td>{$logline.cal_timestamp}</td><td>{$logline.billingGroup}</td><td>{$logline.cal_description}</td><td>{$logline.cal_meal_id}</td><td>{$logline.cal_text}</td><td>{($logline.cal_amount/100)|string_format:"\$%.2f"}</td><td>{($logline.cal_running_balance/100)|string_format:"\$%.2f"}</tr>
     	    {/foreach}
     </table>     

    {/tab}
{/if}

{/tabset}