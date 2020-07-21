:begin
CREATE CONSTRAINT ON (node:`UNIQUE IMPORT LABEL`) ASSERT (node.`UNIQUE IMPORT ID`) IS UNIQUE;
:commit
:begin
UNWIND [{_id:82994, properties:{domain:"FunctionalType", name:"TopNavTree", description:"", in_id:"TreeTop59e89d165a80a1.34594422"}}, {_id:82995, properties:{domain:"FunctionalType", name:"AdminNavTree", description:"", in_id:"TreeTop59e89e225098d2.54609738"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:DomainNavTree;
UNWIND [{_id:83096, properties:{templateName:"list.html.twig", domain:"FunctionalType", name:"databrowser", description:"", in_id:"Frame5db1e07c568bd7.90359988"}}, {_id:83097, properties:{templateName:"view.html.twig", domain:"FunctionalType", name:"View", description:"", in_id:"Frame5db1bd23a7e358.78338488"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:Frame;
UNWIND [{_id:83154, properties:{domain:"FunctionalType", name:"ViewKind", in_id:"ViewKind5d9056c4534d93.85592346"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:MetaLookupAttr;
UNWIND [{_id:83183, properties:{domain:"FunctionalType", name:"Node", description:"", in_id:"ViewKind5d904483839220.57822867"}}, {_id:83184, properties:{domain:"FunctionalType", name:"FullNode", description:"", in_id:"ViewKind5d90448b3b3eb9.53361920"}}, {_id:83185, properties:{domain:"FunctionalType", name:"Table", description:"", in_id:"ViewKind5d904492caeac8.29792148"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:ViewKind;
UNWIND [{_id:83203, properties:{name:"DomainNavTree", in_id:"TreeTop59e89c6ce3a7f1.03420987"}}, {_id:83204, properties:{name:"MetaType", in_id:"zzzAnyType5a2bb390f03bc4.01418784"}}, {_id:83205, properties:{name:"MetaRel", in_id:"MetaRel5a2e993367dae9.28067919"}}, {_id:83206, properties:{name:"MetaAttr", in_id:"MetaAttr5a2ea346d18580.47009090"}}, {_id:83207, properties:{name:"MetaLookupAttr", in_id:"MetaLookupAttr5abd334dd5dde2.79245568"}}, {_id:83208, properties:{name:"Domain", in_id:"Domain5e2821a4c1e780.95843198"}}, {_id:83209, properties:{name:"Frame", in_id:"Frame5db1bc7877e9f8.91427966"}}, {_id:83210, properties:{name:"ViewKind", in_id:"ViewKind5d9044648603f2.79371700"}}, {_id:83211, properties:{name:"View", in_id:"View5d9056c4534c51.95434280"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:FunctionalType;
UNWIND [{_id:83223, properties:{domain:"FunctionalType", name:"iscomp", in_id:"iscomp1711", attrtype:"bool"}}, {_id:83224, properties:{domain:"FunctionalType", name:"multi", in_id:"multi1712", attrtype:"bool"}}, {_id:83225, properties:{domain:"FunctionalType", name:"restrict", in_id:"restrict1713", attrtype:"text"}}, {_id:83226, properties:{domain:"FunctionalType", name:"attrtype", in_id:"attrtype1731", attrtype:"text"}}, {_id:83227, properties:{domain:"FunctionalType", name:"defval", in_id:"defval1732", attrtype:"text"}}, {_id:83228, properties:{domain:"FunctionalType", defval:"false", name:"loadFirst", description:"", in_id:"MetaAttr5db2e46619ea80.91674533", attrtype:"bool"}}, {_id:83229, properties:{domain:"FunctionalType", name:"templateName", in_id:"t5db1bc7877ea71.55974202", attrtype:"text"}}, {_id:83230, properties:{domain:"FunctionalType", defval:"", name:"canEdit", description:"", in_id:"MetaAttr5d960d5c44c1d5.01195404", attrtype:"bool"}}, {_id:83231, properties:{domain:"FunctionalType", defval:"20", name:"RecordsPerPage", description:"", in_id:"MetaAttr5da9a38d332e39.01219011", attrtype:"number"}}, {_id:83232, properties:{domain:"FunctionalType", defval:"", name:"canDelete", description:"", in_id:"MetaAttr5d960d3d886e51.30090582", attrtype:"bool"}}, {_id:83233, properties:{domain:"FunctionalType", name:"templateName", in_id:"t5d9056c4534cd1.60469694", attrtype:"text"}}, {_id:83234, properties:{domain:"FunctionalType", defval:"", name:"searchquery", description:"Query to search for specific data (shows search input in table view when not empty)", in_id:"MetaAttr5f1162575c41f1.44491981", attrtype:"textarea"}}, {_id:83235, properties:{domain:"FunctionalType", defval:"", name:"query", description:"", in_id:"MetaAttr5f0ea9c48df298.23903500", attrtype:"textarea"}}, {_id:83305, properties:{domain:"FunctionalType", defval:"false", name:"canAdd", description:"", in_id:"MetaAttr5f171bbee172d5.42135295", attrtype:"bool"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:MetaAttr;
UNWIND [{_id:83243, properties:{domain:"FunctionalType", name:"hasDomain", iscomp:false, in_id:"hasDomain59e89c6ce3a871.15723249", multi:true}}, {_id:83244, properties:{domain:"FunctionalType", name:"TOTYPE", iscomp:false, in_id:"TOTYPE01", multi:false}}, {_id:83245, properties:{domain:"FunctionalType", name:"FROMTYPE", iscomp:false, in_id:"FROMTYPE5abd334dd5de49.74575557", multi:false}}, {_id:83246, properties:{domain:"FunctionalType", name:"startView", iscomp:"false", in_id:"startView5db1bc7877eaa0.07972301", multi:false}}, {_id:83247, properties:{domain:"FunctionalType", name:"MetaType", iscomp:"false", in_id:"MetaType5d9056c4534d12.41213356", multi:false}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:MetaRel;
UNWIND [{_id:83263, properties:{loadFirst:false, ViewKind:"Node", templateName:"node-queries.html.twig", canEdit:false, domain:"FunctionalType", query:"MATCH (a:{metaType}) WHERE a.in_id='{instanceID}' OPTIONAL MATCH (n:{domain})<-[:MetaType]-(m:View) WHERE n.name='{metaType}' RETURN a as entity,m as query ORDER BY m.grouplabel,m.name", name:"Node View with Queries", description:"", canDelete:false, RecordsPerPage:20, in_id:"View5db2cc20317699.42818335"}}, {_id:83264, properties:{ViewKind:"Table", loadFirst:false, templateName:"buttonlist.html.twig", canEdit:false, domain:"FunctionalType", query:"MATCH (n:{domain}) RETURN DISTINCT n.name,n.in_id ORDER BY n.name,n.in_id", name:"List of Types in domain", description:"", canDelete:false, RecordsPerPage:20, in_id:"View5db1dfd4842d69.70672169"}}, {_id:83265, properties:{ViewKind:"Table", loadFirst:false, canEdit:false, query:"MATCH (n:{metaType}) where n.domain='{domain}' RETURN n.in_id as {metaType}_INID, n.name as Name order by Name DESC", canAdd:false, description:"0,metaType,View5db2cc20317699.42818335", RecordsPerPage:20, in_id:"View5dad8b602ac362.10926618", templateName:"table.html.twig", domain:"FunctionalType", name:"List of Type (for view)", searchquery:"", canDelete:false}}, {_id:83266, properties:{ViewKind:"Table", loadFirst:false, canEdit:false, query:"MATCH (n:{metaType}) where n.domain='{domain}' RETURN n.in_id as {metaType}_INID, n.name as Name, n.description as Description order by Name SKIP {atRecord} LIMIT {maxRecords}", canAdd:true, description:"", RecordsPerPage:20, in_id:"View5d98dbc439a5a4.32197708", templateName:"paginatedtable.html.twig", domain:"FunctionalType", name:"List of a Type", searchquery:"MATCH (n:{metaType}) where n.domain='{domain}' AND lower(n.name) CONTAINS '{searchstring}' RETURN n.in_id as {metaType}_INID, n.name as Name, n.description as Description order by Name SKIP {atRecord} LIMIT {maxRecords}", canDelete:false}}, {_id:83267, properties:{ViewKind:"FullNode", loadFirst:false, templateName:"fullnode.html.twig", canEdit:true, domain:"FunctionalType", query:"MATCH (entity:{metaType}) WHERE entity.in_id='{instanceID}' OPTIONAL MATCH (entity)-[r]-(m) RETURN DISTINCT entity,type(r) as relationName,m as relationNode,labels(m)[0] as RelationNodeMetaType, (startNode(r) = entity) as fromEntity", name:"Full Node View", description:"", canDelete:true, RecordsPerPage:20, in_id:"View5d923c3fa9bb12.15795175"}}, {_id:83268, properties:{ViewKind:"Node", loadFirst:false, templateName:"node.html.twig", canEdit:false, domain:"FunctionalType", query:"MATCH (entity:{metaType}) WHERE entity.in_id='{instanceID}' RETURN entity", name:"Default Node View", description:"", canDelete:false, RecordsPerPage:20, in_id:"View5d9057b5b1c4f3.27505652"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:View;
UNWIND [{_id:82996, properties:{domain:"FunctionalType", name:"FunctionalType", description:"Contains all stuff (type definition for relation, attribute query, action and anytype) that has some actual use in code.\r\nThis stuff is mandatory to make the code work.\r\n(i.e. if changed, this will break basic functionality of the application)", in_id:"Domain59e89c922b1236.19435676"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:Domain;
:commit
:begin
UNWIND [{start: {_id:83205}, end: {_id:83223}, properties:{}}, {start: {_id:83205}, end: {_id:83224}, properties:{}}, {start: {_id:83205}, end: {_id:83225}, properties:{}}, {start: {_id:83206}, end: {_id:83226}, properties:{}}, {start: {_id:83206}, end: {_id:83227}, properties:{}}, {start: {_id:83211}, end: {_id:83228}, properties:{}}, {start: {_id:83211}, end: {_id:83231}, properties:{}}, {start: {_id:83211}, end: {_id:83233}, properties:{}}, {start: {_id:83211}, end: {_id:83232}, properties:{}}, {start: {_id:83211}, end: {_id:83230}, properties:{}}, {start: {_id:83209}, end: {_id:83229}, properties:{}}, {start: {_id:83211}, end: {_id:83235}, properties:{}}, {start: {_id:83211}, end: {_id:83234}, properties:{}}, {start: {_id:83211}, end: {_id:83305}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:HASATTR]->(end) SET r += row.properties;
UNWIND [{start: {_id:83211}, end: {_id:83154}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:HASLOATTR]->(end) SET r += row.properties;
UNWIND [{start: {_id:82995}, end: {_id:82996}, properties:{}}, {start: {_id:82994}, end: {_id:82996}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:hasDomain]->(end) SET r += row.properties;
UNWIND [{start: {_id:83244}, end: {_id:83204}, properties:{}}, {start: {_id:83245}, end: {_id:83204}, properties:{}}, {start: {_id:83247}, end: {_id:83204}, properties:{}}, {start: {_id:83246}, end: {_id:83211}, properties:{}}, {start: {_id:83243}, end: {_id:83208}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:TOTYPE]->(end) SET r += row.properties;
UNWIND [{start: {_id:83154}, end: {_id:83210}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:FROMTYPE]->(end) SET r += row.properties;
UNWIND [{start: {_id:83203}, end: {_id:83243}, properties:{}}, {start: {_id:83205}, end: {_id:83244}, properties:{}}, {start: {_id:83207}, end: {_id:83245}, properties:{}}, {start: {_id:83211}, end: {_id:83247}, properties:{}}, {start: {_id:83209}, end: {_id:83246}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:HASREL]->(end) SET r += row.properties;
UNWIND [{start: {_id:83097}, end: {_id:83265}, properties:{}}, {start: {_id:83096}, end: {_id:83264}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:startView]->(end) SET r += row.properties;
:commit
:begin
MATCH (n:`UNIQUE IMPORT LABEL`)  WITH n LIMIT 20000 REMOVE n:`UNIQUE IMPORT LABEL` REMOVE n.`UNIQUE IMPORT ID`;
:commit
:begin
DROP CONSTRAINT ON (node:`UNIQUE IMPORT LABEL`) ASSERT (node.`UNIQUE IMPORT ID`) IS UNIQUE;
:commit
