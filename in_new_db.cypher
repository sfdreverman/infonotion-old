:begin
CREATE CONSTRAINT ON (node:`UNIQUE IMPORT LABEL`) ASSERT (node.`UNIQUE IMPORT ID`) IS UNIQUE;
:commit
:begin
UNWIND [{_id:1, properties:{domain:"FunctionalType", name:"TopNavTree", description:"", in_id:"TreeTop59e89d165a80a1.34594422"}}, {_id:2, properties:{domain:"FunctionalType", name:"AdminNavTree", description:"", in_id:"TreeTop59e89e225098d2.54609738"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:DomainNavTree;
UNWIND [{_id:3, properties:{templateName:"list.html.twig", domain:"FunctionalType", name:"databrowser", description:"", in_id:"Frame5db1e07c568bd7.90359988"}}, {_id:4, properties:{templateName:"view.html.twig", domain:"FunctionalType", name:"View", description:"", in_id:"Frame5db1bd23a7e358.78338488"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:Frame;
UNWIND [{_id:5, properties:{domain:"FunctionalType", name:"ViewKind", in_id:"ViewKind5d9056c4534d93.85592346"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:MetaLookupAttr;
UNWIND [{_id:15, properties:{domain:"FunctionalType", name:"Node", description:"", in_id:"ViewKind5d904483839220.57822867"}}, {_id:16, properties:{domain:"FunctionalType", name:"FullNode", description:"", in_id:"ViewKind5d90448b3b3eb9.53361920"}}, {_id:17, properties:{domain:"FunctionalType", name:"Table", description:"", in_id:"ViewKind5d904492caeac8.29792148"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:ViewKind;
UNWIND [{_id:18, properties:{name:"DomainNavTree", in_id:"TreeTop59e89c6ce3a7f1.03420987"}}, {_id:19, properties:{name:"MetaType", in_id:"zzzAnyType5a2bb390f03bc4.01418784"}}, {_id:20, properties:{name:"MetaRel", in_id:"MetaRel5a2e993367dae9.28067919"}}, {_id:21, properties:{name:"MetaAttr", in_id:"MetaAttr5a2ea346d18580.47009090"}}, {_id:22, properties:{name:"MetaLookupAttr", in_id:"MetaLookupAttr5abd334dd5dde2.79245568"}}, {_id:23, properties:{name:"Domain", in_id:"Domain5e2821a4c1e780.95843198"}}, {_id:24, properties:{name:"Frame", in_id:"Frame5db1bc7877e9f8.91427966"}}, {_id:25, properties:{name:"ViewKind", in_id:"ViewKind5d9044648603f2.79371700"}}, {_id:26, properties:{name:"View", in_id:"View5d9056c4534c51.95434280"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:FunctionalType;
UNWIND [{_id:29, properties:{domain:"FunctionalType", name:"iscomp", in_id:"iscomp1711", attrtype:"bool"}}, {_id:30, properties:{domain:"FunctionalType", name:"multi", in_id:"multi1712", attrtype:"bool"}}, {_id:31, properties:{domain:"FunctionalType", name:"restrict", in_id:"restrict1713", attrtype:"text"}}, {_id:32, properties:{domain:"FunctionalType", name:"attrtype", in_id:"attrtype1731", attrtype:"text"}}, {_id:33, properties:{domain:"FunctionalType", name:"defval", in_id:"defval1732", attrtype:"text"}}, {_id:34, properties:{domain:"FunctionalType", defval:"false", name:"loadFirst", description:"", in_id:"MetaAttr5db2e46619ea80.91674533", attrtype:"bool"}}, {_id:35, properties:{domain:"FunctionalType", name:"templateName", in_id:"t5db1bc7877ea71.55974202", attrtype:"text"}}, {_id:36, properties:{domain:"FunctionalType", defval:"", name:"canEdit", description:"", in_id:"MetaAttr5d960d5c44c1d5.01195404", attrtype:"bool"}}, {_id:37, properties:{domain:"FunctionalType", defval:"20", name:"RecordsPerPage", description:"", in_id:"MetaAttr5da9a38d332e39.01219011", attrtype:"number"}}, {_id:38, properties:{domain:"FunctionalType", defval:"", name:"canDelete", description:"", in_id:"MetaAttr5d960d3d886e51.30090582", attrtype:"bool"}}, {_id:39, properties:{domain:"FunctionalType", name:"templateName", in_id:"t5d9056c4534cd1.60469694", attrtype:"text"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:MetaAttr;
UNWIND [{_id:40, properties:{domain:"FunctionalType", name:"hasDomain", iscomp:false, in_id:"hasDomain59e89c6ce3a871.15723249", multi:true}}, {_id:42, properties:{domain:"FunctionalType", name:"TOTYPE", iscomp:false, in_id:"TOTYPE01", multi:false}}, {_id:43, properties:{domain:"FunctionalType", name:"FROMTYPE", iscomp:false, in_id:"FROMTYPE5abd334dd5de49.74575557", multi:false}}, {_id:44, properties:{domain:"FunctionalType", name:"startView", iscomp:"false", in_id:"startView5db1bc7877eaa0.07972301", multi:false}}, {_id:45, properties:{domain:"FunctionalType", name:"MetaType", iscomp:"false", in_id:"MetaType5d9056c4534d12.41213356", multi:false}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:MetaRel;
UNWIND [{_id:60, properties:{loadFirst:false, ViewKind:"Node", templateName:"node-queries.html.twig", canEdit:false, domain:"FunctionalType", query:"MATCH (a:{metaType}) WHERE a.in_id='{instanceID}' OPTIONAL MATCH (n:{domain})<-[:MetaType]-(m:View) WHERE n.name='{metaType}' RETURN a as entity,m as query ORDER BY m.grouplabel,m.name", name:"Node View with Queries", description:"", canDelete:false, RecordsPerPage:20, in_id:"View5db2cc20317699.42818335"}}, {_id:61, properties:{ViewKind:"Table", loadFirst:false, templateName:"buttonlist.html.twig", canEdit:false, domain:"FunctionalType", query:"MATCH (n:{domain}) RETURN DISTINCT n.name,n.in_id ORDER BY n.name,n.in_id", name:"List of Types in domain", description:"", canDelete:false, RecordsPerPage:20, in_id:"View5db1dfd4842d69.70672169"}}, {_id:62, properties:{ViewKind:"Table", loadFirst:false, templateName:"table.html.twig", canEdit:false, query:"MATCH (n:{metaType}) where n.domain='{domain}' RETURN n.in_id as {metaType}_INID, n.name as Name order by Name DESC", domain:"FunctionalType", name:"List of Type (for view)", description:"0,metaType,View5db2cc20317699.42818335", canDelete:false, RecordsPerPage:20, in_id:"View5dad8b602ac362.10926618"}}, {_id:63, properties:{ViewKind:"Table", loadFirst:false, templateName:"paginatedtable.html.twig", domain:"FunctionalType", query:"MATCH (n:{metaType}) where n.domain='{domain}' RETURN n.in_id as {metaType}_INID, n.name as Name, n.description as Description order by Name SKIP {atRecord} LIMIT {maxRecords}", canEdit:false, name:"List of a Type", description:"", canDelete:false, RecordsPerPage:20, in_id:"View5d98dbc439a5a4.32197708"}}, {_id:64, properties:{ViewKind:"FullNode", loadFirst:false, templateName:"fullnode.html.twig", domain:"FunctionalType", query:"MATCH (entity:{metaType}) WHERE entity.in_id='{instanceID}' OPTIONAL MATCH (entity)-[r]-(m) RETURN DISTINCT entity,type(r) as relationName,m as relationNode,labels(m)[0] as RelationNodeMetaType, (startNode(r) = entity) as fromEntity", canEdit:true, name:"Full Node View", description:"", canDelete:true, RecordsPerPage:20, in_id:"View5d923c3fa9bb12.15795175"}}, {_id:65, properties:{ViewKind:"Node", loadFirst:false, templateName:"node.html.twig", canEdit:false, domain:"FunctionalType", query:"MATCH (entity:{metaType}) WHERE entity.in_id='{instanceID}' RETURN entity", name:"Default Node View", description:"", canDelete:false, RecordsPerPage:20, in_id:"View5d9057b5b1c4f3.27505652"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:View;
UNWIND [{_id:80, properties:{domain:"FunctionalType", name:"FunctionalType", description:"Contains all stuff (type definition for relation, attribute query, action and anytype) that has some actual use in code.\r\nThis stuff is mandatory to make the code work.\r\n(i.e. if changed, this will break basic functionality of the application)", in_id:"Domain59e89c922b1236.19435676"}}] AS row
CREATE (n:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row._id}) SET n += row.properties SET n:Domain;
:commit
:begin
UNWIND [{start: {_id:20}, end: {_id:29}, properties:{}}, {start: {_id:20}, end: {_id:30}, properties:{}}, {start: {_id:20}, end: {_id:31}, properties:{}}, {start: {_id:21}, end: {_id:32}, properties:{}}, {start: {_id:21}, end: {_id:33}, properties:{}}, {start: {_id:26}, end: {_id:34}, properties:{}}, {start: {_id:26}, end: {_id:37}, properties:{}}, {start: {_id:26}, end: {_id:39}, properties:{}}, {start: {_id:26}, end: {_id:38}, properties:{}}, {start: {_id:26}, end: {_id:36}, properties:{}}, {start: {_id:24}, end: {_id:35}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:HASATTR]->(end) SET r += row.properties;
UNWIND [{start: {_id:26}, end: {_id:5}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:HASLOATTR]->(end) SET r += row.properties;
UNWIND [{start: {_id:2}, end: {_id:80}, properties:{}}, {start: {_id:1}, end: {_id:80}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:hasDomain]->(end) SET r += row.properties;
UNWIND [{start: {_id:42}, end: {_id:19}, properties:{}}, {start: {_id:43}, end: {_id:19}, properties:{}}, {start: {_id:45}, end: {_id:19}, properties:{}}, {start: {_id:44}, end: {_id:26}, properties:{}}, {start: {_id:40}, end: {_id:23}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:TOTYPE]->(end) SET r += row.properties;
UNWIND [{start: {_id:5}, end: {_id:25}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:FROMTYPE]->(end) SET r += row.properties;
UNWIND [{start: {_id:18}, end: {_id:40}, properties:{}}, {start: {_id:20}, end: {_id:42}, properties:{}}, {start: {_id:22}, end: {_id:43}, properties:{}}, {start: {_id:26}, end: {_id:45}, properties:{}}, {start: {_id:24}, end: {_id:44}, properties:{}}] AS row
MATCH (start:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.start._id})
MATCH (end:`UNIQUE IMPORT LABEL`{`UNIQUE IMPORT ID`: row.end._id})
CREATE (start)-[r:HASREL]->(end) SET r += row.properties;
UNWIND [{start: {_id:4}, end: {_id:62}, properties:{}}, {start: {_id:3}, end: {_id:61}, properties:{}}] AS row
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
