# ebolakbdashboard
**Ebola Virus-centered Knowledge Base Dashboard**

This is the visualization dashboard developed for the representation of the different facets of information in the Ebola virus-centered Knowledgebase. 
It communicates with the Ebola-KB endpoint using the SPARQL 1.1 protocol http://ebola-sparql.semanticscience.org/sparql, though the RDF data is provided under the data folder, which can be deployed in a local triple store. The modifications to the endpoint need to be made in the makeRequest.php file.
The EBOV Genomic information, Genome Wheel, relevant associated PubMed Publications, relevant associated PDB Ligand information and corresponding Drug information from DrugBank, a 3D molecular structure viewer, Mesh term graph viewer and Interpro and Gene Ontology annotations for each protein domain are provided as different widgets in the dashboard.

The online version is available at http://ebola.semanticscience.org/ 

**Requirements**
Apache 2 Server
PHP5 support
Ebola-KB SPARQL Endpoint

**Libraries**
jQuery
Bootstrap
D3JS
SigmaJS

**Contact**
maulikrk@stanford.edu
