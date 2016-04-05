<% if PaginatedItems %>
	<% loop PaginatedItems %>
		<div class="item">
			<% with $ActualRecord %>
				<% if $Me.forTemplate %>
					$Me
				<% else %>
					<a href="$Link">$Title</a>
				<% end_if %>
			<% end_with %>
		</div>

	<% end_loop %>

	<% if PaginatedItems.MoreThanOnePage %>
		<div id="pagination">
			<% if $NextPage %>
				<a class="next" href="$Top.Link/$NextPage">&lt;&lt; older items</a>
			<% end_if %>

			<% if $PrevPage %>
				<a class="prev" href="$Top.Link/$PrevPage"> newer items &gt;&gt;</a>
			<% end_if %>
		</div>
	<% end_if %>

<% else %>
	<div class="error">
		<h2>Sorry, you haven't added anything to your shortlist yet.</h2>
	</div>
<% end_if %>
