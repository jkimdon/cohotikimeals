<h1>Sustenance finance information from the meal software</h1>

<p>
<table class="finhistory">
<tr class="odd"><td>Current balance of all accounts:</td><td>{$balance|string_format:"\$%.2f"}</td></tr>
</table>
</p>

<p>
From October 1, 2017 to Sept 30, 2018:
</p>
<table class="finhistory">
<tr class="even"><td>Overall diner income:</td><td>{$allIncome|string_format:"\$%.2f"}</td></tr>
<tr class="odd"><td>Overall shopper expenses:</td><td>{$allShoppers|string_format:"\$%.2f"}</td></tr>
<tr class="even"><td>Overall pantry purchases:</td><td>{$allPantry|string_format:"\$%.2f"}</td></tr>
<tr class="odd"><td>Overall Farmers Cards:</td><td>{$allFarmers|string_format:"\$%.2f"}</td></tr>
<tr class="even"><td>Overall Flatrate spices:</td><td>{$allFlatrate|string_format:"\$%.2f"}</td></tr>
<tr class="odd"><td>Overall Expenses:</td><td>{$allExpenses|string_format:"\$%.2f"}</td></tr>
<tr class="even"><td>Net income:</td><td>{$netIncome|string_format:"\$%.2f"}</td></tr>
</table>


<hr>
<p>
From October 1, 2017 to Sept 30, 2018:
</p>
<table class="finhistory">
      <tr><th>Chef name</th><th>Num meals (num diners)</th><th>Num charged meals</th><th>Num meals with paperwork</th><th>Net income</th></tr>
   {foreach item=chefline from=$chefs}
      <tr class={cycle values="even,odd"}><td>{$chefline.fullName}</td><td>{$chefline.numMeals} ({$chefline.numDiners})</td><td>{$chefline.mealsCharged}</><td>{$chefline.paperworkDone}</td><td>{$chefline.netIncome}</td>
      </tr>
   {/foreach}
</table>