{title admpage="cohomeals"}{tr}Meal details for {$mealdatetime|tiki_date_format:"%a, %b %e, %Y"}{/tr}{/title}

       <div class="wikitext">
         {if $mealcancelled neq '0'}
	   <h2>***** This meal has been <font color="#DD0000">cancelled</font> *****</h2>
	 {else}

	 Signup by: {$signup_deadline|tiki_date_format:"%a, %b %e"}<br>
	 {if $past_deadline} This meal is in <font color="#DD0000">walkin status</font>{/if}

	 {/if}
       </div>
       <p>
       <th>Prices:</th><br>
       <th>Adult:</th> {$adult_price}<br>
       <th>Half price child:</th> {$kid_price}<br>
       </p>
       <p></p>

       <div class="wikitext">
       
       <table class="normal">
         <caption>Meal information</caption>
         <tr><th>Time:</th>
		<td>{$mealdatetime|tiki_date_format:"%I:%M %p"}</td>
	 </tr>
         <tr><th>Menu:</th>
		<td>{$mealmenu}</td>
	 </tr>
         <tr><th>Notes:</th>
		<td>{$mealnotes}</td>
	 </tr>
         <tr><th>Head chef:</th>
	     {if $has_head_chef eq '1'}<td>{$mealheadchef.realName}
	     	 {if ($can_signup eq true) and ($headchefbuddy eq true)} &nbsp;&nbsp;&nbsp; {button href="coho_meals-edit_participation_handler.php?people={$mealheadchef.username}&id={$mealid}&type=H&action=D&olduser={$mealheadchef.username}&mealtype={$mealtype}&unixmealdate={$mealdatetime}" _text="Remove"} {/if}</td>
	     {else}{if $can_signup eq true}
	     	 <td>{button href="coho_meals-edit_participation_handler.php?people={$loggedinuser}&id={$mealid}&type=H&action=A&mealtype={$mealtype}&unixmealdate={$mealdatetime}" _text="Add me"}

		     <a class="btn btn-default" onclick="openBuddyHead()">Add buddy</a>
		     <div id="myBuddyHead" class="overlay">
			  <a href="javascript:void(0)" class="closebtn" onclick="closeBuddyHead()">&times;</a>
			  <div class="overlay-content">
       			  <form action="coho_meals-edit_participation_handler.php" method="post"><b>Select and then click submit<br>
  	     		  	<p align="center"><input class="btn btn-default" type="submit" value="Submit"/></p>
       	     		  	{foreach item=buddy from=$buddies}
	     	  	      		 <input type="checkbox" name="people[]" value="{$buddy.username}">{$buddy.realName}<br>
	     		        {/foreach}</b>
	     		  	<p align="center"><input class="btn btn-default" type="submit" value="Submit"/></p>
				<input type="hidden" name="id" value="{$mealid}"/>
			  	<input type="hidden" name="action" value="A"/>
			  	<input type="hidden" name="type" value="H"/>
			  	<input type="hidden" name="mealtype" value="{$mealtype}"/>
			  	<input type="hidden" name="unixmealdate" value="{$mealdatetime}"/>
       			  </form>
  			  </div>
		      </div>

		      <script>
			function openBuddyHead() {
    				 document.getElementById("myBuddyHead").style.width = "100%";
			}
			function closeBuddyHead() {
   				 document.getElementById("myBuddyHead").style.width = "0%";
			}
		       </script>
		 {/if}</td>
	     {/if}
	 </tr>
	 <tr><th>Crew:</th>
	   <td>
	   <font color="#DD0000">Please note that you are no longer automatically signed up to dine when signing up to crew/chef. Please be sure to sign up to dine if you want to eat. Thanks!</font>
	   <table>
	     <tr><th>Crew Description</th><th>Volunteer</th></tr>
	     {foreach item=mem from=$crew}
	     <tr>
	       <td>{$mem.job}</td><td>{$mem.fullname} &nbsp;&nbsp;&nbsp;
	       {if $mem.has_volunteer eq '0'}
	       	     {button href="coho_meals-edit_participation_handler.php?people={$loggedinuser}&id={$mealid}&type=C&action=A&mealtype={$mealtype}&unixmealdate={$mealdatetime}&job={$mem.job}&olduser={$mem.username}" _text="Add me"}
      		     &nbsp;&nbsp;&nbsp;
	     	     <a class="btn btn-default" onclick="openBuddy{$mem.username}{$mem.job|strip:''}()">Add buddy</a>
		     <div id="myBuddy{$mem.username}{$mem.job|strip:''}" class="overlay">
			  <a href="javascript:void(0)" class="closebtn" onclick="closeBuddy{$mem.username}{$mem.job|strip:''}()">&times;</a>
			  <div class="overlay-content">
       			  <form action="coho_meals-edit_participation_handler.php" method="post"><b>Select and then click submit<br>
	     		  	<p align="center"><input class="btn btn-default" type="submit" value="Submit"/></p>
				{foreach item=buddy from=$buddies}
	     	  	      		 <input type="checkbox" name="people[]" value="{$buddy.username}">{$buddy.realName}<br>
	     		        {/foreach}</b>
	     		  	<p align="center"><input class="btn btn-default" type="submit" value="Submit"/></p>
				<input type="hidden" name="id" value="{$mealid}"/>
			  	<input type="hidden" name="action" value="A"/>
			  	<input type="hidden" name="type" value="C"/>
			  	<input type="hidden" name="job" value="{$mem.job}"/>
			  	<input type="hidden" name="mealtype" value="{$mealtype}"/>
			  	<input type="hidden" name="unixmealdate" value="{$mealdatetime}"/>
  			  	<input type="hidden" name="olduser" value="{$mem.username}"/>
       			  </form>
  			  </div>
		      </div>

		      <script>
			function openBuddy{$mem.username}{$mem.job|strip:''}() {
    				 document.getElementById("myBuddy{$mem.username}{$mem.job|strip:''}").style.width = "100%";
			}
			function closeBuddy{$mem.username}{$mem.job|strip:''}() {
   				 document.getElementById("myBuddy{$mem.username}{$mem.job|strip:''}").style.width = "0%";
			}
		       </script>
		     
     		     &nbsp;&nbsp;&nbsp;
	       {else} {if ($can_signup eq true) and ($mem.mybuddy eq true)}
		   {button href="coho_meals-edit_participation_handler.php?people={$mem.username}&id={$mealid}&type=C&action=D&mealtype={$mealtype}&unixmealdate={$mealdatetime}&job={$mem.job}" _text="Remove"} {/if}
	       {/if}
	       </td>
	     </tr>
	     {/foreach}
{*unimplemented for now	     <tr><form action="crew_notes_handler.php&mealtype={$mealtype}" method="post">
	         <td><input type="text" name="newCrew" size="25" maxlength="100"/></td>
		 <td> add your own description then click: 
		      <input type="submit" name="whoadd" value="Add me"/>
     		      &nbsp;&nbsp;&nbsp;
     		      <input type="submit" name="whoadd" value="Add buddy"/>
     		      &nbsp;&nbsp;&nbsp;
     		      <input type="submit" name="whoadd" value="Just add description"/>
		      <input type="hidden" name="user" value="{$loggedinuser}">
  		      <input type="hidden" name="id" value="{$mealid}">
      		      <input type="hidden" name="mealtype" value="{$mealtype}">
  		  </td>
	      </form></tr> *}
	   </table>
	   </td>
	 </tr>
	 <th>Diners</th>
	 <td>
	 {$prev_building=0}
	 {foreach item=diner from=$diners}
	       {if $diner.building neq $prev_building}
	       	   {if ($diner.building lte 9) and ($diner.building ge 0)}
	       	       <b>Building {$diner.building}</b><br>
	       	   {else}
			<b>Additional meal plan participants</b><br>
	       	   {/if}
	       	   {$prev_building=$diner.building}
	       {/if}
	       {$diner.realName}
	       {if ($can_signup eq true) and ($diner.mybuddy eq true)}
   		  &nbsp;&nbsp;&nbsp;
		  {button href="coho_meals-edit_participation_handler.php?people={$diner.username}&id={$mealid}&type=M&action=D&olduser={$diner.username}&mealtype={$mealtype}&unixmealdate={$mealdatetime}" _text="Remove"}
	       {/if}
	       <br>
	 {/foreach}
	 {if $guest_diners neq false}
	     <b>Guests</b><br>
	     {foreach item=guest from=$guest_diners}
	     	{$guest.realName} (guest of {$guest.hostrealname}) (cost multiplier = {$guest.meal_multiplier})
	     	{if ($can_signup eq true) and ( ($guest.hostusername eq $loggedinuser) or ($is_meal_admin) )}
	     	    &nbsp;&nbsp;&nbsp;
		    {button href="coho_meals-signup_guest.php?guestName={$guest.realName}&id={$mealid}&type=M&action=D&host={$guest.hostusername}&mealtype={$mealtype}&unixmealdate={$mealdatetime}" _text="Remove"}<br>
	     	{/if}
	     {/foreach}
	  {/if}
     	  {if ($can_signup eq true)}
      	     {button href="coho_meals-edit_participation_handler.php?people={$loggedinuser}&id={$mealid}&type=M&action=A&mealtype={$mealtype}&unixmealdate={$mealdatetime}" _text="Add me"}
      	     &nbsp;&nbsp;&nbsp;
	     <a class="btn btn-default" onclick="openBuddy()">Add buddy</a>
	     &nbsp;&nbsp;&nbsp;	     
	     <a class="btn btn-default" onclick="openGuest()">Add Guest</a>
     	     &nbsp;&nbsp;&nbsp;
	  {/if}
	 </td>

	 {$end_common_foods=5} {* we have 4 "common" food restrictions *}
	 {$food_count=0}
	 <tr><th>Food restrictions</th>
	 <td>
	 <table>
	 <tr><th>Common restrictions</th><td>
	 {$firstline=1}
	 {$prev_food=""}
	 {foreach item=foodline from=$foodlimits}
	 	  {if $foodline.food neq $prev_food}
  	 	      {if $food_count eq $end_common_foods}
		      </td></tr><tr><th>Other restrictions</th><td>
		      {/if}
		      </td></tr>
	 	      <tr class="{cycle values="even,odd"}"><td>{$foodline.food}</td><td>
		      {$prev_food=$foodline.food}
      		      {$food_count=$food_count+1}
		  {else},
		  {/if}
		  {$foodline.realName}
	 {/foreach}
	 </table></td></tr>

       </table>


       </div>

<div id="myBuddy" class="overlay">
<a href="javascript:void(0)" class="closebtn" onclick="closeBuddy()">&times;</a>
  <div class="overlay-content">
       <form action="coho_meals-edit_participation_handler.php" method="post"><b>Select and then click submit<br>
       	     <p align="center"><input class="btn btn-default" type="submit" value="Submit"/></p>
       	     {foreach item=buddy from=$buddies}
	     	  <input type="checkbox" name="people[]" value="{$buddy.username}">{$buddy.realName}<br>
	     {/foreach}</b>
	     <p align="center"><input class="btn btn-default" type="submit" value="Submit"/></p>
	     <input type="hidden" name="id" value="{$mealid}"/>
	     <input type="hidden" name="action" value="A"/>
	     <input type="hidden" name="type" value="M"/>
	     <input type="hidden" name="mealtype" value="{$mealtype}"/>
     	     <input type="hidden" name="unixmealdate" value="{$mealdatetime}"/>
        </form>
  </div>
</div>
<div id="myGuest" class="overlay">
<a href="javascript:void(0)" class="closebtn" onclick="closeGuest()">&times;</a>
  <div class="overlay-content">
       <form action="coho_meals-signup_guest.php" method="post"><b>Signup Guest</b><br><c>(Recall the guest will be charged to your account)<br>
       	     <p>Enter the full name of your guest:</c><br><d>
	     <input type="text" name="guestName" size="30" maxlength="75"/></p></d>
	     <p><c>Enter their cost multiplier (to be entered as a decimal value, such as 0.5 or 1 or 2.5. Invalid entries will be set to the normal multiplier of "1"):</c><d> <input name="meal_multiplier" type="number" step="0.01" value="1.0"/></d></p>

	     {if $is_meal_admin}
	     	 <p><c>Host: </c><d><select name="host"/>
		 {foreach item=buddy from=$buddies}
		 	  <option value="{$buddy.username}" {if $loggedinuser eq $buddy.username}selected="selected"{/if}>{$buddy.realName}</option>
		 {/foreach}
	     {else}<input type="hidden" name="host" value="{$loggedinuser}"/>
	     {/if}</d>
	     <p align="center"><input class="btn btn-default" type="submit" value="Submit"/></p>
	     <input type="hidden" name="id" value="{$mealid}"/>
	     <input type="hidden" name="action" value="A"/>
	     <input type="hidden" name="type" value="M"/>
	     <input type="hidden" name="mealtype" value="{$mealtype}"/>
     	     <input type="hidden" name="unixmealdate" value="{$mealdatetime}"/>
        </form>
  </div>
</div>

<script>
function openBuddy() {
    document.getElementById("myBuddy").style.width = "100%";
}
function closeBuddy() {
   document.getElementById("myBuddy").style.width = "0%";
}
function openGuest() {
    document.getElementById("myGuest").style.width = "100%";
}
function closeGuest() {
   document.getElementById("myGuest").style.width = "0%";
}
</script>

