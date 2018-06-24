<span style="font-family:Arial;">

<span style="font-size: 1.5em;">
<p>Here is the weekly list of items from the calendar found at: <a href="https://www.cohoecovillage.org/tiki">https://www.cohoecovillage.org/tiki</a></p>
</span>

{section name=d loop=$printDay}

<span style="font-size:2em;color:#3366ff;">{$printDay[d].day|tiki_date_format:"%A, %b %e"}</span><br />

<span style="font-size: 1.5em;">
{section name=bd loop=$printDay[d].birthdays}
<table>
<tr><td>
<b>{$printDay[d].birthdays[bd]|escape}</b>
</td></tr></table>
{/section}

{* Change %i back to %l if it ever starts working properly (to get rid of the leading 0) *}
{section name=ev loop=$printDay[d].events}
<table>
<tr>
<td nowrap style="vertical-align:top;">
{if $printDay[d].events[ev].start eq $printDay[d].events[ev].end}
  {$printDay[d].events[ev].start|tiki_date_format:"%i:%M%p"} -- 
{else}
  {$printDay[d].events[ev].start|tiki_date_format:"%i:%M%p"}-{$printDay[d].events[ev].end|tiki_date_format:"%i:%M%p"} -- 
{/if}
</td>
<td style="vertical-align:top;"><b>{$printDay[d].events[ev].name|escape}</b> (Location: {$printDay[d].events[ev].location|escape})</td>
</tr><tr>
<td></td><td style="vertical-align:top;">{$printDay[d].events[ev].description|nl2br}</td>
</tr></table>
{/section}


{section name=gr loop=$printDay[d].guestroom}
<table>
<tr><td nowrap style="vertical-align:top;">
<b>Guest Room</b> - </td>
<td style="vertical-align:top;">Please welcome {$printDay[d].guestroom[gr].visitor|escape}, guest of {$printDay[d].guestroom[gr].host|escape}. Expected to arrive on {$printDay[d].guestroom[gr].start|tiki_date_format:"%a, %b %e"} at {$printDay[d].guestroom[gr].start|tiki_date_format:"%i:%M%p"} and leave on {$printDay[d].guestroom[gr].end|tiki_date_format:"%a, %b %e"} at {$printDay[d].guestroom[gr].end|tiki_date_format:"%i:%M%p"}
</td></tr></table>
{/section}

{section name=gr loop=$printDay[d].camping}
<table>
<tr><td nowrap style="vertical-align:top;">
<b>Hosted Camping</b> - </td>
<td style="vertical-align:top;">Please welcome {$printDay[d].camping[gr].visitor|escape}, guest of {$printDay[d].camping[gr].host|escape}. Expected to arrive on {$printDay[d].camping[gr].start|tiki_date_format:"%a, %b %e"} at {$printDay[d].camping[gr].start|tiki_date_format:"%i:%M%p"} and leave on {$printDay[d].camping[gr].end|tiki_date_format:"%a, %b %e"} at {$printDay[d].camping[gr].end|tiki_date_format:"%i:%M%p"}
</td></tr></table>
{/section}

</span>

<br /><br />

{/section}


<span style="font-size:2em;color:#993300;">Visitors</span><br />

<span style="font-size: 1.5em;">
{section name=vi loop=$visitors}
<table>
<tr><td nowrap style="vertical-align:top;">
{$visitors[vi].startTimeStamp|tiki_date_format:"%b %e"} to {$visitors[vi].endTimeStamp|tiki_date_format:"%b %e"} -- </td>
<td style="vertical-align:top;"><b>{$visitors[vi].name|escape}</b>. {$visitors[vi].description|nl2br}</td>
</tr></table>
{/section}
<br />
</span>

<span style="font-size:2em;color:#993300;">People who are or will be away</span><br />

<span style="font-size: 1.5em;">
{section name=vi loop=$absent}
<table>
<tr><td nowrap style="vertical-align:top;">
{$absent[vi].startTimeStamp|tiki_date_format:"%b %e"} to {$absent[vi].endTimeStamp|tiki_date_format:"%b %e"} -- </td>
<td style="vertical-align:top;"><b>{$absent[vi].name|escape}</b>. {$absent[vi].description|nl2br}</td>
</tr></table>
{/section}
</span>

</span>
