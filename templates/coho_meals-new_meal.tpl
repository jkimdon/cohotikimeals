<tr>
   <td>Meal title: </td>

   <td> <input type="text" name="save[name]" value="Community Meal" size="32" style="width:90%;"/> </td>
</tr>

<tr>
   <td> Is this meal recurring? &nbsp;&nbsp;</td>
   <td><input type="checkbox" id="id_recurrent" name="recurrent" value="1" onclick="toggle('recurrenceRules');toggle('mealdate');toggle('mealdatelabel');"/> </td>
</tr>

<tr> 
  <td>&nbsp;</td>
  <td style="padding:5px 10px">
     <div id="recurrenceRules" style="position:relative;top:0px;left:0px;width:100%;display:{if $prefs.javascript_enabled eq 'y'}none{else}block{/if};">
     	  <input type="radio" name="recurrenceType" value="weekly" />On a weekly basis<br />
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Each &nbsp;
 	       <select name="weekday">
	       	       <option value="0" selected="selected">Sunday</option>
	       	       <option value="1">Monday</option>
       	       	       <option value="2">Tuesday</option>
		       <option value="3">Wednesday</option>
		       <option value="4">Thursday</option>
		       <option value="5">Friday</option>
		       <option value="6">Saturday</option>
	       </select>
	       &nbsp; of the week
	       <br /><hr style="width:75%"/>
	  <input type="radio" name="recurrenceType" value="monthlyByWeekday" />On a monthly basis based on the day of the week<br />
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Each &nbsp;
	       <select name="monthlyWeekNumber">
	       	       <option value="0" selected="selected">First</option>
	       	       <option value="1">Second</option>
       	       	       <option value="2">Third</option>
		       <option value="3">Fourth</option>
		       <option value="4">Fifth</option>
	       </select>
	       <select name="monthlyWeekDay">
	       	       <option value="0" selected="selected">Sunday</option>
		       <option value="1">Monday</option>
		       <option value="2">Tuesday</option>
		       <option value="3">Wednesday</option>
		       <option value="4">Thursday</option>
		       <option value="5">Friday</option>
	  	       <option value="6">Saturday</option>
	       </select>
	       <br /><hr style="width:75%"/>

		Starting on:
		{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
			{jscalendar id="startPeriod" date=$recurrence.startPeriod fieldname="startPeriod" align="Bc" showtime='n'}
		{else}
			{html_select_date prefix="startPeriod_" time=$recurrence.startPeriod field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
		{/if}
		<br /><hr style="width:75%"/>
		<input type="radio" name="endType" value="dt" {if $recurrence.endPeriod gt 0}checked="checked"{/if}/>&nbsp;<label for="id_endTypeDt">{tr}End before{/tr}</label>
		{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
			{jscalendar id="endPeriod" date=$recurrence.endPeriod fieldname="endPeriod" align="Bc" showtime='n'}
		{else}
			{html_select_date prefix="endPeriod_" time=$recurrence.endPeriod field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
		{/if}
		<br />
		<input type="radio" name="endType" value="dtneverending" {if $recurrence.endPeriod eq 0 or $calitem.calitemId eq 0}checked="checked"{/if}/>&nbsp;<label for="id_endTypeDtNe">{tr}Never ending{/tr}</label>
		<br />&nbsp;
	  </div>
  </td>
</tr>


<tr>
   <td><div id="mealdatelabel">Date: </div></td>
   <td class="html_select_time"><div id="mealdate">
 	  {if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
	      {jscalendar id="start" date=$calitem.start fieldname="save[date_start]" align="Bc" showtime='n'}
	  {else}
	      {html_select_date prefix="start_date_" time=$calitem.start field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
	  {/if}</div>
   </td>

</tr>


<tr>
   <td>Time: </td>
   <td class="html_select_time">
       {html_select_time prefix="start_" display_seconds=false display_meridian=true use_24_hours=false time=$calitem.start minute_interval=$prefs.calendar_timespan}
   </td>
</tr>

<tr>
   <td>Price: </td>
   <td>$<input type="text" name="price_dollars" size="3" maxlength="3" value="4" />.<input type="text" name="price_cents" size="2" maxlength="2" value="00"/></td>
</tr>

<tr>
   <td>Signup deadline: </td>
   <td><input type="text" name="deadline" size="2" maxlength="2" value="2"/> days before meal date</td>
</tr>

