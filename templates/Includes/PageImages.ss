<% if SortedImages %>
<div id="galleria">
<% if not IsGallery %>
    <% loop SortedImages %>
        <img src="$URL" width="160" height="120"  title="$NiveTitle" alt="$Title"  />
    <% end_loop %>
<% end_if %>
</div>
<% end_if %>
