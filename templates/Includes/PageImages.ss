<% if SortedImages %>
<div class="galleria">
<% loop SortedImages %>
    <img src="$URL" width="160" height="120"  title="$Caption" alt="$Title"  />
<% end_loop %>
</div>
<% end_if %>
