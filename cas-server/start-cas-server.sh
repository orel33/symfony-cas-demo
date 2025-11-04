#!/bin/bash

IMG=apereo/cas:7.3.0

docker pull $IMG

docker run --rm -it -p 9000:9000 \
  -v $(pwd)/config:/etc/cas/config \
  --name cas-server $IMG

#  -v $(pwd)/myfiles/certs/cas-keystore.p12:/etc/cas/ssl/cas-keystore.p12 \

# docker exec -it cas-server /bin/bash
