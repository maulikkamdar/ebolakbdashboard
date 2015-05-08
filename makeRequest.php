<?php
    
function constructQuery($query, $url)
{
   $format = 'application/sparql-results+json';
 
   $searchUrl = $url . '?'
      .'query='.urlencode($query)
      .'&format='.urlencode($format);
	  
   return $searchUrl;
}
 
 
function request($url)
{
   if (!function_exists('curl_init')){ 
      die('CURL is not installed!');
   }
   $ch= curl_init();
 
   curl_setopt($ch, 
      CURLOPT_URL, 
      $url);
 
   curl_setopt($ch, 
      CURLOPT_RETURNTRANSFER, 
      true);
 
   $response = curl_exec($ch);
 
   curl_close($ch);
 
   return $response;
}

$dataType = $_GET['dataType'];
$id = $_GET['id'];
$url = 'http://ebola-sparql-dev.semanticscience.org/sparql';
$url = 'http://localhost:12013/sparql';
    
switch($dataType) {
    case 'defpublications' : $query = "PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
        PREFIX pubmed:<http://bio2rdf.org/pubmed_vocabulary:>
        SELECT DISTINCT ?title ?bioUri ?identifier WHERE {
            ?bioUri a pubmed:PubMedRecord .
            ?bioUri rdfs:label ?title;
            bio2rdf:identifier ?identifier.
        }";
        break;
    case 'defligands' : $query = "PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
        
        SELECT DISTINCT ?bioUri ?title ?identifier ?drugbankUri WHERE {
            ?pdbUri a ebola:PDBStructure.
            ?pdbUri ebola:hasLigand ?bioUri .
            ?bioUri ebola:chemicalName ?title .
            ?bioUri bio2rdf:identifier ?identifier .
            OPTIONAL {?bioUri ebola:x-drugbank ?drugbankUri}
        }";
        break;
    case 'defGo' : $query = "PREFIX ebola: <http://bio2rdf.org/ebola_vocabulary:>
        PREFIX go: <http://www.geneontology.org/go#>
        PREFIX bio2rdf: <http://bio2rdf.org/bio2rdf_vocabulary:>
        
        CONSTRUCT {
            ?interproDomain rdfs:label ?interprolabel.
            ?goTerm rdfs:label ?golabel; go:namespace ?namespace
        } WHERE {
            ?domain ebola:x-interpro ?interproDomain .
            ?interproDomain rdfs:label ?interprolabel.
            OPTIONAL {?domain ebola:x-go ?goTerm . ?goTerm go:namespace ?namespace; rdfs:label ?golabel}
        }";
        break;
    case 'publications' : $query = "PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
        
        SELECT DISTINCT ?title ?bioUri ?identifier WHERE {
            <".$id."> ebola:hasKeyword ?keyword .
            ?keyword ebola:x-pubmed ?bioUri .
            ?bioUri rdfs:label ?title;
                bio2rdf:identifier ?identifier.
        }";
        break;
    case 'ligands' : $query = "PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
        
        SELECT DISTINCT ?bioUri ?title ?identifier ?drugbankUri WHERE {
        <".$id."> ebola:hasKeyword ?keyword .
            ?keyword ebola:x-pdb ?pdbUri .
            ?pdbUri ebola:hasLigand ?bioUri .
            ?bioUri ebola:chemicalName ?title .
            ?bioUri bio2rdf:identifier ?identifier .
            OPTIONAL {?bioUri ebola:x-drugbank ?drugbankUri}
        }";
        break;
    case 'specpublications': $query = "PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>
        PREFIX pubmed:<http://bio2rdf.org/pubmed_vocabulary:>
        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
        
        CONSTRUCT {
            <".$id."> pubmed:Article_Title ?title;
            pubmed:Abstract ?abstractText ; pubmed:Article_Date ?articleDate;
            pubmed:Journal `bif:concat (?journalTitle, \" (\", ?journalVol, \":\", ?journalIssue, \")\")` ;
            pubmed:Author ?author ;
            pubmed:Mesh_Term ?mesh
        } WHERE {
            <".$id."> rdfs:label ?title;
                pubmed:abstract ?abstract; pubmed:journal ?journal.
            ?journal pubmed:journal_title ?journalTitle .
            OPTIONAL {?abstract pubmed:abstract_text ?abstractText}
            OPTIONAL {?journal pubmed:journal_volume ?journalVol }
            OPTIONAL {?journal pubmed:journal_issue ?journalIssue}
            OPTIONAL {<".$id."> pubmed:article_date ?articleDate}
            OPTIONAL {<".$id."> pubmed:author ?author}
            OPTIONAL {<".$id."> pubmed:mesh_descriptor_name ?mesh}
        }";
        break;
    case 'specPublicationsMesh': $query = "PREFIX pubmed:<http://bio2rdf.org/pubmed_vocabulary:>
        SELECT DISTINCT * WHERE {
            <".$id."> pubmed:mesh_descriptor_name ?mesh .
            ?pubmedUri pubmed:mesh_descriptor_name ?mesh; rdfs:label ?title
            FILTER (?pubmedUri != <".$id.">)
        }";
        break;
    case 'specligands' : $query = "PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
        PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
        CONSTRUCT {
            <".$id."> ebola:Molecular_Weight ?molWeight; ebola:Chemical_Name ?chemicalName;
            ebola:Smiles_Notation ?smiles; ebola:Molecular_Formula ?formula; ebola:InChI ?inchi;
            ebola:InChI_Key ?inchiKey; ebola:Chemical_Type ?type;
            ebola:PDB_Structure ?pdbUri; ebola:PubMedRecord ?pubmedUri;
            ebola:DrugBank_Drug ?drugbankUri
        } WHERE {
            ?pdbUri ebola:hasLigand <".$id."> .
            <".$id."> ebola:molecularWeight ?molWeight; ebola:chemicalName ?chemicalName; ebola:molecularFormula ?formula
                OPTIONAL {<".$id."> ebola:smilesNotation ?smiles}
                OPTIONAL{<".$id."> ebola:inchi ?inchi}
                OPTIONAL{<".$id."> ebola:inchiKey ?inchiKey}
                OPTIONAL {<".$id."> ebola:chemicalType ?type}
                OPTIONAL {?pdbUri ebola:x-pubmed ?pubmedUri}
                OPTIONAL {<".$id."> ebola:x-drugbank ?drugbankUri}
        }";
        break;
    case 'otherProtMeta': $query = "PREFIX ebola: <http://bio2rdf.org/ebola_vocabulary:>
                                        PREFIX go: <http://www.geneontology.org/go#>
                                        PREFIX bio2rdf: <http://bio2rdf.org/bio2rdf_vocabulary:>
                                        
                                        CONSTRUCT {
                                        ?interproDomain rdfs:label ?interprolabel.
                                        ?goTerm rdfs:label ?golabel; go:namespace ?namespace
                                        } WHERE {
                                        <".$id."> ebola:domain ?domain.
                                        ?domain ebola:x-interpro ?interproDomain .
                                        ?interproDomain rdfs:label ?interprolabel.
                                        OPTIONAL {?domain ebola:x-go ?goTerm . ?goTerm go:namespace ?namespace; rdfs:label ?golabel}
                                        }";
                                        break;
    case 'otherDomMeta':  $query = "PREFIX ebola: <http://bio2rdf.org/ebola_vocabulary:>
                                        PREFIX go: <http://www.geneontology.org/go#>
                                        PREFIX bio2rdf: <http://bio2rdf.org/bio2rdf_vocabulary:>
                                        
                                        CONSTRUCT {
                                        ?interproDomain rdfs:label ?interprolabel.
                                        ?goTerm rdfs:label ?golabel; go:namespace ?namespace
                                        } WHERE {
                                        ?ebolaProt ebola:domain <".$id.">.
                                        ?ebolaProt ebola:domain ?domain.
                                        ?domain ebola:x-interpro ?interproDomain .
                                        ?interproDomain rdfs:label ?interprolabel.
                                        OPTIONAL {?domain ebola:x-go ?goTerm . ?goTerm go:namespace ?namespace; rdfs:label ?golabel}
                                        }";
                                        break;
    case 'specInterpro': $query = "PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
                                        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
                                        
                                        SELECT * WHERE {
                                        ?s ebola:x-interpro <".$id.">;
                                        ebola:domain-start ?start ; ebola:domain-stop ?stop; ebola:domain-system ?system;
                                        bio2rdf:identifier ?identifier
                                        }";
                                        break;
    case 'specGo': $query = "PREFIX ebola:<http://bio2rdf.org/ebola_vocabulary:>
                                        PREFIX bio2rdf:<http://bio2rdf.org/bio2rdf_vocabulary:>
                                        PREFIX go: <http://www.geneontology.org/go#>
                                        SELECT * WHERE {
                                        <".$id."> rdfs:description ?desc; go:namespace ?namespace
                                        }";
                                        break;

    /*case 'otherProtMeta': $query = "PREFIX ebola: <http://bio2rdf.org/ebola_vocabulary:>
        PREFIX go: <http://www.geneontology.org/go#>
        PREFIX bio2rdf: <http://bio2rdf.org/bio2rdf_vocabulary:>
                                        
        CONSTRUCT {
            <".$id."> ebola:x-interpro ?interproDomain;
            ebola:x-go ?goTerm; ebola:x-kegg ?kegg; ebola:x-metacyc ?metacyc; ebola:x-kegg-enzyme ?enzyme .
            ?interproDomain rdfs:label ?interprolabel.
            ?goTerm rdfs:label ?golabel; go:namespace ?namespace
        } WHERE {
            <".$id."> ebola:domain ?domain.
            ?domain ebola:x-interpro ?interproDomain .
            ?interproDomain rdfs:label ?interprolabel.
            OPTIONAL {?domain ebola:x-go ?goTerm . ?goTerm go:namespace ?namespace; rdfs:label ?golabel}
            OPTIONAL {?domain ebola:x-kegg ?kegg}
            OPTIONAL {?domain ebola:x-metacyc ?metacyc}
            OPTIONAL {?domain ebola:x-kegg-enzyme ?enzyme}
        }";
        break;*/
}

$requestURL = constructQuery($query, $url);
$responseArray = request($requestURL);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>SPARQL Proxy Executor</title>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>

<body><?php echo $responseArray; ?>
</body>
</html>

