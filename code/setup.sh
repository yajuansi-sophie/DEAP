#!/bin/bash

#########################################################################
#
# Create a directory tree that DEAP can use to store non-volotile data.
#
#########################################################################

project=ABCD
user=`/usr/bin/whoami`
rootpath=/var/www/html

if [ "${user}" != "root" ]; then
    echo "Usage: should be called by the root user"
    exit -1
fi

if [ ! -d "${rootpath}/data/${project}" ]; then
    echo "Usage: mount an external directory with an Rds data file to ${rootpath}/data/${project}"
    exit -1
fi

dataRds=`ls *.Rds | head -1`
if [ -z "${dataRds}" ] && [ ! -f "${dataRds}" ]; then
    echo "Detected initial data Rds in project data directory, start setup..."
    mkdir -p "${rootpath}/data/${project}/data_uncorrected"
    mkdir -p "${rootpath}/data/${project}/Ontology/searchServer/"
    cp "${rootpath}/applications/Ontology/searchServer/teach.json" "${rootpath}/data/${project}/Ontology/searchServer/"
    mkdir -p "${rootpath}/data/${project}/NewDataExpo/usercache/"
    mkdir -p "${rootpath}/data/${project}/Pre-Registration/"

    # the owner of all of these should be the web-user (fixed user across all docker instances)
    chown -R www-data:www-data "${rootpath}/data/${project}/"
elif [ -f "${rootpath}/data/${project}/data_uncorrected/nda17.Rds" ]; then
    echo "Setup found. Nothing needs to be done."
    exit
else
    echo "Error: Setup has not been done and no data file detected. DEAP project directory structure is not correctly setup."
    exit
fi
