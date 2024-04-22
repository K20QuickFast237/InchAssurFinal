# Instructions pour la consttruction du cotainer pour la bd mysql

# Base image
FROM mysql:latest
# FROM mysql:8.0

# Environment Variables
ENV MYSQL_ROOT_PASSWORD=root
ENV MYSQL_DATABASE=inchassurdb
ENV MYSQL_USER=kevin
ENV MYSQL_PASSWORD=kevin.

# initialise the database
ADD init-data.sql /docker-entrypoint-initdb.d
# copy the sql file
# COPY init-data.sql /docker-entrypoint-initdb.d/init-data/

# expose the port
EXPOSE 3306
