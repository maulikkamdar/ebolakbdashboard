var proteins = [];
var proteinLocator = [];
var domainLocator = [];
var refGenMapper = []

d3.tsv('ebolaData/mapper.tsv', function (data) {
    for(i in data) {
        refGenMapper[data[i].RefSeqID] =  data[i].GenBankId;
    }
});

d3.csv('ebolaData/proteinLoc.csv', function (data){
       //console.log(data);
       for(i in data) {
       var newProtein = {"id" : data[i].ProteinId, "start": parseInt(data[i].Start), "stop": parseInt(data[i].Stop), "proteinName" : data[i].ProteinName, "proteinDescription": data[i].ProteinDescription, "domains" : [], "proteinLength" : 0, "publications":data[i].Publications, "ligands":data[i].Ligands};
            proteins.push(newProtein);
            proteinLocator[data[i].ProteinId] = i;
       }
       console.log(proteins);
       getDomains();
});

getAll();

function getAll() {
    d3.text("makeRequest.php?dataType=defpublications&id=", function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            display('publications', data.results.bindings, "");
            //console.log(data.results.bindings);
            createGenericNetwork(data.results.bindings, "ebola:EBOV", "ebol", "");
            });
    
    d3.text("makeRequest.php?dataType=defligands&id=", function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            display('ligands', data.results.bindings, "");
            });
    
    d3.text("makeRequest.php?dataType=defGo&id=", function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            displayOther('goTerms', data.results.bindings, "");
            });
}

function getDomains() {
    d3.csv('ebolaData/concise-domains.csv', function (data) {
           for(i in data) {
           var newDomain = {"id": data[i].DomainId, "start": parseInt(data[i].DomainStart), "stop": parseInt(data[i].DomainStop), "proteinId": data[i].ProteinId, "domainSystem": data[i].DomainSystem, "signature": data[i].DomainComment, "interproId": data[i].InterproId , "interproName": data[i].InterproName, "publications": data[i].Publications, "ligands":data[i].Ligands };
           proteins[proteinLocator[data[i].ProteinId]].domains.push(newDomain);
           proteins[proteinLocator[data[i].ProteinId]].length = parseInt(data[i].ProteinLength);
           domainLocator[data[i].DomainId] = proteinLocator[data[i].ProteinId] + "_" + (proteins[proteinLocator[data[i].ProteinId]].domains.length-1);
           }
           drawChromosomeLayer();
           publishTable();
           });
}

function makeRequest(resourceType, resourceId, resourceName) {
    console.log(resourceId);
    if(resourceType == "domain") {
        var uri = 'http://bio2rdf.org/' + resourceId.toLowerCase();
        d3.text("makeRequest.php?dataType=otherDomMeta&id=" + uri, function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            displayOther('goTerms', data.results.bindings, resourceId);
        });
    } else if (resourceType == "protein") {
        var uri ='http://bio2rdf.org/genbank:' + refGenMapper[resourceId];
        d3.text("makeRequest.php?dataType=otherProtMeta&id=" + uri, function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            displayOther('goTerms', data.results.bindings, resourceId);
        });
    } else if (resourceType == "ebol") {
        var uri = 'http://bio2rdf.org/ebola:EBOV';
    }
    d3.text("makeRequest.php?dataType=publications&id=" + uri, function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            display('publications', data.results.bindings, resourceId, resourceName);
            createGenericNetwork(data.results.bindings, resourceId, resourceType);
    });
    d3.text("makeRequest.php?dataType=ligands&id=" + uri, function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            display('ligands', data.results.bindings, resourceId, resourceName);
    });

}

function getAddDetail(resourceUri, resourceId, resourceType) {
    d3.text("makeRequest.php?dataType="+resourceType+"&id="+resourceUri, function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            displaySpec(resourceType, data.results.bindings, resourceId);
    })
}

function getAddAnnDetail(resourceUri, resourceId, resourceType, displayType) {
    d3.text("makeRequest.php?dataType="+resourceType+"&id="+resourceUri, function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            displaySpecAnn(resourceType, data.results.bindings, resourceId, displayType);
            })
}

function getMeshDetail(resourceUri, resourceId){
    d3.text("makeRequest.php?dataType=specPublicationsMesh&id="+resourceUri, function(json){
            var dataParts = json.split('<body>');
            var data = JSON.parse(dataParts[dataParts.length-1].split('</body>')[0]);
            createSpecNetwork(data.results.bindings, resourceId, "");
    })
}


// Create the XHR object.
function createCORSRequest(method, url) {
    var xhr = new XMLHttpRequest();
    if ("withCredentials" in xhr) {
        // XHR for Chrome/Firefox/Opera/Safari.
        xhr.open(method, url, true);
    } else if (typeof XDomainRequest != "undefined") {
        // XDomainRequest for IE.
        xhr = new XDomainRequest();
        xhr.open(method, url);
    } else {
        // CORS not supported.
        xhr = null;
    }
    return xhr;
}

function makeCorsRequest(url, resourceTerm) {
    var xhr = createCORSRequest('GET', url);
    if (!xhr) {
        console.log('CORS not supported');
        return;
    }
    
    // Response handlers.
    xhr.onload = function() {
        var data = xhr.responseText;
        jQuery('#' + resourceTerm + 'sdf_src').val(data);
        new GLmol(resourceTerm+'sdf');
    };
    
    xhr.onerror = function() {
        console.log('Woops, there was an error making the request.');
    };
    
    xhr.send();
}

function loadStructure(resourceTerm, structureFile){
    makeCorsRequest(structureFile, resourceTerm);
}