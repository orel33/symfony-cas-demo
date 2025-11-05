#!/bin/bash

IMG=apereo/cas:7.3.0

# useful to update the image (or not?)
docker pull $IMG

docker run --rm -it -p 9000:9000 \
  -v $(pwd)/config:/etc/cas/config \
  --name cas-server $IMG

#  -v $(pwd)/certs/cas-keystore.p12:/etc/cas/ssl/cas-keystore.p12 \
