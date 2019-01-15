FROM fauria/lamp:latest
LABEL maintainer="Katelyn Gigante"

RUN mkdir -p /var/www/html/gatherling
WORKDIR /var/www/html/gatherling
COPY . /var/www/html/gatherling

## Expose used ports
EXPOSE 80

ENV LOG_STDOUT true
ENV LOG_STDERR true

## Run
CMD ["/usr/sbin/run-lamp.sh"]
