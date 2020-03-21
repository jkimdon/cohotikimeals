{title}Peer-to-peer cost-sharing bookkeeping{/title}

{if $in_meal_program neq true} Sorry, this is only available to meal program participants.
{else}

<p>This is a tool for us to keep track of what we owe each other. It
is not connected with your meal account or CoHo or CoHoTopia. It is
simply meant to be a convenience for when we buy things for each other
or share costs. Please direct questions to Joey</p>

<hr class="finhistory">

<h1>Status</h1>

<p>{$debt_message}</p>

<p>{$lend_message}</p>

<a href="coho_costsharing_log.php">(Click here for details/log.)</a>

<hr class="finhistory">

<h1>Enter transaction</h1>

<form action="coho_costsharing_handler.php" method="post" class="form-horizontal">

  <input type="radio" id="id_transactionType" name="transactionType" value="borrower" checked="checked" onclick="showBorrow();"> I received something:</input>

  <span id="borrowEntry" style="padding-left:30px;">
 {html_options name='new_lender' options=$allBillingGroups} gave me money/goods in the amount: $<input type="text" name="borrower_dollars" size="3" maxlength="3"/>.<input type="text" name="borrower_cents" size="2" maxlength="2"/><br>
Memo: <input type="text" name="borrowmemo" size="40" maxlength="80"/>
  <input class="btn btn-default" type="submit" value="Submit transaction" />
  </span>

  <br>


  <input type="radio" id="id_transactionType" name="transactionType" value="lender" onclick="showLend();"> I gave something: </input>

  <span id="lendEntry" style="display:none; padding-left:30px;">
I gave {html_options name='new_borrower' options=$allBillingGroups} money/goods in the amount: $<input type="text" name="lender_dollars" size="3" maxlength="3"/>.<input type="text" name="lender_cents" size="2" maxlength="2"/><br>
Memo: <input type="text" name="lendmemo" size="40" maxlength="80"/>
  <input class="btn btn-default" type="submit" value="Submit transaction" />
  </span>
</form>



<script>
function showBorrow() {
   var be = document.getElementById("borrowEntry");
   var le = document.getElementById("lendEntry");

   be.style.display = "inline";
   le.style.display = "none";
}

function showLend() {
   var be = document.getElementById("borrowEntry");
   var le = document.getElementById("lendEntry");

   be.style.display = "none";
   le.style.display = "inline";
}
</script>

{/if}