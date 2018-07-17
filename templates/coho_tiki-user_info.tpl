{* $Id: coho_tiki-user_info.tpl *}

{if $userwatch ne $user}
  {title help="Meal+Preferences"}{tr}Personal Meal Account:{/tr} {$userwatch}{/title}
{else}
  {title help="Meal+Preferences"}{tr}Personal Meal Account:{/tr}{/title}
{/if}



{cycle values="odd,even" print=false}
{tabset name="coho_meals_user_preference"}

{tab name="Meal Account Finances"}
There is nothing to see here
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

{/tabset}