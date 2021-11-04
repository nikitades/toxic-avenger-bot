#!/bin/sh
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE USER toxicavenger WITH PASSWORD 'toxicavenger';
    CREATE DATABASE toxicavenger;
    GRANT ALL PRIVILEGES ON DATABASE toxicavenger TO toxicavenger;
EOSQL
