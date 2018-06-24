{* $Id: tiki-user_preferences.tpl 42282 2012-07-09 03:06:07Z marclaporte $ *}

{if $userwatch ne $user}
  {title help="User+Preferences"}{tr}User Preferences:{/tr} {$userwatch}{/title}
{else}
  {title help="User+Preferences"}{tr}User Preferences{/tr}{/title}
{/if}

{if $userwatch eq $user or $userwatch eq ""}
    {include file='tiki-mytiki_bar.tpl'}
{/if}

{if $tiki_p_admin_users eq 'y'}
	<div class="navbar">
		{assign var=thisuser value=$userinfo.login}
		{button href="tiki-assignuser.php?assign_user=$thisuser" _text="{tr}Assign Group{/tr}"}
		{button href="tiki-user_information.php?view_user=$thisuser" _text="{tr}User Information{/tr}"}
	</div>
{/if}


{if $tikifeedback}
  <div class="simplebox highlight">
    {section name=n loop=$tikifeedback}<div>{$tikifeedback[n].mes}</div>{/section}
  </div>
{/if}
{cycle values="odd,even" print=false}
{tabset name="mytiki_user_preference"}

{if $prefs.feature_userPreferences eq 'y'}
{tab name="{tr}Personal Information{/tr}"}
<form action="tiki-user_preferences.php" method="post">
  <input type="hidden" name="view_user" value="{$userwatch|escape}" />


  <table class="formcolor">
    <tr>
      <td>{tr}User:{/tr}</td>
      <td>
        <strong>{$userinfo.login|escape}</strong>
        {if $prefs.login_is_email eq 'y' and $userinfo.login neq 'admin'} 
          <em>({tr}Use the email as username{/tr})</em>
        {/if}
      </td>
    </tr>
  
    <tr>
      <td>
        {tr}Real Name:{/tr}
      </td>
      <td>
        {if $prefs.auth_ldap_nameattr eq '' || $prefs.auth_method ne 'ldap'}
          <input type="text" name="realName" value="{$user_prefs.realName|escape}" style="width:20em;font-size:1.1em;" />{else}{$user_prefs.realName|escape}
        {/if}
      </td>
    </tr>

    <tr>
      <td>{tr}Enrolled in meal program?{/tr}</td>
      <td>{html_radios values=$ynarray output=$ynarray name='in_meal_program' selected={$user_prefs.in_meal_program}}</td>
    </tr>


    <tr>
      <td>
        {tr}Birthdate (need not be set if you choose not to disclose, but then you will be charged adult rates):{/tr}
      </td>
      <td>
      {if $tiki_p_admin_users eq 'y'}
         {if $user_prefs.birthdate eq ''}
            {assign "starting_date" "32400"}
         {else}
	    {assign "starting_date" $user_prefs.birthdate}
	 {/if}
      	 {html_select_date prefix="birth_" time={$starting_date} field_order=$prefs.display_field_order start_year=1910 end_year = $prefs.calendar_end_year}
      {else}
        {if $user_prefs.birthdate eq ''}None set
      	{else}{$user_prefs.birthdate|tiki_long_date}
      	{/if}
      {/if}
       </td>
    </tr>

    <tr>
      <td>Billing group</td>
       <td>
      {if $tiki_p_admin_users eq 'y'}
      	  <input type="text" name="billingGroup" value="{$user_prefs.billingGroup|escape}" style="width:20em;font-size:1.1em;" />
      {else}
	{if $user_prefs.billingGroup eq ''}!!!NONE SET! Please fix!!!
      	{else}
      	{$user_prefs.billingGroup|escape}
	{/if}
      {/if}
      </td>
    </tr>

  {if $showUnit eq 'y'}
    <tr>
      <td>Unit number</td>
      <td>
      <input type="text" name="unitNumber" value="{$user_prefs.unitNumber|escape}" style="width:20em;font-size:1.1em;" />
      </td>
    </tr>
  {/if}
  
	{if $prefs.feature_community_gender eq 'y'}
      <tr><td>{tr}Gender:{/tr}</td>
        <td>
          <input type="radio" name="gender" value="Male" {if $user_prefs.gender eq 'Male'}checked="checked"{/if}/> {tr}Male{/tr}
          <input type="radio" name="gender" value="Female" {if $user_prefs.gender eq 'Female'}checked="checked"{/if}/> {tr}Female{/tr}
          <input type="radio" name="gender" value="Hidden" {if $user_prefs.gender ne 'Male' and $user_prefs.gender ne 'Female'}checked="checked"{/if}/> {tr}Hidden{/tr}
        </td>
      </tr>
	{/if}

    {if $prefs.feature_wiki eq 'y' and $prefs.feature_wiki_userpage eq 'y'}
      <tr>
        <td>{tr}Your personal Wiki Page:{/tr}</td>
        <td>
          {if $userPageExists eq 'y'}
            <a class="link" href="tiki-index.php?page={$prefs.feature_wiki_userpage_prefix}{$userinfo.login}" title="{tr}View{/tr}">{$prefs.feature_wiki_userpage_prefix}{$userinfo.login|escape}</a> 
	    (<a class="link" href="tiki-editpage.php?page={$prefs.feature_wiki_userpage_prefix}{$userinfo.login}">{tr}Edit{/tr}</a>)
          {else}
            {$prefs.feature_wiki_userpage_prefix}{$userinfo.login|escape} (<a class="link" href="tiki-editpage.php?page={$prefs.feature_wiki_userpage_prefix}{$userinfo.login}">{tr}Create{/tr}</a>)
          {/if}
        </td>
      </tr>
    {/if}
  
	{if $prefs.userTracker eq 'y' && $usertrackerId}
		{if $tiki_p_admin eq 'y' and !empty($userwatch) and $userwatch neq $user}
			<tr>
				<td>{tr}User's personal tracker information:{/tr}</td>
				<td>
					<a class="link" href="tiki-view_tracker_item.php?trackerId={$usertrackerId}&user={$userwatch|escape:url}&view=+user">{tr}View extra information{/tr}</a>
				</td>
			</tr>
		{else}
			<tr>
				<td>{tr}Your personal tracker information:{/tr}</td>
				<td>
					<a class="link" href="tiki-view_tracker_item.php?view=+user">{tr}View extra information{/tr}</a>
				</td>
			</tr>
		{/if}
	{/if}

    {* Custom fields *}
    {section name=ir loop=$customfields}
      {if $customfields[ir].show}
        <tr>
          <td>{$customfields[ir].label}:</td>
          <td>
            <input type="{$customfields[ir].type}" name="{$customfields[ir].prefName}" value="{$customfields[ir].value}" size="{$customfields[ir].size}" />
          </td>
        </tr>
      {/if}
    {/section}
    <tr>
      <td>{tr}Last login:{/tr}</td>
      <td><span class="description">{$userinfo.lastLogin|tiki_long_datetime}</span></td>
    </tr>
  
    <td colspan="2" class="input_submit_container"><input type="submit" name="new_prefs" value="{tr}Save changes{/tr}" /></td>
  
  </table>
{/tab}
{tab name="{tr}Preferences{/tr}"}
  <table class="formcolor">
    <tr>
      <th colspan="2">{tr}Meal program{/tr}</th>
    </tr>

    <tr>
      <td>Buddies</td>
      <td>
      </td>
    </tr>

    <tr>
      <td>Food restrictions</td>
      <td>

       <p>Your personal food restrictions are as follows. Adapt as
        needed, though please limit yourself to restrictions rather
        than preferences since it can be a lot of work for chefs to
        accommodate food needs for many people.</p>

  	<p> Please include details in the comments section such as
  	suggested alternatives, if small amounts are ok, or anything
  	else that might be useful for a chef who is trying to
  	accommodate your needs.</p>

      </td>
    </tr>

    <tr>
      <td>Meal subscriptions</td>
      <td>
      </td>
    </tr>
    

    <tr>
      <td colspan="2" class="input_submit_container"><input type="submit" name="new_prefs" value="{tr}Save changes{/tr}" /></td>
    </tr>
  </table>
</form>
{/tab}
{/if}

{if $prefs.change_password neq 'n' or ! ($prefs.login_is_email eq 'y' and $userinfo.login neq 'admin')}
	{tab name="{tr}My Password{/tr}"}
  <form action="tiki-user_preferences.php" method="post">
  <input type="hidden" name="view_user" value="{$userwatch|escape}" />
  <table class="formcolor">
    {if $prefs.auth_method neq 'cas' || ($prefs.cas_skip_admin eq 'y' && $user eq 'admin')}
      {if $prefs.change_password neq 'n' and ($prefs.login_is_email ne 'y' or $userinfo.login eq 'admin')}
        <tr>
          <td colspan="2">{tr}Leave "New password" and "Confirm new password" fields blank to keep current password{/tr}</td>
        </tr>
      {/if}
    {/if}
  
      {if $prefs.login_is_email eq 'y' and $userinfo.login neq 'admin'}
        <input type="hidden" name="email" value="{$userinfo.email|escape}" />
      {else}
        <tr>
          <td>{tr}Email address:{/tr}</td>
          <td><input type="text" name="email" value="{$userinfo.email|escape}" /></td>
        </tr>
      {/if}

      {if $prefs.auth_method neq 'cas' || ($prefs.cas_skip_admin eq 'y' && $user eq 'admin')}
        {if $prefs.change_password neq 'n'}
          <tr>
            <td>{tr}New password:{/tr}</td>
            <td><input type="password" name="pass1" /></td>
          </tr>
  
          <tr>
            <td>{tr}Confirm new password:{/tr}</td>
            <td><input type="password" name="pass2" /></td>
          </tr>
        {/if}
      
        {if $tiki_p_admin ne 'y' or $userwatch eq $user}
          <tr>
            <td>{tr}Current password (required):{/tr}</td>
            <td><input type="password" name="pass" /></td>
          </tr>
        {/if}
      {/if}
    
      <tr>
        <td colspan="2" class="input_submit_container"><input type="submit" name="chgadmin" value="{tr}Save changes{/tr}" /></td>
      </tr>
    </table>
  </form>
	{/tab}
{/if}

{if $tiki_p_delete_account eq 'y' and $userinfo.login neq 'admin'}
{tab name="{tr}Account Deletion{/tr}"}
<form action="tiki-user_preferences.php" method="post" onsubmit='return confirm("{tr _0=$userwatch|escape}Are you really sure you want to delete the account %0?{/tr}");'>
{if !empty($userwatch)}<input type="hidden" name="view_user" value="{$userwatch|escape}" />{/if}
 <table class="formcolor">
  <tr class="{cycle}">
   <td></td>
   <td><input type='checkbox' name='deleteaccountconfirm' value='1' /> {tr}Check this box if you really want to delete the account{/tr}</td>
  </tr>
    <tr>
      <td colspan="2"  class="input_submit_container"><input type="submit" name="deleteaccount" value="{if !empty($userwatch)}{tr}Delete the account:{/tr} {$userwatch|escape}{else}{tr}Delete my account{/tr}{/if}" /></td>
    </tr>
 </table>
</form>
{/tab}
{/if}

{/tabset}
