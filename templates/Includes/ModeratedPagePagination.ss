<% if ApprovedPages.MoreThanOnePage %>
  <p class="pagintation">
  <% if ApprovedPages.PrevLink %>
    <a href="$ApprovedPages.PrevLink">prev</a> | 
  <% end_if %>
 
  <% control ApprovedPages.Pages %>
    <% if CurrentBool %>
      <strong>$PageNum</strong> 
    <% else %>
      <a href="$Link" title="Go to page $PageNum">$PageNum</a> 
    <% end_if %>
  <% end_control %>
 
  <% if ApprovedPages.NextLink %>
    | <a href="$ApprovedPages.NextLink">next</a>
  <% end_if %>
  </p>
<% end_if %>