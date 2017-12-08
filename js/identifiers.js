       //--------------------------------------------------------------------------------
		function doi_in_wikidata(doi) {
			var sparql = `SELECT *
WHERE
{
  ?work wdt:P356 "DOI" .
}`;

			sparql = sparql.replace(/DOI/, doi.toUpperCase());
	
			$.getJSON('https://query.wikidata.org/bigdata/namespace/wdq/sparql?query=' + encodeURIComponent(sparql),
				function(data){
				  if (data.results.bindings.length == 1) {
            document.getElementById("wikidata").innerHTML = 'Wikidata: <a href="' + data.results.bindings[0].work.value + '">' + data.results.bindings[0].work.value.replace("http://www.wikidata.org/entity/","") + '</a>';

				  }
			});			

		}		     
    
    
       //--------------------------------------------------------------------------------
    function doi_in_orcid(doi) {   
      $.getJSON('https://enchanting-bongo.glitch.me/search?q=' + encodeURIComponent(doi) + '&callback=?',
            function(data){
				      if (data.orcid) {
                var html = '';
                if (data.orcid.length == 0) {
                  html = 'no ORCIDs';                  
                } else {
                  for (var i in data.orcid) {
                    html += '<div>';
                    html += '<img src="https://orcid.org/sites/default/files/images/orcid_16x16.png" align="center">';
                    html += ' <a href="https://orcid.org/' + data.orcid[i] + '" target="_new">orcid.org/' + data.orcid[i] + '</a>';
                    html += '</div>';                 
                  }
                }
                document.getElementById("orcid").innerHTML = html;
				      }  
			    });       
    
    }