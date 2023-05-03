# ParaulogicMB
ParaulogicMB és una versió en PHP del joc del Paraulògic que he desenvolupat com a projecte d'avaluació del mòdul 7 del grau superior de Desnvolupament d'Aplicacions Web

# ProjecteM7
# ParaulògicMB - Projecte de Paraulògic Marca Blanca


## ENUNCIAT

1. Desenvolupar una aplicació web en entorn de servidor fent ús del llenguatge PHP i del sistema gestor de bases de dades MySQL.
2. Es pretén desenvolupar una petita aplicació.
3. Cada grup podrà triar quina aplicació vol desenvolupar seguint els requeriments esmentats a continuació.
4. L’aplicació servirà per gestionar una funcionalitat web, haurà d’accedir a dades emmagatzemades a una base de dades i aquestes hauran d’estar relacionades (com a mínim hi haurà d’haver una relació 1-N i una altra M-N).
5. Per accedir a l’aplicació hi haurà validació d’usuaris.
6. Les dades dels usuaris estaran emmagatzemades en una taula de la Base de dades.

### Exemple Plantilla Mínima de diagrama E-R

![Base de datos](https://github.com/lace8guti/ProjecteM7/blob/main/Captura%20de%202023-03-02%2012-54-42.png?raw=true)

## REQUERIMENTS

1. Descripció del projecte (Per a que serveix l’aplicació desenvolupada).
2. Diagrama E-R.
3. Model Relacional.
4. Script DDL SQL.
5. Seeders SQL per fer les proves (inserts).
6. Implementar l’aplicació en llenguatge de servidor PHP.
7. Gestió CRUD online de totes les taules de la base de dades amb paginació.
8. Aplicació navegable a través de menú principal que permeti accedir a les diferents opcions.
9. Ús de CSS Bootstrap.
10. Validació de les dades d’entrada de formularis a nivell servidor.
11. Validació d’usuaris fent ús de sessions.
12. Repoblament de formularis.
13. Utilitzar Repositori Github: codi font compartit per l’equip de treball, documentació de codi i control de versions.


## GESTIÓ DEL PROJECTE

- Grups de 2 persones
- Plataforma Github per compartir codi i documentar el projecte

## DATA LLIURAMENT

1. 5 maig del 2023
2. Exposicions del projecte – 08, 09, ... de maig del 2023

## MÈTODE LLIURAMENT I FORMAT

1. Link Github codi font en una tasca Moodle.
2. Documentació del projecte inclosa dins fitxer .README: 
- Índex
- Continguts 
- Diagrama E-R
- Model relacional
- Descripció de les funcionalitats implementades.
3. Presentació del projecte.

## AVALUACIÓ

1. Assistència a un 80% sessions de classe (divendres i dijous) . No s’avaluarà el projecte
si es supera aquest %.
2. Pujada a Github versió modificada del projecte final sessió de classe del divendres. 
3. No s’avaluarà el projecte si no s’assoleix un mínim de 80% de commits en les dates previstes.
4. Tots els projectes no originals o còpies seran qualificats amb un 0.
5. Es farà ús d’una rúbrica d’avaluació que podreu consultar al moodle.


-----------------------------------------------------------------------------------------------------------------

## 0. SCRUM a Jira:

Utilitzaré per aquest project la tecnologia de Jira per fer un seguiment del projecte i mantenir una dinàmica de treball que em permeti arribar als objectius dins de les dates esperades--> [ParaulogicMB](https://lace8guti.atlassian.net/jira/software/projects/PAR/boards/1)



## 1. Descripció del projecte 

Vaig començar a programar PHP al gener de 2022, al curs de Programació de Sistemes Informàtics del SOC, a l'institut Vidal i Barraquer.
Al llarg del mòdul 2 vam realitzar diferents projectes, un d'ells era una simulació del joc [Paraulògic](https://www.vilaweb.cat/paraulogic/) que més tard va desenvolupar en una versió del joc del Penjat.

El Paraulògic és un joc que consisteix en 7 lletres situades dins de 7 hexàgons, aquestes lletres poden conformar una sèrie de paraules.
L'objectiu del joc és endevinar paraules utilitzant combinacions de les lletres que ens proporcionen. A més paraules, major puntuació. 
Aquestes lletres i paraules canvien cada dia, es poden demanar pistes i la puntuació pot ser compartida a les xarxes socials.

El Paraulògic va sorgir com a resposta a una moda de jocs d'endevinar paraules (veure [Wordle](https://es.wikipedia.org/wiki/Wordle) o [Selling Bee](https://en.wikipedia.org/wiki/The_New_York_Times_Spelling_Bee)).

La idea amb aquest projecte de ParaulògicMB és replicar el joc del Paraulògic amb certes modificacions, afegir-ne algunes característiques pròpies (creació d'usuaris i manteniment de les seves dades).


![Exemple Paraulògic](https://github.com/lace8guti/ProjecteM7/blob/main/Captura%20de%202023-03-02%2013-57-00.png?raw=true)


## 2. Modificacions

A diferència de l'aplicació origial que no tenía cap mena de gestió d'usuaris, en aquesta versió, sí que gestionarem usuaris.

### Llavors necessitarem:

1) Una taula Usuaris on es guardi: 
l'id (autoincrementat, PK),
el nom d'usuari,
la contrasenya
i la puntuació total(FK) d'aquest. 

  Especificacions:
-  a) El nom d'usuari no podrà contenir ni espais ni caracters especials.  
-  b) El nom d'usuari no pot ser una paraula reservada (null, main, etc).
-  c) La contrasenya ha de tenir un mínim de 8 caracters i no pot contenir ni espais ni caracters especials.
-  d) La contrasenya es guardarà com a hash a fi d'evitar emmagatzematge d'informació sensible.

2) Una taula Puntuació que guardarà:
- la puntuació total,
- el dia,
- les paraules que ha respost per dia,
- les paraules que ha respost en total,
- les pistes que ha demanat en un dia i
- les pistes que ha demanat en total.

3) Una taula Repte que guardi:
- id del repte,
- dia
4) Una taula Paraules que guardi:
- paraules
- lletres
## DIAGRAMA DE CLASES DE L'APLICACIÓ PARAULOGICMB
![DIAGRAMA DE CLASES DE L'APLICACIÓ PARAULOGICMB](https://github.com/lace8guti/ProjecteM7-ParaulogicMB/blob/main/Captura%20de%202023-03-21%2009-50-09.png?raw=true)

### Funcions

1. Crear usuari

- a) Demana un nom d'usuari -> S'ha de comprovar que aquest nom estigui disponible -> No ha de coinicidir amb un nom d'usuari existent a la taula.

- b) Demana un nom d'usuari -> S'ha de comprovar que aquest nom no sigui una paraula reservada i que no contingui cap caracter no vàlid.

- c) Demana una contrasenya -> S'ha de comprovar que aquesta contrasenya no pot contenir ni espais ni caracters especials.

- d) S'ha de realitzar una conversió de la contrasenya al hash y compararla amb el hash emmagatzemat a la BBDD.

2. Iniciar sessió

- a) Demana un nom d'usuari i una contrasenya-> Comprovar que ambdòs camps NO estiguin buits -> Si algun dels camps és buit, misstage d'error.
- b) Demana un nom d'usuari i una contrasenya-> Comprovar que el nom d'usuari i la contrasenya són correctes -> Iniciar sessió i guardarla X
temps.

3. Generar repte diari:
- a) El repte diari s'ha de crear cada día a les 00:00, consistirá en una funció que faci una petició a la BBDD de un conjunt de paraules d'un arxiu de text de paraules i en prengui 30 per repte.

4. Comprovar les paraules:
En cas de que una paraula s'encerti, s'hauria d'avisar l'usuari i a més a més, si li otorgaran punts en funció de la llongitud de la paraula, d'altra banda, si una paraula no és correcta o ja ha estat encertada, també s'hauria d'avisar a l'usuari.

5. Mostrar informació annexa:
Així com pistes, funcionament del joc, informació sobre les paraules encertades, etc.

6. Consultar estadístiques:
Consultar les paraules encertades per l'usuari, així com un rànquin dels usuaris.

7. Canviar la contrasenya:
Permetre a l'usuari canviar la seva contrasenya si vol.



Prova de cas particular:

var t={"l":["m","e","i","a","t","c","d"],"p":{"academia": "acadèmia","academic": "acadèmic","academica": "acadèmica","accadi": "accadi","accadia": "accàdia","accidia": "accídia","acetamida": "acetamida","acid": "àcid","acida": "àcida","acidemia": "acidèmia","aciditat": "aciditat","acimada": "acimada","adam": "adam","adamic": "adàmic","adamica": "adàmica","adamita": "adamita","adamitic": "adamític","adamitica": "adamítica","addicta": "addicta","addicte": "addicte","adiatetic": "adiatètic","adiatetica": "adiatètica","aede": "aede","amatada": "amatada","amida": "amida","atacada": "atacada","cada": "cada","cadec": "càdec","cadet": "cadet","cadi": "cadi","cadmi": "cadmi","cadmic": "càdmic","cadmica": "càdmica","caid": "caid","camada": "camada","cecidi": "cecidi","cicadacia": "cicadàcia","cicadata": "cicadata","daci": "daci","dacia": "dàcia","dad": "dad","dada": "dada o dadà","daic": "daic","daica": "daica","dama": "dama o damà","data": "data","dea": "dea","deca": "decà","decada": "dècada","decidida": "decidida","decidit": "decidit","decim": "dècim","decima": "dècima","deicida": "deïcida","deicidi": "deïcidi","deitat": "deïtat","dema": "demà","demati": "dematí","dia": "dia","diac": "diac","diaca": "diaca","diacid": "diàcid","diacida": "diàcida","diada": "diada","diadema": "diadema","diademada": "diademada","diademat": "diademat","diadic": "diàdic","diadica": "diàdica","diatetic": "diatètic","diatetica": "diatètica","dic": "dic","dicdic": "dic-dic","dictam": "dictam","dictat": "dictat","dictic": "díctic","dictica": "díctica","dida": "dida","didactic": "didàctic","didactica": "didàctica","didim": "dídim","didima": "dídima","didimi": "didimi","die": "diè","dieta": "dieta","dietetic": "dietètic","dietetica": "dietètica","dimidiada": "dimidiada","dimidiat": "dimidiat","dit": "dit","dita": "dita o dità","ditada": "ditada","ditet": "ditet","ecidi": "ecidi","edat": "edat","edema": "edema","edeta": "edetà","edicte": "edicte","eidetic": "eidètic","eidetica": "eidètica","emaciada": "emaciada","emidid": "emídid","idea": "idea","idem": "ídem","imada": "imada","imida": "imida","immediat": "immediat","immediata": "immediata","immeditada": "immeditada","immeditat": "immeditat","macada": "macada","macadam": "macadam","macadamia": "macadàmia","mamada": "mamada","meda": "meda","mede": "mede","medi": "medi","media": "medià","mediat": "mediat","mediata": "mediata","mediateca": "mediateca","medic": "mèdic","medica": "mèdica","mida": "mida","tacada": "tacada","tedi": "tedi","teid": "teid","timid": "tímid","timida": "tímida","timiditat": "timiditat"}
