<a href="coho_meals-view_entry.php?id={$mealId}&mealdatetime={$mealdatetime}">
<h1>Meal Report for {$meal.title} on {$mealdatetime|tiki_date_format:"%a, %b %e, %Y"} at {$mealdatetime|tiki_date_format:"%I:%M %p"}</h1>
</a>

{if $paperwork_done}

{else} {* confirming paperwork before submitting *}

<h1>DRAFT (submit below)</h1>

    <form action="coho_meals-edit_meal_summary.php" method="post">

{/if}


<table class="finhistory">
   <tr><td><b>Income</b>:</td><td></td><td></td><td></td></tr>
   <tr><td></td><td>Diners in the system:</td><td>{$presignup_income|string_format:"\$%.2f"}</td>
       <td>({$numdiners} diners, weighted equivalent of {$preweighted} diners)</td></tr>
   {if $paperwork_done eq 0}<tr><td></td><td>Walkins entered by you</td><td>{$walkin_income|string_format:"\$%.2f"}</td>
       <td>({$numwalkins} diners, weighted equivalent of {$walkinweighted} diners)</td></tr>{/if}
    <tr><td></td><td>Total income</td><td></td><td>{$totalincome|string_format:"\$%.2f"}</td></tr>

    <tr><td><b>Expenses</b>:</td><td></td><td></td><td></td></tr>
    <tr><td></td><td>Shoppers</td><td>{$shoppercost|string_format:"\$%.2f"}</td><td></td></tr>
    <tr><td></td><td>Market cards and prepaid accounts</td><td>{$farmercost|string_format:"\$%.2f"}</td><td></td></tr>
    <tr><td></td><td>Flat rate spices ({$numdiners} people)</td><td>{$flatrate|string_format:"\$%.2f"}</td><td></td></tr>
    <tr><td></td><td>Pantry purchases</td><td>{$pantrycost|string_format:"\$%.2f"}</td>
        <td>{foreach item=detail from=$pantry_details}{if $detail.numunits neq 0}{$detail.numunits} {$detail.units}(s) of {$detail.food} ({$detail.cost|string_format:"\$%.2f"})<br>{/if}{/foreach}</td></tr>
    <tr><td></td><td>Total expenses</td><td></td><td>{$totalexpenses|string_format:"\$%.2f"}</td></tr>
    <tr><td><b>Difference</b>:</td><td></td><td></td><td>{$profit|string_format:"\$%.2f"}</td></tr>
    <tr><td><b>Per adult cost</b>:</td><td></td><td>{$per_person|string_format:"\$%.2f"}</td><td>(charged {($meal.base_price/100)|string_format:"\$%.2f"})</td></tr>
</table>


<p>  *** Remember to submit a reimbursement form to Valerie (in her CH cubby or in the purple box on her porch) along with your receipts. ***</p> {* Form is below for reference. ***</p> 
<table>
     <tr class="d0">
         <td>Reimbursement form:</td>
         <td><a class="addbutton" href="refs/Reimbursement.pdf">pdf</a></td>
         <td><a class="addbutton" href="refs/Reimbursement.xls">excel</a></td>
         <td><a class="addbutton" href="refs/Reimbursement.doc">doc</a></td>
      </tr>
</table>
*}

{if $paperwork_done}

{else} {* confirming *}

   <input type="hidden" name="id" value="{$mealId}" />
   <input type="hidden" name="mealdatetime" value="{$mealdatetime}" />
   <input type="hidden" name="mealtype" value="regular" />
   {foreach item=w from=$passthroughwalkin}
      <input type="hidden" name="walkin[]" value="{$w}" />
   {/foreach}
   {$i=0}
   {foreach item=ng from=$passthroughnewguest}
      {if $ng neq ''}
         <input type="hidden" name="newguest[]" value="{$ng}" />
         <input type="hidden" name="multiplier[]" value="{$passthroughmultiplier[$i]}" />
         <input type="hidden" name="host[]" value="{$passthroughhost[$i]}" />
      {/if}
      {$i=$i+1}
   {/foreach}
   {$i=0}
   {foreach item=sh from=$passthroughshopper}
      {if $sh neq 'none'}
         <input type="hidden" name="shopper[]" value="{$sh}" />
         <input type="hidden" name="dollars[]" value="{$passthroughdollars[$i]}" />
         <input type="hidden" name="cents[]" value="{$passthroughcents[$i]}" />
         <input type="hidden" name="vendor[]" value="{$passthroughvendor[$i]}" />
         {$i=$i+1}
      {/if}
   {/foreach}
   <input type="hidden" name="farmersDollars" value="{$passthroughfarmersdollars}" />
   <input type="hidden" name="farmersCents" value="{$passthroughfarmerscents}" />
   {foreach item=pantry from=$passthroughpantry}
      <input type="hidden" name="{$pantry.key}" value="{$pantry.amt}" />
   {/foreach}

   <input class="btn btn-default" type="submit" name="todo" value="confirm" /> OR <input class="btn btn-default" type="submit" name="todo" value="edit" />

   </form>


{/if}