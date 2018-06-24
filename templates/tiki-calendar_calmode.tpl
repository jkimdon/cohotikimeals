{* $Id: tiki-calendar_calmode.tpl 65350 2018-01-28 18:21:08Z jonnybradley $ *}
<div class="table-responsive">
	<table class="caltable table">
		<tr>
			<td style="width: 1%;" class="heading weeks"></td>
			{section name=dn loop=$daysnames}
				{if in_array($smarty.section.dn.index,$viewdays)}
					<td id="top_{$smarty.section.dn.index}" class="heading" style="width:14%;">{$daysnames[dn]}</td>
				{/if}
			{/section}
		</tr>

		{section name=w loop=$cell}
			<tr id="row_{$smarty.section.w.index}" style="height:80px">
				<td class="heading weeks"><a href="{$myurl}?viewmode=week&amp;todate={$cell[w][0].day}" title="{tr}View this Week{/tr}">{$weekNumbers[w]}</a></td>
				{section name=d loop=$weekdays}
					{if in_array($smarty.section.d.index,$viewdays)}
						{if $cell[w][d].focus}
							{cycle values="odd,even" print=false advance=false}
						{else}
							{cycle values="text-muted" print=false advance=false}
						{/if}
						<td id="row_{$smarty.section.w.index}_{$smarty.section.d.index}" class="{if $cell[w][d].day eq $today}calhighlight calborder{/if} {cycle}" style="padding:0px">
							<table style="width:100%; border:none">
								<tr>
									<td class="focus {if $cell[w][d].day eq $today}calhighlight{/if}" style="width:50%;text-align:left">
										{* test display_field_order and use %d/%m or %m/%d on each day 'cell' *}
										{if ($prefs.display_field_order eq 'DMY') || ($prefs.display_field_order eq 'DYM') || ($prefs.display_field_order eq 'YDM')}
											<a href="{$myurl}?focus={$cell[w][d].day}" title="{tr}Change Focus{/tr}" class="change-focus">
												{$cell[w][d].day|tiki_date_format:"%d/%m"}
											</a>
										{else}
											<a href="{$myurl}?focus={$cell[w][d].day}" title="{tr}Change Focus{/tr}" class="change-focus">
												{$cell[w][d].day|tiki_date_format:"%m/%d"}
											</a>
										{/if}
									</td>
									{if $myurl neq "tiki-action_calendar.php"}
										<td class="focus {if $cell[w][d].day eq $today}calhighlight{/if}" style="width:50%;text-align:right;font-size:75%">
											{* add additional check to NOT show add event icon if no calendar displayed *}
											{if $tiki_p_add_events eq 'y' and count($listcals) > 0 and $displayedcals|@count > 0}
												<a href="tiki-calendar_edit_item.php?todate={$cell[w][d].day}{if $displayedcals|@count eq 1}&amp;calendarId={$displayedcals[0]}{/if}" title=":{tr}Add event{/tr}" class="addevent tips">
													{icon name='create'}
												</a>
											{/if}
											<a class="viewthisday tips" href="tiki-calendar.php?viewmode=day&amp;todate={$cell[w][d].day}{if $displayedcals|@count eq 1}&amp;calendarId={$displayedcals[0]}{/if}" title=":{tr}View this Day{/tr}">
												{icon name='calendar'}
											</a>
										</td>
									{/if}
								</tr>
							</table>
							{if $cell[w][d].focus}
								{section name=item loop=$cell[w][d].items}
									{if $smarty.section.item.first}
										<table style="width:100%;">
									{/if}
									{assign var=over value=$cell[w][d].items[item].over}
									{assign var=calendarId value=$cell[w][d].items[item].calendarId}
									<tr>
									{if ($calendarId neq '12' and $calendarId neq '2') or ($cell[w][d].items[item].notEndOfMultipleDayEvent eq true)} {* coho hardcoded. 2 is the guest room, 12 is camping *}
										{if is_array($cell[w][d].items[item])}
											<td class="Cal{$cell[w][d].items[item].type} calId{$cell[w][d].items[item].calendarId} viewcalitemId_{$cell[w][d].items[item].calitemId} tips" style="padding:0;height:14px;background-color:#{$infocals.$calendarId.custombgcolor};border-color:#{$infocals.$calendarId.customfgcolor};border-style:solid;opacity:{if $cell[w][d].items[item].status eq '0'}0.8{else}1{/if};filter:Alpha(opacity={if $cell[w][d].items[item].status eq '0'}80{else}100{/if});border-width:1px {if $cell[w][d].items[item].endTimeStamp <= ($cell[w][d].day + 86400)}1{else}0{/if}px 1px {if $cell[w][d].items[item].startTimeStamp >= $cell[w][d].day}1{else}0{/if}px;cursor:pointer"
												{if $prefs.calendar_sticky_popup eq 'y'}
													{popup caption="{tr}Event{/tr}" vauto=true hauto=true sticky=true fullhtml="1" trigger="onClick" text=$over}
												{else}
													{popup caption="{tr}Event{/tr}" vauto=true hauto=true sticky=false fullhtml="1" text=$over}
												{/if}>

												{if $myurl eq "tiki-action_calendar.php" or ( $cell[w][d].items[item].startTimeStamp >= $cell[w][d].day or ($cell[w][d].items[item].startTimeStamp <= $cell[w][d].day and $cell[w][d].items[item].endTimeStamp >= $cell[w][d].day) or $smarty.section.d.index eq '0' or $cell[w][d].firstDay)}
												<a style="padding:1px 3px;{if $cell[w][d].items[item].status eq '2'}text-decoration:line-through;{/if}color:#{$infocals.$calendarId.customfgcolor};"
														{if $myurl eq "tiki-action_calendar.php"}
                                										{if $cell[w][d].items[item].calendarId eq "1"} {* coho meal program is #1 *}
														{if $cell[w][d].items[item].modifiable eq "y" || $cell[w][d].items[item].visible eq 'y'}href="coho_meals-view_entry.php?id={$cell[w][d].items[item].calitemId}"{/if}
														{elseif $cell[w][d].items[item].calitemId eq "-1"}
														{if $cell[w][d].items[item].modifiable eq "y" || $cell[w][d].items[item].visible eq 'y'}href="tiki-calendar_edit_item.php?viewrecurrenceId={$cell[w][d].items[item].recurrenceId}&calendarId={$cell[w][d].items[item].calendarId}&itemdate={$cell[w][d].items[item].startTimeStamp}"{/if}
														{else}
														{if $cell[w][d].items[item].modifiable eq "y" || $cell[w][d].items[item].visible eq 'y'}href="tiki-calendar_edit_item.php?viewcalitemId={$cell[w][d].items[item].calitemId}"{/if}
														{/if}

														{elseif $prefs.calendar_sticky_popup neq 'y'}
															{if $cell[w][d].items[item].modifiable eq "y" || $cell[w][d].items[item].visible eq 'y'}href="tiki-calendar_edit_item.php?viewcalitemId={$cell[w][d].items[item].calitemId}"{/if}
														{else}
																href="#"
														{/if}
													>{$cell[w][d].items[item].name|truncate:$trunc:".."|escape|default:"..."}</a>
													{if $cell[w][d].items[item].web}
														<a href="{$cell[w][d].items[item].web}" target="_other" class="calweb" title="{$cell[w][d].items[item].web}"><img src="img/icons/external_link.gif" width="7" height="7" alt="&gt;"></a>
													{/if}
													{if $cell[w][d].items[item].nl}
														<a href="tiki-newsletters.php?nlId={$cell[w][d].items[item].nl}&info=1" class="calweb" title="Subscribe"><img src="img/icons/external_link.gif" width="7" height="7" alt="&gt;"></a>
													{/if}
												{else}&nbsp;
												{/if}
											</td>
										{else}
											<td style="padding: 0; height: 14px; border: solid white 1px; width: 100%; font-size: 10px">&nbsp;</td>
										{/if}
										{/if}
									</tr>
									{if $smarty.section.item.last}
										</table>
									{/if}
								{/section}
							{/if}
						</td>
					{/if}
				{/section}
			</tr>
		{/section}
	</table>
</div>
