# infonotion
Low-code platform

This is a crude example of a low-code environment.

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
- Go to: localhost:7474/browser and set the admin user (neo4j) password to "test"

Perparations for the code
---
- Pull the repo to a folder
- Open a command-line window 
- Go to the folder of the pulled repo
- Build the webapp (type "compose install")
- Run the webapp (type "php bin/console server:run")

Setting up the Database contents
---
- Copy contents of in_new_db.cypher to your clipboard
- Open the Neo4j browser
- Paste the clipboard contents in to the commmand bar and press enter\

Open the application
---
- Browse to http://127.0.0.1:8000/
