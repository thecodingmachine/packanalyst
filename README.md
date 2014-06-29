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