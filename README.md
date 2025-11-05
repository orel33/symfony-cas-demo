# CAS Demo

Dans cette démo CAS ([Central Authentication Service](https://en.wikipedia.org/wiki/Central_Authentication_Service)), on dispose d'un serveur CAS (Docker) qui se lance sur <http://localhost:9000/cas> et des exemples d'application web (client CAS) qui se lancent sur  <http://localhost:8000>.

* [cas-server](cas-server/) : serveur CAS local (Docker) 
* [cas-test](cas-test/) : test CAS en PHP "pure"
* [cas-demo](cas-demo/) : demo CAS avec Symfony

**Nota Bene** : Les exemples actuels fonctionnent uniquement avec le protocole HTTP (et pas encore HTTPS).
