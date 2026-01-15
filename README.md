# Symfony + CAS Demo

Voici quelques exemples de démos CAS ([Central Authentication Service](https://en.wikipedia.org/wiki/Central_Authentication_Service)). 

Pour commencer, voici quelques démos basiques sans CAS :

* [apache-demo](apache-demo/) : demo Apache2 en HTTP et HTTPS
* [php-test](php-test/) : demo PHP "pure" (sans CAS)
* [symfony-demo](symfony-demo/) : demo Symfony (sans CAS)

Dans cette démo, on dispose d'un serveur CAS local (Docker) qui se lance sur <http://localhost:9000/cas> et des exemples d'application web (client CAS) qui se lancent sur  <http://localhost:8000>.

* [cas-server](cas-server/) : serveur CAS local (Docker) 

Les démos suivantes se basent maintenant sur le serveur CAS de l'Université de Bordeaux : <https://cas.u-bordeaux.fr/cas> :

* [cas-test](cas-test/) : test CAS en PHP "pure"
* [cas-demo](cas-demo/) : demo CAS avec Symfony 





