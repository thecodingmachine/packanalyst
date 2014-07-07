Packanalyst
===========

A PHP package analyzer for Composer/Packagist.

Install
-------

Install Neo4J

Install ElasticSearch

###Install ElasticSearch river module for Neo4J:

In elastic search directory, run:

```js
bin/plugin -install com.sksamuel.elasticsearch/elasticsearch-river-neo4j/1.2.1.1

curl -XPUT 'http://localhost:9200/_river/my_neo_river/_meta' -d '{
    "type": "neo4j",
    "neo4j": {
        "uri": "http://localhost:7474",
        "interval": 10000
    },
    "index": {
        "name": "neo4j",
        "type": "allitems"
    }
}'
```


BON, NEO4J, C'est galère!
TESTONS AVEC MONGODB.

Idée de modèle:

Items:

{
	"name": "FQDN",
	"inherits": [ FQDN1, FQDN2... ],
	"globalInherits": [ FQDN1, FQDN2... ], // inherits + inherits of parents, recursively
	"type": "class",
	"packageName": "packagename",
	"packageVersion": "version",
	"phpDoc": "doc class"

}

index sur: packageName + packageVersion
index sur: name
index sur: inherits

Mais aussi:

{
	packageName: ""
	version: ""
	type: ""
	releaseDate: date
}