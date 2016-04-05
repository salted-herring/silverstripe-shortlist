<% if ShortlistURL && ID != -1 %>
	<% if AddedToShortList($ID,$ClassName) %>
		<a href="{$ShortListURL}remove?id=$ID&type=$ClassName&s=$SessionID">remove from shortlist <% if ShortListCount %><span>($ShortListCount)</span><% end_if %></a>
	<% else %>
		<a href="{$ShortListURL}add?id=$ID&type=$ClassName&s=$SessionID">add to shortlist <% if ShortListCount %><span>($ShortListCount)</span><% end_if %></a>
	<% end_if %>
<% end_if %>
