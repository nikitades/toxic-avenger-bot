#!/bin/sh
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE USER whocaresbot WITH PASSWORD 'whocaresbot';
    CREATE DATABASE whocaresbot;
    GRANT ALL PRIVILEGES ON DATABASE whocaresbot TO whocaresbot;
EOSQL
