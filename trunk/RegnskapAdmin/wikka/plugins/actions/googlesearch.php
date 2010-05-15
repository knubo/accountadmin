<script src="http://www.google.com/jsapi"></script>


<script type="text/javascript">
/*
*  How to restrict a search to a specific website.
*/

google.load('search', '1');

function OnLoad() {
  // Create a search control
  var searchControl = new google.search.SearchControl();

  // Add in a WebSearch
  var webSearch = new google.search.WebSearch();

  webSearch.setSiteRestriction('www.knubo.no');

  // Add the searcher to the SearchControl
  searchControl.addSearcher(webSearch);

  // tell the searcher to draw itself and tell it where to attach
  searchControl.draw(document.getElementById("content"));

}

google.setOnLoadCallback(OnLoad);

</script>

<div id="content">Laster...</div>

<?php

?>