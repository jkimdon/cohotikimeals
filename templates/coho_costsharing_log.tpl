{function name=prevpage}
  {assign "pageNo" $pageNo-1}
  <button class="btn btn-default" type="submit" name="pageNo" value="{$pageNo}"><</button>
{/function}

{function name=nextpage}
  {assign "pageNo" $pageNo+1}
  <button class="btn btn-default" type="submit" name="pageNo" value="{$pageNo}">></button>
{/function}




{title}Costsharing log{/title}


<form role="form" action="coho_costsharing_log.php" method="post" class="form-horizontal">
       <br><u>Filter history (optional):</u><br>
       Show entries from between
       {html_select_date prefix="finfilter_start_" time=$filterstart field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
       and {html_select_date prefix="finfilter_end_" time=$filterend field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
       <p><input class="btn btn-default" type="submit" value="Submit filter" /></p>
<br><p> {if $pageNo > 1} {prevpage} {/if} Page {$pageNo} {nextpage}</p>

</form>


    	 <table class="finhistory">
     	    <tr><th>Entry date</th><th>Lender</th><th>Borrower</th><th>Amount</th><th>Memo</th></tr>
     	    {foreach item=logline from=$costlog}
               <tr class="{cycle values="even,odd"}"><td>{$logline.dateEntered}</td><td>{$logline.lender}</td><td>{$logline.borrower}</td><td>{$logline.amount|string_format:"\$%.2f"}</td><td>{$logline.memo}</td></tr>
     	    {/foreach}
     </table>     
