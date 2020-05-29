Infonotion
Low-code platform proof-of-concept (crude start for a low-code environment)

Installation
---
Needed:
- Neo4j 3.5.1
- Php 7.3.6
- Composer

Preparations
---
- Install Php and make it findable in your repo folder
- Install Neo4j 3.5.1
- Start Neo4j
- Open the Neo4j Browser - http://localhost:7474/browser/ 
- Set the admin user (neo4j) password to "test"

Perparations for the code
---
- Clone the repo to a folder
- Open a command-line window in the folder of the cloned repo
- Build the webapp (type ```composer install ```
- Run the webapp (type ```php bin/console server:run```)

Setting up the Database contents
---
- Copy contents of ```in_new_db_RAW.cypher``` to your clipboard
- Open the Neo4j browser
- Paste the clipboard contents in to the commmand bar and press enter

Open the application
---
- Open http://127.0.0.1:8000/
*(on Windows: using localhost:8000, can result in slowness due to the DNS resolve)*

Formula 1 example database
---
The ```/examples``` folder contains a zip-file with a cypher-shell export of a F1 database.
*(The source of this data is: https://www.kaggle.com/rohanrao/formula-1-world-championship-1950-2020)*

To add the example:
- Install Neo4j 3.5.1 *(alernatively: make sure your existing 3.5.1 installation is completely empty)*
- Unpack the zip-file to the import folder of your Neo4j
- ADD the following line to your conf/neo4j.conf *(in the apoc section)*
```
apoc.import.file.enabled=true
```
- Start Neo4j
- Go to: http://localhost:7474/browser/ and set the admin user (neo4j) password to "test"
- In the neo4j browser execute:
```
CALL apoc.export.cypher.all("in_F1_example.cypher", {
    format: "cypher-shell",
    useOptimizations: {type: "UNWIND_BATCH", unwindBatchSize: 20}
})
YIELD file, batches, source, format, nodes, relationships, properties, time, rows, batchSize
RETURN file, batches, source, format, nodes, relationships, properties, time, rows, batchSize;
```
- Open http://127.0.0.1:8000/
- You'll find an ```F1``` option on the left-side.
- Try http://127.0.0.1:8000/frameN/View/F1/Season/empty 
This last is 100% configured and shows what can be done with the current ViewController

Happy coding!
