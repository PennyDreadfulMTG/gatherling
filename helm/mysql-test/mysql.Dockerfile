# This is a Dockerfile for a test MySQL instance. It starts a MySQL instance with
# a database called "gatherling" and a root user with no password, suitable for
# running inside Kubernetes testing environments.

FROM ubuntu:20.04
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update -y && apt-get upgrade -y 
RUN apt-get install -y mysql-server
EXPOSE 3306
ADD my.cnf /etc/my.cnf
RUN rm /etc/mysql/my.cnf
ADD mysql-init.sh /mysql-init.sh
RUN chmod a+x /mysql-init.sh && /mysql-init.sh && rm -rf /mysql-init.sh
RUN rm -r /var/log/mysql /var/log/mysql_*.log || true
RUN mv /var/lib/mysql /var/lib/mysql-template
ADD launch.sh /launch.sh
RUN chmod a+x /launch.sh
ENTRYPOINT [ "/launch.sh" ]
