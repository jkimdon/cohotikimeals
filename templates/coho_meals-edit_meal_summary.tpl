<a href="coho_meals-view_entry.php?id={$mealId}&mealdatetime={$mealdatetime}">
<h1>Edit Meal Report for {$meal.title} on {$mealdatetime|tiki_date_format:"%a, %b %e, %Y"} at {$mealdatetime|tiki_date_format:"%I:%M %p"}</h1>
</a>
{if $allowed_to_edit eq false}
    Please get the head chef to do the meal summary.
{else}

{if $paperwork_done eq true}
    Paperwork for this meal has been completed.<br>
{else}
    <form action="coho_meals-display_meal_summary.php" method="post">
    <input type="hidden" name="id" value={$mealId} />
    <input type="hidden" name="confirmOrDisplay" value='confirm' />

<h3>Step 1: Who came?</h3>
<h4>Please put a check next to any walkins.</h4>
{foreach item=mealperson from=$mealpeople}
    <input type="checkbox" name="walkin[]" value="{$mealperson.username}" {$mealperson.status}>{$mealperson.realName}</input><br>
{/foreach}

<h4>Guests:</h4>
{foreach item=guest from=$guest_diners}
     {$guest.realName} (guest of {$guest.hostrealname}) (cost multiplier = {$guest.meal_multiplier})
{/foreach}
<br>
<h2>Additional guests:</h2>
<table>
<tr><th>Guest full name</th><th>Cost multiplier (e.g. 1, 0.5, 2.5)</th><th>Host</th></tr>
{$i=0}
{foreach item=confirmguest from=$confirmingguests}
    <tr><td><input type="text" size="30" maxlength="70" name="newguest[]" value="{$confirmguest.name}"/></td>
        <td><input type="number" step="0.01" name="multiplier[]" value="{$confirmguest.multiplier}"/></td>
	<td><select name="host[]">
	       <option value="none">Select host</option>
	       {foreach item=hostoption from=$mealpeople}
	           <option value="{$hostoption.username}" {if $hostoption.username eq $confirmguest.host}selected="selected"{/if}>{$hostoption.realName}</option>
	       {/foreach}
	    </select></td>
    </tr>
    {$i=$i+1}
{/foreach}
{for $j=0 to 2}
    <tr><td><input type="text" size="30" maxlength="70" name="newguest[]" value=""/></td>
        <td><input type="number" step="0.01" name="multiplier[]" value="1.0"/></td>
	<td><select name="host[]">
	       <option value="none" selected="selected">Select host</option>
	       {foreach item=hostoption from=$mealpeople}
	           <option value="{$hostoption.username}" {if $hostoption.username eq $formfiller}selected="selected"{/if}>{$hostoption.realName}</option>
	       {/foreach}
	    </select></td>
    </tr>
{/for}
</table>

<h3>Step 2: What did you spend?</h3>
       
<p>A) How much was spent by your shopper(s)? (To be reimbursed)</p>
<table class="finhistory">
<tr>
    <td width=8%></td>
    <td>Shopper name</td>
    <td colspan="2">Amount</td>
    <td>Vendor</td>
</tr>
{foreach item=confirmshopper from=$confirmingshoppers}
  <tr><td></td>
     <td><select name="shopper[]">
           <option value="none">Select shopper</option>
	   {foreach item=shopperoption from=$mealpeople}
	      <option value="{$shopperoption.username}" {if $shopperoption.username eq $confirmshopper.username}selected="selected"{/if}>{$shopperoption.realName} </option>
	   {/foreach}
        </select></td>
      <td>$<input type="text" name="dollars[]" size="3" maxlength="3" value="{$confirmshopper.dollars}"/></td>
      <td>.<input type="text" name="cents[]" size="2" maxlength="2" value="{$confirmshopper.cents}"/></td>
      <td><input type="text" name="vendor[]" size="15" maxlength="50" value="{$confirmshopper.vendor}"/></td>
  </tr>
{/foreach}
{for $j=0 to 2}
  <tr><td></td>
     <td><select name="shopper[]">
           <option value="none" selected="selected">Select shopper</option>
	   {foreach item=shopperoption from=$mealpeople}
	      <option value="{$shopperoption.username}">{$shopperoption.realName}</option>
	   {/foreach}
        </select></td>
      <td>$<input type="text" name="dollars[]" size="3" value=""/></td>
      <td>.<input type="text" name="cents[]" size="2" value=""/></td>
      <td><input type="text" name="vendor[]" size="15" maxlength="50" value=""/></td>
  </tr>
{/for}
</table>

<p/>
<p>B) How much was spent using the Farmer\'s Market cards?
$<input type="text" name="farmersDollars" size="3" value="{$farmersDollars}"/>.
<input type="text" name="farmersCents" size="2" value="{$farmersCents}"/>      
</p>

<p>C) How much of each pantry item did you use? *** Enter in decimal form (NO fractions) ***</p>
<table>
{$prev_cat=""}
{foreach item=food from=$pantryfoods}
    {if $prev_cat neq $food.category}
    	<tr class="n1"><td>&nbsp;&nbsp;&nbsp;</td><td><b>{$food.category}</b></td><td></td></tr>
	{$prev_cat=$food.category}
    {/if}
    <tr><td></td><td></td>
        <td>{$food.name}:&nbsp; <input type="text" name="amount{$food.id}" value="{$food.amount}" size="5" maxlength="8"/>&nbsp;{$food.unit}(s)</td></tr>
{/foreach}
</table>

<h3>Step 3: <input class="btn btn-default" type="submit" value="Preview"/></h3>

</form>

{/if} {* end paperwork not yet done *}
{/if} {* end allowed to edit *}