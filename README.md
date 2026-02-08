![Diagrama de flujo](diagrama.png)

Microservicio: Producción y Cocina
Propósito

Gestionar el ciclo de vida de la Orden de Producción/Cocina desde su creación con ítems, pasando por sus 4 estados efectivos, hasta generar la lista de despacho para el siguiente microservicio.

Funcionalidades actuales

Creación de OP con ítems
Alta de la orden con su detalle (SKU y cantidades).
Validación de SKUs y snapshot de precios en los ítems (unit_price, unit_special_price, final_price).
Flujo de estados (4 estados)

CREADA → PLANIFICADA → EN_PROCESO → CERRADA.

Transiciones mediante comandos de aplicación:
GenerarOP → crea OP en CREADA.
PlanificarOP → genera batches/lotes de producción y pasa a PLANIFICADA.
IniciarOP → valida y pasa a EN_PROCESO.
CerrarOP → consolida y pasa a CERRADA.

Batches de producción
Generación de asociados a la OP durante la planificación, en función de los ítems.

Lista de despacho
Al cerrar la OP se genera la lista de despacho para el siguiente microservicio (logística/entrega).

Arquitectura
DDD + Clean Architecture + CQRS:
Agregado OrdenProduccion con colección OrderItems y VOs (Sku, Quantity).

Comandos y handlers (p. ej., GenerarOPHandler, PlanificarOPHandler, IniciarOPHandler, CerrarOPHandler).
Repositorio Eloquent para persistencia (IDs BIGINT AUTO_INCREMENT; FK order_item.op_id).

Outbox pattern:
Los eventos de dominio (p. ej., OrdenProduccionCreada, OrdenProduccionPlanificada, OrdenProduccionIniciada, OrdenProduccionCerrada) se registran en tabla outbox dentro de la misma transacción y se publican after-commit (Si los datos logran registrarse).

---

## API Gateway (Laravel + Keycloak)

Endpoints:

- `POST /api/login` (sin token)
- `GET /api/users` (con token Keycloak)
- `GET /api/posts` (con token Keycloak)

### Keycloak (OIDC/OAuth2)

- Realm: `classroom`
- Client: `api-gateway`
- User: `student` / `student123`
- URL interna (docker): `http://keycloak:8080`
- Token endpoint: `/realms/classroom/protocol/openid-connect/token`
- JWKS endpoint: `/realms/classroom/protocol/openid-connect/certs`
- Issuer esperado: `http://keycloak:8080/realms/classroom`

### Levantar con Docker

```bash
docker compose up --build
```

php artisan queue:work


### Ejemplos curl

Login:

```bash
curl -s -X POST http://localhost:8000/api/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"student","password":"student123"}'
```

Users (reemplaza `$TOKEN`):

```bash
curl -s http://localhost:8000/api/users \
  -H "Authorization: Bearer $TOKEN"
```

Posts:

```bash
curl -s http://localhost:8000/api/posts \
  -H "Authorization: Bearer $TOKEN"
```

## Outbox -> RabbitMQ (opcional)

Puedes publicar eventos del outbox a RabbitMQ en lugar de HTTP.

### Requisitos
- Instalar librería: `composer require php-amqplib/php-amqplib`
- Configurar driver:
  - `EVENTBUS_DRIVER=rabbitmq`

### Variables de entorno
```
EVENTBUS_DRIVER=rabbitmq
RABBITMQ_HOST=154.38.180.80
RABBITMQ_PORT=5672
RABBITMQ_USER=admin
RABBITMQ_PASSWORD=rabbit_mq
RABBITMQ_VHOST=/
RABBITMQ_EXCHANGE=outbox.events
RABBITMQ_EXCHANGE_TYPE=fanout
RABBITMQ_EXCHANGE_DURABLE=true
RABBITMQ_ROUTING_KEY=
RABBITMQ_QUEUE=
RABBITMQ_QUEUE_DURABLE=true
RABBITMQ_QUEUE_EXCLUSIVE=false
RABBITMQ_QUEUE_AUTO_DELETE=false
RABBITMQ_BINDING_KEY=
RABBITMQ_PUBLISH_RETRIES=3
RABBITMQ_PUBLISH_BACKOFF_MS=250
```

### Payload publicado
```json
{
  "event_id": "<uuid>",
  "event": "OrdenProduccionCreada",
  "occurred_on": "2026-02-08T00:00:00+00:00",
  "schema_version": 1,
  "correlation_id": "<uuid>",
  "aggregate_id": "<uuid>",
  "payload": { "...": "..." }
}
```

### Envelope oficial (Outbox/RabbitMQ)
- `event_id` (uuid)
- `event` (nombre de clase)
- `occurred_on` (ISO 8601)
- `schema_version` (int)
- `correlation_id` (uuid)
- `aggregate_id` (uuid)
- `payload` (camelCase)

Nota: el identificador principal del recurso es `aggregate_id`. El payload no repite ese valor para evitar duplicidad.

### Routing key y binding
- Si `RABBITMQ_ROUTING_KEY` está vacío, se usa el nombre del evento normalizado como routing key.
- Si configuras `RABBITMQ_QUEUE`, se declara la cola y se hace bind automático:
  - `RABBITMQ_BINDING_KEY` si está definido, o el routing key calculado.

### Queues por evento (default)
Cada evento de dominio se publica en su propia cola:
- `ProductoCreado` → `produccion.producto-creado`
- `ProductoActualizado` → `produccion.producto-actualizado`
- `RecetaVersionCreada` → `produccion.receta-creada`
- `RecetaVersionActualizada` → `produccion.receta-actualizada`
- `DireccionCreada` → `produccion.direccion-creada`
- `DireccionActualizada` → `produccion.direccion-actualizada`
- `PacienteCreado` → `produccion.paciente-creado`
- `PacienteActualizado` → `produccion.paciente-actualizado`
- `SuscripcionCreada` → `produccion.suscripcion-creada`
- `SuscripcionActualizada` → `produccion.suscripcion-actualizada`
- `PaqueteCreado` → `produccion.paquete-creado`
- `PaqueteActualizado` → `produccion.paquete-actualizado`
- `PaqueteParaDespachoCreado` → `produccion.paquete-despacho-creado`
- `CalendarioCreado` → `produccion.calendario-creado`
- `CalendarioActualizado` → `produccion.calendario-actualizado`
- `CalendarioItemCreado` → `produccion.calendario-item-creado`
- `CalendarioItemActualizado` → `produccion.calendario-item-actualizado`
- `OrdenProduccionCreada` → `produccion.orden-creada`
- `OrdenProduccionPlanificada` → `produccion.orden-planificada`
- `OrdenProduccionProcesada` → `produccion.orden-procesada`
- `OrdenProduccionCerrada` → `produccion.orden-cerrada`
- `OrdenProduccionDespachada` → `produccion.orden-despachada`
- `ProduccionBatchCreado` → `produccion.batch-orden-creado`

Si quieres otra cola por evento, edita `config/rabbitmq.php` en `event_queues`.

## Actividad 4 – Capa de testing (Unit + Integration) + Coverage >=80%

Este repositorio incluye:

- **Unit tests** (sin DB): lógica de Dominio + Application Handlers.
- **Integration tests** (con DB): flujos end-to-end vía HTTP (Feature tests).

### 1) Ejecutar Unit tests

```bash
php artisan test --testsuite=Unit
```

### 2) Generar Code Coverage (Unit tests)

> Requisito: tener un driver de coverage habilitado Xdebug.

```bash
php artisan test --testsuite=Unit --coverage-text --coverage-html=storage/coverage
```

El reporte HTML queda en `storage/coverage/index.html`.

**Nota:** La configuración de `phpunit.xml` limita la cobertura a `app/Domain` y `app/Application` para que el porcentaje refleje la lógica del microservicio (no el boilerplate de Laravel).

### 3) Ejecutar Integration tests (2 flujos)

```bash
php artisan test --testsuite=Feature
```

Flujos incluidos:

1. `FlujoOrdenProduccionIntegrationTest`: **generar → planificar → procesar → despachar**.
2. `EventBusIntegrationTest`: **auth + idempotencia** del endpoint `/api/event-bus`.

---
