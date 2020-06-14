Infonotion
Low-code platform proof-of-concept (crude start for a low-code environment)

Installation
---
Needed:
- Neo4j 3.5.1, with apoc 3.5.0.11-all
- Php 7.3.6
- Composer 1.10.6

Preparations
---
- Install Php and make it findable in your repo folder
- Install Neo4j 3.5.1
- Install the apoc library (put the .jar in the neo4j/plugins folder)
- Start Neo4j
- Open the Neo4j Browser - http://localhost:7474/browser/ 
- Set the admin user (neo4j) password to "test" with the following command in the Neo4j Browser:  

		:server change-password

Preparations for the code
---
- Clone the repo to a folder
- Open a command-line window in the folder of the cloned repo
- Build the webapp (type ```composer install ```
- Run the webapp (type ```php bin/console server:run```)

Setting up the Database contents
---
- Open the Neo4j browser
- Execute:
```
CALL apoc.cypher.runFile("in_new_db.cypher")
```

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
- Install the apoc library (put the .jar in the neo4j/plugins folder)
- Unpack the zip-file to the import folder of your Neo4j
- ADD the following line to your conf/neo4j.conf *(in the apoc section)*
```
apoc.import.file.enabled=true
```
- Start Neo4j
- Go to: http://localhost:7474/browser/ and set the admin user (neo4j) password to "test"
- In the neo4j browser execute:
```
CALL apoc.cypher.runFile("in_F1_example.cypher")
```
- Open http://127.0.0.1:8000/
- You'll find a new ```F1``` domain on the left-side.
- Try http://127.0.0.1:8000/frameN/View/F1/Season/empty 
This last is 100% configured and shows what can be done with the current ViewController

Questionnaire example database
---
The /examples folder contains a zip-file with a cypher-shell export of the Questionnaire database.

To add the example follow the steps of the F1 example, but with:
- for unpacking the zip use: "in_Questionnaire_example.zip"
- for the *runFile* command use: "in_Questionnaire_example.cypher"

In the neo4j browser execute:
Open http://127.0.0.1:8000/questionnaire/ListOfQuestions5edd01130cda05.66170200

Happy coding!
