#!/bin/sh
set -e

# Copia el archivo desde el bind mount de Windows a una ruta interna real
# y ajusta permisos correctos (esto sí funciona porque el destino NO es bind mount).
if [ -f /my.cnf.host ]; then
  cp /my.cnf.host /etc/mysql/conf.d/my.cnf
  chmod 644 /etc/mysql/conf.d/my.cnf
fi

exec "$@"