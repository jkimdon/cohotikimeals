{strip}
{title admpage="cohomeals"}{tr}Meal details for {$mealdatetime|tiki_date_format:"%a, %b %e, %Y"}{/tr}{/title}

       <div class="wikitext">
         {if $mealcancelled neq '0'}
	   <h2>***** This meal has been <font color="#DD0000">cancelled</font> *****</h2>
	 {else}

	 Signup deadline: {$signup_deadline|tiki_date_format:"%a, %b %e, %Y"}<br>
	 {if $past_deadline} This meal is in <font color="#DD0000">walkin status</font>{/if}

	 {/if}
       </div>
       <p>
	<table class="normal">
       	       <tr>
		<th>Prices:</th>
       	       	<th>adult</th>
       	       	<th>half-price child</th>
       	       	<th>walkin</th>
       	       </tr>
       	       <tr>
		<td>Signing up now costs:</td>
       		<td class="number">{$adult_price}</td>
       		<td class="number">{$kid_price}</td>
       		<td class="number">{$walkin_price}</td>
       	       </tr>
         </table>
       </p>
       <p></p>

       <div class="wikitext">
       	    {if $has_head_chef eq '0'}
            	<p>***Note: If you (or a buddy) are subscribed to meals on this day of the week and you want want to prevent yourself (or a buddy) from being automatically signed up for this meal only, click <a href="block_diner.php?id=$mealid">here</a>.</p> 
      	    {/if}

       
       <table class="normal">
         <caption>Meal information</caption>
         <tr><th>Suit:</th>
             <td>{$mealsuit}</td>
	 </tr>
         <tr><th>Time:</th>
		<td>{$mealdatetime|tiki_date_format:"%l:%M %p"}</td>
	 </tr>
         <tr><th>Menu:</th>
		<td>{$mealmenu}</td>
	 </tr>
         <tr><th>Notes:</th>
		<td>{$mealnotes}</td>
	 </tr>
         <tr><th>Head chef:</th>
	     {if $has_head_chef eq '1'}<td>{$mealheadchef} 
	     	 {if $can_signup eq true} &nbsp;&nbsp;&nbsp; {button href="coho_meals-edit_participation_handler.php?user={$mealheadchef}&id={$mealid}&type=H&action=D&olduser={$mealheadchef}" _text="Remove"} {/if}</td>
	     {else}{if $can_signup eq true}
	     	 <td>{button href="coho_meals-edit_participation_handler.php?user={$user}&id={$mealid}&type=H&action=A" _text="Add me"} 
	     	 {button href="coho_meals-signup_buddy.php?id={$mealid}&type=H&action=A" _text="Add buddy"}
	     	 {button href="coho_meals-signup_guest.php?id={$mealid}&type=H&action=A" _text="Add guest"}
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
	       	     {button href="coho_meals-edit_participation_handler.php?user={$user}&id={$mealid}&type=C&action=A" _text="Add me"} 
	     	     {button href="coho_meals-signup_buddy.php?id={$mealid}&type=C&action=A" _text="Add buddy"}
	     	     {button href="coho_meals-signup_guest.php?id={$mealid}&type=C&action=A" _text="Add guest"}
	       {else} {if $can_signup eq true}
		   {button href="coho_meals-edit_participation_handler.php?user={$mem.username}&id={$mealid}&type=C&action=D&olduser={$mem.username}" _text="Remove"} {/if}
	       {/if}
	       </td>
	     </tr>
	     {/foreach}
	     <tr><form action="crew_notes_handler.php" method="post">
	         <td><input type="text" name="newCrew" size="25" maxlength="100"/></td>
		 <td> add your own description then click: 
		      <input type="submit" name="whoadd" value="Add me"/>
     		      &nbsp;&nbsp;&nbsp;
     		      <input type="submit" name="whoadd" value="Add buddy"/>
     		      &nbsp;&nbsp;&nbsp;
     		      <input type="submit" name="whoadd" value="Just add description"/>
		      <input type="hidden" name="user" value="$user">
  		      <input type="hidden" name="id" value="$mealid">
  		  </td>
	      </form></tr>
	   </table>
	   </td>
	 </tr>
	 <th>On-site diners</th>
	 <td>
	 {$prev_building=0}
	 {foreach item=diner from=$diners}
	   {if $diner.type eq 'M'}
	       {if $diner.building neq $prev_building}
	       	   {if ($diner.building lte 9) and ($diner.building ge 0)}
	       	       <b>Building {$diner.building}</b><br>
	       	   {else}
			<b>Additional meal plan participants</b><br>
	       	   {/if}
	       {$prev_building=$diner.building}
	       {/if}
	       {$diner.realName}<br>
	   {/if}
	 {/foreach}
	 </td>

       </table>


       </div>

{/strip}