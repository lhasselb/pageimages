<% if SortedImages %>
<div>
<% loop SortedImages %>
    <img src="$CroppedImage(160,120).URL" width="160" height="120"  title="$Caption" alt="$Title"  />
<% end_loop %>
</div>
<% end_if %>
