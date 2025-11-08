set -e

if [ -f /my.cnf.host ]; then
  cp /my.cnf.host /etc/mysql/conf.d/my.cnf
  chmod 644 /etc/mysql/conf.d/my.cnf
fi

exec "$@"