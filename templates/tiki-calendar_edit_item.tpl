{* just copy this entire template when upgrading *}
{strip}
	{title admpage="calendar"}{tr}Calendar Item{/tr}{/title}
	(Please note that common house events may require an event form or a cemetery parking permit. Contact Jude.)

   	<div class="navbar">
     	        {if $tiki_p_view_calendar eq 'y'}
	     	        {button href="tiki-calendar.php" _text="{tr}View Calendars{/tr}"}
	        {/if}
	     	{if $tiki_p_admin_calendar eq 'y'}
	     	        {button href="tiki-admin_calendars.php?calendarId=$calendarId" _text="{tr}Edit Calendar{/tr}"}
	        {/if}
	        {if $tiki_p_add_events eq 'y' and $id }
	     	        {button href="tiki-calendar_edit_item.php" _text="{tr}New event{/tr}"}
	        {/if}
		{if $id eq '-1'}
	    	        {if $edit}
			    {button href="tiki-calendar_edit_item.php?viewrecurrenceId=$recurrenceId&calendarId=$calendarId&itemdate=$itemdate" _text="{tr}View event{/tr}"}
	        {elseif $tiki_p_change_events eq 'y'}
			{button href="tiki-calendar_edit_item.php?recurrenceId=$recurrenceId&calendarId=$calendarId&itemdate=$itemdate" _text="{tr}Edit/Delete this event only{/tr}"}
			{button href="tiki-calendar_edit_item.php?recurrenceId=$recurrenceId&calendarId=$calendarId&itemdate=0" _text="{tr}Edit/Delete all events in this recurrence{/tr}"}
	        {/if}
	    {elseif $id}
	    	    {if $edit}
		    	    {button href="tiki-calendar_edit_item.php?viewcalitemId=$id" _text="{tr}View event{/tr}"}
	            {elseif $tiki_p_change_events eq 'y'}
		    	    {button href="tiki-calendar_edit_item.php?calitemId=$id" _text="{tr}Edit/Delete event{/tr}"}
	            {/if}
	    {/if}
	    {if $tiki_p_admin_calendar eq 'y'}
	    	{button href="tiki-admin_calendars.php" _text="{tr}Admin Calendars{/tr}"}
            {/if}
    	</div>

        <div class="wikitext">
		{if $edit}
			{if $preview}
				<h2>
					{tr}Preview{/tr}
				</h2>
				{$calitem.parsedName}
				<div class="wikitext">
					{$calitem.parsed}
				</div>
				<h2>
					{if $id}
						{tr}Edit Calendar Item{/tr}
					{else}
						{tr}New Calendar Item{/tr}
					{/if}
				</h2>
			{/if}
			<form action="{$myurl}" method="post" name="f" id="editcalitem">
			        <input type="hidden" name="save[user]" value="{$calitem.user}" />
				{if $id}
					<input type="hidden" name="save[calitemId]" value="{$id}" />
				{/if}
		{/if}
		{if $prefs.calendar_addtogooglecal == 'y'}
			{wikiplugin _name="addtogooglecal" calitemid=$id}{/wikiplugin}
		{/if}
		<table class="formcolor{if !$edit} vevent{/if}">
			<tr>
				<td>
					{tr}Calendar{/tr}
				</td>
				<td style="background-color:#{$calendar.custombgcolor};color:#{$calendar.customfgcolor};">
					{if $edit}
						{if $prefs.javascript_enabled eq 'n'}
							{$calendar.name|escape}<br />{tr}or{/tr}&nbsp;
							<input type="submit" name="changeCal" value="{tr}Go to{/tr}" />
						{/if}
						<select name="save[calendarId]" id="calid" onchange="javascript:needToConfirm=false;document.getElementById('editcalitem').submit();">
							{foreach item=it key=itid from=$listcals}
								{if $it.tiki_p_add_events eq 'y'}
									<option value="{$it.calendarId}" style="background-color:#{$it.custombgcolor};color:#{$it.customfgcolor};"
										{if isset($calitem.calendarId)}
											{if $calitem.calendarId eq $itid}
												 selected="selected"
											{/if}
										{else}
											{if $calendarView}
												{if $calendarView eq $itid}
													 selected="selected"
												{/if}
											{else}
												{if $calendarId}
													{if $calendarId eq $itid}
														 selected="selected"
													{/if}
												{/if}
											{/if}
										{/if}
									>
										{$it.name|escape}
									</option>
								{/if}
							  {/foreach}
		</select>
{else}
	{$listcals[$calitem.calendarId].name|escape}
{/if}
	</td>
</tr>

<tr>
<td>{tr}Title (or guest name if guest room reservation){/tr}</td>
<td>
{if $edit}
	<input type="text" name="save[name]" value="{$calitem.name|escape}" size="32" style="width:90%;"/>
{else}
	<span class="summary">{$calitem.name|escape}</span>
{/if}
</td>
</tr>
<tr>
	<td>{tr}Recurrence{/tr}</td>
	<td>
{if $edit}
	{if $recurrence.id gt 0}
	<input type="hidden" name="recurrent" value="1"/>
		{tr}This event depends on a recurrence rule{/tr}
            {if $edit} 
              {if $affect eq 'all'}
                <input type="hidden" name="affect" value="all"/>
	      {else}
                <input type="hidden" name="affect" value="event"/>
	      {/if}
            {/if}
	{else}
<input type="checkbox" id="id_recurrent" name="recurrent" value="1" onclick="toggle('recurrenceRules');toggle('startdate');toggle('enddate');"{if $calitem.recurrenceId gt 0 or $recurrent eq 1}checked="checked"{/if}/><label for="id_recurrent">{tr}This event depends on a recurrence rule{/tr}</label>
	{/if}
{else}
	<span class="summary">{if $calitem.recurrenceId gt 0}{tr}This event depends on a recurrence rule{/tr}{else}{tr}This event is not recurrent{/tr}{/if}</span>
{/if}
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td style="padding:5px 10px">
{if $edit}
	  <div id="recurrenceRules" style="position:relative;top:0px;left:0px;width:100%;display:{if ( !($calitem.recurrenceId gt 0) and $recurrent neq 1 ) && $prefs.javascript_enabled eq 'y'}none{else}block{/if};">
	  {if $calitem.recurrenceId gt 0}<input type="hidden" name="recurrenceId" value="{$recurrence.id}" />{/if}
{if $recurrence.id gt 0}
	{if $recurrence.weekly}
	  <input type="hidden" name="recurrenceType" value="weekly" />{tr}On a weekly basis{/tr}<br />
	{/if}
{else}
	  <input type="radio" id="id_recurrenceTypeW" name="recurrenceType" value="weekly" {if $recurrence.weekly or $calitem.calitemId eq 0}checked="checked"{/if}/><label for="id_recurrenceTypeW">{tr}On a weekly basis{/tr}</label><br />
	  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{if $recurrence.id eq 0 or $recurrence.weekly}
			  {tr}Each{/tr}&nbsp;
			  <select name="weekday">
				<option value="0" {if $recurrence.weekday eq '0'}selected="selected"{/if}>{tr}Sunday{/tr}</option>
				<option value="1" {if $recurrence.weekday eq '1'}selected="selected"{/if}>{tr}Monday{/tr}</option>
				<option value="2" {if $recurrence.weekday eq '2'}selected="selected"{/if}>{tr}Tuesday{/tr}</option>
				<option value="3" {if $recurrence.weekday eq '3'}selected="selected"{/if}>{tr}Wednesday{/tr}</option>
				<option value="4" {if $recurrence.weekday eq '4'}selected="selected"{/if}>{tr}Thursday{/tr}</option>
				<option value="5" {if $recurrence.weekday eq '5'}selected="selected"{/if}>{tr}Friday{/tr}</option>
				<option value="6" {if $recurrence.weekday eq '6'}selected="selected"{/if}>{tr}Saturday{/tr}</option>
			  </select>
			  &nbsp;{tr}of the week{/tr}
		<br /><hr style="width:75%"/>
{/if}
{if $recurrence.id gt 0}
	{if $recurrence.monthly}
	  <input type="hidden" name="recurrenceType" value="monthly" />{tr}On a monthly basis on the same day of the month{/tr}<br />
	{/if}
{else}
		<input type="radio" id="id_recurrenceTypeM" name="recurrenceType" value="monthly" {if $recurrence.monthly}checked="checked"{/if}/><label for="id_recurrenceTypeM">{tr}On a monthly basis on the same day of the month{/tr}</label><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{if $recurrence.id eq 0 or $recurrence.monthly}
			  {tr}Each{/tr}&nbsp;
			  <select name="dayOfMonth">
				{section name=k start=1 loop=32}
				<option value="{$smarty.section.k.index}" {if $recurrence.dayOfMonth eq $smarty.section.k.index}selected="selected"{/if}>{if $smarty.section.k.index lt 10}0{/if}{$smarty.section.k.index}</option>
				{/section}
			  </select>
			  &nbsp;{tr}of the month{/tr}
		<br /><hr style="width:75%"/>
{/if}
{if $recurrence.id gt 0}
	{if $recurrence.monthlyByWeekday}
	  <input type="hidden" name="recurrenceType" value="monthlyByWeekday" />{tr}On a monthly basis based on the day of the week{/tr}<br />
	{/if}
{else}
		<input type="radio" id="id_recurrenceTypeMW" name="recurrenceType" value="monthlyByWeekday" {if $recurrence.monthlyByWeekday}checked="checked"{/if}/><label for="id_recurrenceTypeMW">{tr}On a monthly basis based on the day of the week{/tr}</label><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{if $recurrence.id eq 0 or ($recurrence.monthlyByWeekday and $affect neq 'event' and $calitem.calitemId le 0)}
			  {tr}Each{/tr}&nbsp;
                          <select name="monthlyWeekNumber">
				<option value="0" {if $recurrence.monthlyWeekNumber eq '0'}selected="selected"{/if}>{tr}First{/tr}</option>
				<option value="1" {if $recurrence.monthlyWeekNumber eq '1'}selected="selected"{/if}>{tr}Second{/tr}</option>
				<option value="2" {if $recurrence.monthlyWeekNumber eq '2'}selected="selected"{/if}>{tr}Third{/tr}</option>
				<option value="3" {if $recurrence.monthlyWeekNumber eq '3'}selected="selected"{/if}>{tr}Fourth{/tr}</option>
				<option value="4" {if $recurrence.monthlyWeekNumber eq '4'}selected="selected"{/if}>{tr}Fifth{/tr}</option>
                          </select>
			  <select name="monthlyWeekday">
				<option value="0" {if $recurrence.monthlyWeekday eq '0'}selected="selected"{/if}>{tr}Sunday{/tr}</option>
				<option value="1" {if $recurrence.monthlyWeekday eq '1'}selected="selected"{/if}>{tr}Monday{/tr}</option>
				<option value="2" {if $recurrence.monthlyWeekday eq '2'}selected="selected"{/if}>{tr}Tuesday{/tr}</option>
				<option value="3" {if $recurrence.monthlyWeekday eq '3'}selected="selected"{/if}>{tr}Wednesday{/tr}</option>
				<option value="4" {if $recurrence.monthlyWeekday eq '4'}selected="selected"{/if}>{tr}Thursday{/tr}</option>
				<option value="5" {if $recurrence.monthlyWeekday eq '5'}selected="selected"{/if}>{tr}Friday{/tr}</option>
				<option value="6" {if $recurrence.monthlyWeekday eq '6'}selected="selected"{/if}>{tr}Saturday{/tr}</option>
			  </select>
		<br /><hr style="width:75%"/>
{/if}

{if $recurrence.id gt 0}
	{if $recurrence.yearly}
	  <input type="hidden" name="recurrenceType" value="yearly" />{tr}On a yearly basis{/tr}<br />
	{/if}
{else}
		<input type="radio" id="id_recurrenceTypeY" name="recurrenceType" value="yearly" {if $recurrence.yearly}checked="checked"{/if}/><label for="id_recurrenceTypeY">{tr}On a yearly basis{/tr}</label><br />
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{if $recurrence.id eq 0 or $recurrence.yearly}
			  {tr}Each{/tr}&nbsp;
			  <select name="dateOfYear_day" onChange="checkDateOfYear(this.options[this.selectedIndex].value,document.forms['f'].elements['dateOfYear_month'].options[document.forms['f'].elements['dateOfYear_month'].selectedIndex].value);">
				{section name=k start=1 loop=32}
				<option value="{$smarty.section.k.index}" {if $recurrence.dateOfYear_day eq $smarty.section.k.index}selected="selected"{/if}>{if $smarty.section.k.index lt 10}0{/if}{$smarty.section.k.index}</option>
				{/section}
			  </select>
			  &nbsp;{tr}of{/tr}&nbsp;
			  <select name="dateOfYear_month" onChange="checkDateOfYear(document.forms['f'].elements['dateOfYear_day'].options[document.forms['f'].elements['dateOfYear_day'].selectedIndex].value,this.options[this.selectedIndex].value);">
				<option value="1"  {if $recurrence.dateOfYear_month eq '1'}selected="selected"{/if}>{tr}January{/tr}</option>
				<option value="2"  {if $recurrence.dateOfYear_month eq '2'}selected="selected"{/if}>{tr}February{/tr}</option>
				<option value="3"  {if $recurrence.dateOfYear_month eq '3'}selected="selected"{/if}>{tr}March{/tr}</option>
				<option value="4"  {if $recurrence.dateOfYear_month eq '4'}selected="selected"{/if}>{tr}April{/tr}</option>
				<option value="5"  {if $recurrence.dateOfYear_month eq '5'}selected="selected"{/if}>{tr}May{/tr}</option>
				<option value="6"  {if $recurrence.dateOfYear_month eq '6'}selected="selected"{/if}>{tr}June{/tr}</option>
				<option value="7"  {if $recurrence.dateOfYear_month eq '7'}selected="selected"{/if}>{tr}July{/tr}</option>
				<option value="8"  {if $recurrence.dateOfYear_month eq '8'}selected="selected"{/if}>{tr}August{/tr}</option>
				<option value="9"  {if $recurrence.dateOfYear_month eq '9'}selected="selected"{/if}>{tr}September{/tr}</option>
				<option value="10" {if $recurrence.dateOfYear_month eq '10'}selected="selected"{/if}>{tr}October{/tr}</option>
				<option value="11" {if $recurrence.dateOfYear_month eq '11'}selected="selected"{/if}>{tr}November{/tr}</option>
				<option value="12" {if $recurrence.dateOfYear_month eq '12'}selected="selected"{/if}>{tr}December{/tr}</option>
			  </select>
&nbsp;&nbsp;
			  <span id="errorDateOfYear" style="color:#900;"></span>
		<br /><br /><hr />
{/if}
		<br />
{if $recurrence.id gt 0}
	<input type="hidden" name="startPeriod" value="{$recurrence.startPeriod}"/>
	<input type="hidden" name="nbRecurrences" value="{$recurrence.nbRecurrences}"/>
	<input type="hidden" name="endPeriod" value="{$recurrence.endPeriod}"/>
{/if}
		{tr}Starting on{/tr} :
		{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
			{jscalendar id="startPeriod" date=$recurrence.startPeriod fieldname="startPeriod" align="Bc" showtime='n'}
		{else}
			{html_select_date prefix="startPeriod_" time=$recurrence.startPeriod field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
		{/if}
		<br /><hr style="width:75%"/>
		{*		<input type="radio" id="id_endTypeNb" name="endType" value="nb" {if $recurrence.nbRecurrences or $calitem.calitemId eq 0}checked="checked"{/if}/>&nbsp;<label for="id_endTypeNb">{tr}End after{/tr}</label>
																												       <input type="text" name="nbRecurrences" size="3" style="text-align:right" value="{if $recurrence.nbRecurrences gt 0}{$recurrence.nbRecurrences}{else}{if $calitem.calitemId eq 0}1{/if}{/if}"/>{tr}occurrences{/tr}<br />*}
		<input type="radio" id="id_endTypeDt" name="endType" value="dt" {if $recurrence.endPeriod gt 0}checked="checked"{/if}/>&nbsp;<label for="id_endTypeDt">{tr}End before{/tr}</label>
		{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
			{jscalendar id="endPeriod" date=$recurrence.endPeriod fieldname="endPeriod" align="Bc" showtime='n'}
		{else}
			{html_select_date prefix="endPeriod_" time=$recurrence.endPeriod field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
		{/if}
		<br />
		<input type="radio" id="id_endTypeDtNe" name="endType" value="dtneverending" {if $recurrence.endPeriod eq 0 or $calitem.calitemId eq 0}checked="checked"{/if}/>&nbsp;<label for="id_endTypeDtNe">{tr}Never ending{/tr}</label>
		<br />&nbsp;
	  </div>
{else}
	{if $recurrence.id > 0}
		{if $recurrence.weekly}
	  		{tr}Event is repeated{/tr} {if $recurrence.nbRecurrences gt 0}{$recurrence.nbRecurrences} {tr}times{/tr}, {/if}{tr}every{/tr}&nbsp;{tr}{$daysnames[$recurrence.weekday]}{/tr}
		{elseif $recurrence.monthly}
	  		{tr}Event is repeated{/tr} {if $recurrence.nbRecurrences gt 0}{$recurrence.nbRecurrences} {tr}times{/tr}, {/if}{tr}on{/tr}&nbsp;{$recurrence.dayOfMonth} {tr}of every month{/tr}
		{elseif $recurrence.monthlyByWeekday}
	  		{tr}Event is repeated{/tr} {if $recurrence.nbRecurrences gt 0}{$recurrence.nbRecurrences} {tr}times{/tr}, {/if}{tr}on the {/tr}&nbsp;{$weeknumasword[$recurrence.monthlyWeekNumber]} {$daysnames[$recurrence.monthlyWeekday]} {tr}of every month{/tr}
		{else}
	  		{tr}Event is repeated{/tr} {if $recurrence.nbRecurrences gt 0}{$recurrence.nbRecurrences} {tr}times{/tr}, {/if}{tr}on each{/tr}&nbsp;{$recurrence.dateOfYear_day} {tr}of{/tr} {tr}{$monthnames[$recurrence.dateOfYear_month]}{/tr}
		{/if}
	<br />
	{tr}Starting on{/tr} {$recurrence.startPeriod|tiki_long_date}
	{if $recurrence.endPeriod gt 0}, {tr}ending by{/tr} {$recurrence.endPeriod|tiki_long_date}{/if}.
	{/if}
{/if}
	</td>
</tr>
<tr>
<td>{tr}Start (or check-in time){/tr}</td>
<td>
{if $edit}
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			{if $affect neq 'all'}
			<td rowspan="2" style="border:0;padding-top:2px;vertical-align:middle"><div style="display:block" id="startdate">
			{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
				{jscalendar id="start" date=$calitem.start fieldname="save[date_start]" align="Bc" showtime='n'}
			{else}
				{html_select_date prefix="start_date_" time=$calitem.start field_order=$prefs.display_field_order start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
			{/if}
		        <input type="hidden" name="original_start" value="{$calitem.start}" id="start" />
			</div></td>
			{else}
		        <input type="hidden" name="save[start]" value="{$calitem.start}" id="start" />
			{/if}
			<td rowspan="2" style="border:0;vertical-align:middle" class="html_select_time">
				<span id="starttime" style="display: {if $calitem.allday} none {else} inline {/if}">{html_select_time prefix="start_" display_seconds=false time=$calitem.start minute_interval=$prefs.calendar_timespan hour_minmax=$hour_minmax}</span>
			</td>
			<td style="border:0;padding-top:2px;vertical-align:middle;" rowspan="2">
				<label for="alldayid">
				<input type="checkbox" id="alldayid" name="allday" 
					   onclick="toggleSpan('starttimehourplus');
					   			toggleSpan('starttimehourminus');
					   			toggleSpan('starttime');
					   			toggleSpan('starttimeminplus');
					   			toggleSpan('starttimeminminus');
					   			toggleSpan('endtimehourplus');
					   			toggleSpan('endtimehourminus');
					   			toggleSpan('endtime');
					   			toggleSpan('endtimeminplus');
					   			toggleSpan('endtimeminminus');
					   			toggleSpan('durhourplus');
					   			toggleSpan('durhourminus');
					   			toggleSpan('duration');
					   			toggleSpan('duratione');
					   			toggleSpan('durminplus');
					   			toggleSpan('durminminus');"
					   value="true" {if $calitem.allday} checked="checked" {/if} /> {tr}All day{/tr}</label>
			</td>
		</tr>
	</table>
{else}
    {if $calitem.allday}
	    <abbr class="dtstart" title="{$calitem.start|tiki_short_date}">{$calitem.start|tiki_long_date}</abbr>
    {else}
        <abbr class="dtstart" title="{$calitem.start|isodate}">{$calitem.start|tiki_long_datetime}</abbr>
    {/if}
{/if}
</td>
</tr>
<tr>
<td>{tr}End (or check-out time) -- if unknown, select same time as Start Time{/tr}</td><td>
	{if $edit}
		<input type="hidden" name="save[end_or_duration]" value="end" id="end_or_duration" />
		<div id="end_date" style="display:block"> {* the display:block inline style used here is needed to make toggle() function work properly *}
		<table cellpadding="0" cellspacing="0" border="0">
		<tr>
		        {if $affect neq 'all'}
			<td rowspan="2" style="border:0;vertical-align:middle"><div style="display:block" id="enddate">
			{if $prefs.feature_jscalendar eq 'y' and $prefs.javascript_enabled eq 'y'}
				{jscalendar id="end" date=$calitem.end fieldname="save[date_end]" align="Bc" showtime='n'}
			{else}
				{html_select_date prefix="end_date_" time=$calitem.end field_order=$prefs.display_field_order  start_year=$prefs.calendar_start_year end_year=$prefs.calendar_end_year}
			{/if}
			</div></td>
			{/if}
			<td rowspan="2" style="border:0;vertical-align:middle" class="html_select_time">
				<span id="endtime" style="display: {if $calitem.allday} none {else} inline {/if}">{html_select_time prefix="end_" display_seconds=false time=$calitem.end minute_interval=$prefs.calendar_timespan hour_minmax=$hour_minmax}</span>
			</td>
				  {*			<td rowspan="2" style="border:0;padding-top:2px;vertical-align:middle">
				<span id="duration" style="display: {if $calitem.allday} none {else} inline {/if}"><a href="#" onclick="document.getElementById('end_or_duration').value='duration';flip('end_duration');flip('end_date');return false;">{tr}Duration{/tr}</a></span>
																																	      </td>*}
		</tr>
</table>
</div>

<div id="end_duration" style="display:none;">
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td style="border:0;padding-top:2px;vertical-align:middle">
			<span id="durhourplus" style="display: {if $calitem.allday} none {else} inline {/if}"><a href="#" onclick="document.f.duration_Hour.selectedIndex=(document.f.duration_Hour.selectedIndex+1);">{icon _id='plus_small' align='left' width='11' height='8'}</a></span>
		</td>
		<td style="border:0;vertical-align:middle" rowspan="2" class="html_select_time">
			<span id="duratione" style="display: {if $calitem.allday} none {else} inline {/if}">{html_select_time prefix="duration_" display_seconds=false time=$calitem.duration|default:'01:00' minute_interval=$prefs.calendar_timespan}</span>
		</td>
		<td style="border:0;padding-top:2px;vertical-align:middle">
			<span id="durminplus" style="display: {if $calitem.allday} none {else} inline {/if}"><a href="#" onclick="document.f.duration_Minute.selectedIndex=(document.f.duration_Minute.selectedIndex+1);">{icon _id='plus_small' align='left' width='11' height='8'}</a></span>
		</td>
		<td rowspan="2" style="border:0;padding-top:2px;vertical-align:middle">
			<a href="#" onclick="document.getElementById('end_or_duration').value='end';flip('end_date');flip('end_duration');return false;">{tr}Date and time of end{/tr}</a>
		</td>
	</tr>
	<tr>
		<td style="border:0;vertical-align:middle">
			<span id="durhourminus" style="display: {if $calitem.allday} none {else} inline {/if}"><a href="#" onclick="document.f.duration_Hour.selectedIndex=(document.f.duration_Hour.selectedIndex-1);">{icon _id='minus_small' align='left' width='11' height='8'}</a></span>
		</td>
		<td style="border:0;vertical-align:middle">
			<span id="durminminus" style="display: {if $calitem.allday} none {else} inline {/if}"><a href="#" onclick="document.f.duration_Minute.selectedIndex=(document.f.duration_Minute.selectedIndex-1);">{icon _id='minus_small' align='left' width='11' height='8'}</a></span>
		</td>
	</tr>
</table>
</div>
{else}
    {if $calitem.allday}
        {if $calitem.end}<abbr class="dtend" title="{$calitem.end|tiki_short_date}">{/if}{$calitem.end|tiki_long_date}{if $calitem.end}</abbr>{/if}
    {else}
        {if $calitem.end}<abbr class="dtend" title="{$calitem.end|isodate}">{/if}{$calitem.end|tiki_long_datetime}{if $calitem.end}</abbr>{/if}
    {/if}
{/if}
{if $impossibleDates}
<br />
<span style="color:#900;">{tr}Events cannot end before they start{/tr}</span>
{/if}
</td>
</tr>
<tr>
<td>{tr}Description{/tr}
</td><td>
{if $edit}
{*  {toolbars area_id="save[description]"} (Broken) *}
  <textarea id='editwiki' class="wikiedit" name="save[description]" style="width:98%">{$calitem.description|escape}</textarea>
{else}
  <span class="description">{$calitem.parsed|default:"<i>{tr}No description{/tr}</i>"}</span>
{/if}
</td></tr>

{if $calendar.customstatus ne 'n'}
<tr><td>{tr}Status{/tr}</td><td>

<div class="statusbox{if $calitem.status eq 0} status0{/if}">
{if $edit}
<input id="status0" type="radio" name="save[status]" value="0"{if (!empty($calitem) and $calitem.status eq 0) or (empty($calitem) and $calendar.defaulteventstatus eq 0)} checked="checked"{/if} />
<label for="status0">{tr}Tentative{/tr}</label>
{else}
{tr}Tentative{/tr}
{/if}
</div>
<div class="statusbox{if $calitem.status eq 1} status1{/if}">
{if $edit}
<input id="status1" type="radio" name="save[status]" value="1"{if $calitem.status eq 1} checked="checked"{/if} />
<label for="status1">{tr}Confirmed{/tr}</label>
{else}
{tr}Confirmed{/tr}
{/if}
</div>
<div class="statusbox{if $calitem.status eq 2} status2{/if}">
{if $edit}
<input id="status2" type="radio" name="save[status]" value="2"{if $calitem.status eq 2} checked="checked"{/if} />
<label for="status2">{tr}Cancelled{/tr}</label>
{else}
{tr}Cancelled{/tr}
{/if}
</div>
</td></tr>
{/if}

{if $calendar.custompriorities eq 'y'}
<tr><td>
{tr}Priority{/tr}</td><td>
{if $edit}
<select name="save[priority]" style="background-color:#{$listprioritycolors[$calitem.priority]};font-size:150%;"
onchange="this.style.bacgroundColor='#'+this.selectedIndex.value;">
{foreach item=it from=$listpriorities}
<option value="{$it}" style="background-color:#{$listprioritycolors[$it]};"{if $calitem.priority eq $it} selected="selected"{/if}>{$it}</option>
{/foreach}
</select>
{else}
<span style="background-color:#{$listprioritycolors[$calitem.priority]};font-size:150%;width:90%;padding:1px 4px">{$calitem.priority}</span>
{/if}

</td></tr>
{/if}
<tr style="display:{if $calendar.customcategories eq 'y'}tablerow{else}none{/if};" id="calcat">
<td>{tr}Classification{/tr}</td>
<td>
{if $edit}
<select name="save[categoryId]">
{foreach item=it from=$listcats}
<option value="{$it.categoryId}"{if $calitem.categoryId eq $it.categoryId} selected="selected"{/if}>{$it.name|escape}</option>
{/foreach}
</select>
{else}
<span class="category">{$calitem.categoryName|escape}</span>
{/if}
</td>
</tr>
<tr style="display:{if $calendar.customlocations eq 'y'}tablerow{else}none{/if};" id="calloc">
<td>{tr}Location{/tr}</td>
<td>
{if $edit}
<select name="save[locationId]">
{foreach item=it from=$listlocs}
<option value="{$it.locationId}"{if $calitem.locationId eq $it.locationId} selected="selected"{elseif $it.locationId eq "1"} selected="selected"{/if}>{$it.name|escape}</option>
{/foreach}
</select>
{tr} or new {/tr}
<input type="text" name="save[newloc]" value="" />
{else}
<span class="location">{$calitem.locationName|escape}</span>
{/if}
</td>
</tr>
<tr style="display:{if $calendar.customlanguages eq 'y'}tablerow{else}none{/if};" id="callang">
<td>{tr}Language{/tr}</td>
<td>
{if $edit}
<select name="save[lang]">
<option value=""></option>
{foreach item=it from=$listlanguages}
<option value="{$it.value}"{if $calitem.lang eq $it.value} selected="selected"{/if}>{$it.name}</option>
{/foreach}
</select>
{else}
{$calitem.lang|langname}
{/if}
</td>
</tr>

{if $groupforalert ne ''}
{if $showeachuser eq 'y' }
<tr>
<td>{tr}Choose users to alert{/tr}</td>
<td>
{/if}
{section name=idx loop=$listusertoalert}
{if $showeachuser eq 'n' }
<input type="hidden"  name="listtoalert[]" value="{$listusertoalert[idx].user}">
{else}
<input type="checkbox" name="listtoalert[]" value="{$listusertoalert[idx].user}"> {$listusertoalert[idx].user}
{/if}
{/section}
</td>
</tr>
{/if}


{if $calendar.customparticipants eq 'y'}
	<tr><td colspan="2">&nbsp;</td></tr>
{/if}

<tr style="display:{if $calendar.customparticipants eq 'y'}tablerow{else}none{/if};" id="calorg">
<td>{tr}Contact/Host:{/tr}</td>
<td>
{if $edit}
{assign var='editorg' value=$calitem.organizers[0]}
{if $calitem.organizers[0] eq ''} 
{assign var='org_selected' value=$user}
{else}
{assign var='org_selected' value=$calitem.organizers[0]}
{/if}
<select name="save[organizers]">
{foreach item=it from=$listusers}
<option value="{$it.username}"{if $it.username eq $org_selected} selected="selected" {/if}>{$it.realname|escape}</option>
  {if $calitem.organizers[0] eq $it.username}{assign var='editorg' value=''}{/if}
{/foreach}
</select>
or enter name and phone or email:
<input type="text" name="save[guestContact]" value="{$editorg}" />
{else}
<span class="organizers">{$calitem.organizers_realname|escape}</span>
{/if}
</td>
</tr>





</table>

{if $edit}
<table class="normal">
{* <tr><td><input type="submit" name="preview" value="{tr}Preview{/tr}" /></td></tr> *}
{if !$user and $prefs.feature_antibot eq 'y'}
	{include file='antibot.tpl'}
{/if}
{if $affect eq 'event'}
<tr><td><input type="submit" name="act" value="{tr}Save only this occurrence{/tr}" />
{else}
<tr><td><input type="submit" name="act" value="{tr}Save{/tr}" />
{/if}
{if $id gt 0}&nbsp;<input type="submit" onclick='document.location="tiki-calendar_edit_item.php?calitemId={$id}&amp;delete=y";return false;' value="{tr}Delete event{/tr}"/>{/if}
{if $recurrence.id}
    {if $affect eq 'event'}
	&nbsp;<input type="submit" name="delete_occurrence" value="Delete only this occurrence"/>
    {else}    
    	&nbsp;<input type="submit" onclick='document.location="tiki-calendar_edit_item.php?recurrenceId={$recurrence.id}&amp;delete=y";return false;' value="{tr}Delete Recurrent events{/tr}"/>
    {/if}
{/if}
&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" onclick='document.location="{$referer|escape:'html'}";return false;' value="{tr}Cancel{/tr}"/>
</td></tr>
</table>
{/if}
</form>
</div>
{/strip}
