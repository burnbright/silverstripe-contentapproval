<% if Articles.MoreThanOnePage %>
  <p class="pagintation">
  <% if Articles.PrevLink %>
    <a href="$Articles.PrevLink">prev</a> | 
  <% end_if %>
 
  <% control Articles.Pages %>
    <% if CurrentBool %>
      <strong>$PageNum</strong> 
    <% else %>
      <a href="$Link" title="Go to page $PageNum">$PageNum</a> 
    <% end_if %>
  <% end_control %>
 
  <% if Articles.NextLink %>
    | <a href="$Articles.NextLink">next</a>
  <% end_if %>
  </p>
<% end_if %>