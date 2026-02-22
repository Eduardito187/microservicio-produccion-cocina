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
Los eventos de dominio (p. ej., OrdenProduccionCreada, OrdenProduccionPlanificada, OrdenProduccionIniciada, OrdenProduccionCerrada) se registran mediante un Unit of Work de Outbox.
El Unit of Work se flushea dentro de la transacción de aplicación (antes del commit), garantizando atomicidad entre cambios de estado y escritura en `outbox`.
La publicación a broker se ejecuta `after-commit`.

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

## Inbound (Inbox + RabbitMQ Consumer)

- El consumer usa configuración **INBOUND_RABBITMQ_*** (sin fallback a outbox).
- `schema_version` es obligatorio para inbound.
- Para consumir eventos externos de recetas debes incluir `planes.*` en `INBOUND_RABBITMQ_ROUTING_KEYS`.
- Para eventos de calendario externos usa también `calendarios.*` en `INBOUND_RABBITMQ_ROUTING_KEYS`.
- Para eventos de contratos externos usa también `contrato.*` en `INBOUND_RABBITMQ_ROUTING_KEYS`.
- Para eventos externos de pacientes incluye `paciente.paciente-creado,paciente.paciente-actualizado,paciente.paciente-eliminado` en `INBOUND_RABBITMQ_ROUTING_KEYS`.
- Schemas de eventos: `docs/schemas/envelope.json`, `docs/schemas/inbound-calendar.json`, `docs/schemas/inbound-logistica.json`, `docs/schemas/inbound-recetas.json`, `docs/schemas/inbound-contratos.json`.
 - Schemas outbound mínimos: `docs/schemas/outbound-produccion.json`.
 - Política de versionado: `docs/VERSIONING.md`.

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

Nota: el identificador principal del recurso es `aggregate_id`. En el evento `PaqueteParaDespachoCreado` también se incluye `payload.id` por requerimiento de contrato externo.

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

### Retry + DLQ (Inbound)
Variables:
```
INBOUND_RABBITMQ_RETRY_EXCHANGE=
INBOUND_RABBITMQ_RETRY_QUEUE=
INBOUND_RABBITMQ_RETRY_ROUTING_KEY=
INBOUND_RABBITMQ_RETRY_DELAYS=10,60,300
INBOUND_RABBITMQ_DLX=
INBOUND_RABBITMQ_DLQ=
INBOUND_RABBITMQ_DLQ_ROUTING_KEY=
```
- El consumer aplica backoff usando retry exchange/queue con TTL.
- Mensajes inválidos (payload/envelope) se envían directamente a DLQ (no requeue).

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
## Fase 2 (fuera de alcance)

La Fase 2 del sistema contempla la incorporación de los contextos de Logística, Calendario y Catering, habilitando la planificación dinámica de entregas, optimización de rutas y trazabilidad completa de la entrega mediante eventos de dominio y procesos de orquestación distribuidos (Sagas). Esta fase queda explícitamente fuera del alcance de la implementación actual, pero se deja diseñada y preparada a nivel de arquitectura.

### Qué habilita la Fase 2 (qué cambia)

En Fase 1:
- Producción reacciona a datos maestros
- Producción emite eventos propios
- No coordina a nadie

En Fase 2:
- Producción participa en flujos multi-servicio
- Aparecen logística, entregas, rutas y confirmaciones
- Los eventos ya tienen consecuencias externas

### Nuevos Bounded Contexts (explícitos)

A) Logística / Entregas
- Responsable de rutas, repartidores, evidencia de entrega, incidencias
- Eventos típicos: RutaGenerada, PaqueteEnRuta, EntregaConfirmada, EntregaFallida, EntregaReprogramada

B) Calendario & Planificación
- Responsable de días con entrega/sin entrega, cambios con anticipación, direcciones por día
- Eventos: CalendarioEntregaCreado, EntregaProgramada, DiaSinEntregaMarcado, DireccionEntregaCambiada, EntregaReprogramada

C) Catering / Planes Alimentarios
- Responsable de planes 15/30 días, recetas por día, relación plan ↔ paciente
- Eventos: PlanCateringContratado, PlanCateringActualizado, PlanCateringCancelado

### Qué hace Producción y Cocina en Fase 2

Producción no lidera estos contextos, solo colabora.

Producción pasa a:
- Consumir: EntregaProgramada, DiaSinEntregaMarcado, DireccionEntregaCambiada
- Recalcular: Órdenes de Producción, Paquetes
- Emitir: OrdenProduccionRecalculada, ProduccionAjustada

### Process Manager / Saga (clave de Fase 2)

En Fase 1:
- Evento → handler → fin

En Fase 2:
- Evento → Process Manager → comandos a otros servicios

Ejemplo:
- PlanCateringContratado
  - ProcessManagerCatering
  - CrearCalendarioEntrega
  - GenerarOrdenProduccion
  - NotificarLogistica

El Process Manager no vive en Producción. Vive en el servicio de orquestación o en el bounded context dueño del flujo (Catering).

### Cambios técnicos obligatorios en Fase 2

RabbitMQ
- Exchanges por contexto
- DLQ real por inbound
- Retry con backoff
- Event versioning obligatorio

Seguridad
- correlation_id obligatorio end-to-end
- audit trail de eventos críticos
- idempotencia cross-service

Testing
- Contract Testing (Pact) obligatorio
- Tests de saga / process manager
- Chaos testing básico (retry / duplicados)

## Fase 3 (fuera de alcance)

La Fase 3 del sistema contempla la incorporación de capacidades analíticas, de optimización y de evidencia operativa, permitiendo a la organización evolucionar hacia una toma de decisiones basada en datos, trazabilidad completa y mejora continua de sus procesos de producción y entrega.

### Qué cambia de verdad en Fase 3

En Fase 2:
- El sistema coordina
- Las decisiones son reglas estáticas

En Fase 3:
- El sistema aprende
- Las decisiones se optimizan
- Cada entrega tiene prueba

### Nuevos Bounded Contexts (reales)

A) Analytics & Observabilidad
- Responsable de KPIs operativos, SLAs y métricas de entrega
- Eventos consumidos: todos los eventos de Producción, Logística y fallas/reintentos
- Produce: IndicadorActualizado, AlertaOperativaGenerada

B) Optimización / IA ligera
- Responsable de optimizar rutas, predecir fallas, ajustar producción
- Inputs: historial de entregas, geo, tiempo/clima (opcional)
- Outputs: RutaOptimizadaSugerida, ProduccionAjusteRecomendado
- No reemplaza decisiones humanas al inicio: recomienda, no ordena

C) Evidencia & Auditoría
- Responsable de pruebas de entrega, disputas y trazabilidad legal
- Eventos: EntregaConfirmada, EntregaFallida, EvidenciaAdjuntada
- Datos: foto, geo, timestamp, dispositivo/repartidor

### Producción en Fase 3

Producción no cambia mucho, y eso es una buena señal.

Hace:
- Emitir eventos limpios
- Corregir producción ante recomendaciones

Consume:
- ProduccionAjusteRecomendado (opcional)
- AlertaOperativaGenerada

### Cambios técnicos obligatorios en Fase 3

Event Store (opcional pero ideal)
- Persistir eventos como fuente histórica
- Permite replay y reconstrucción

Feature Store
- Distancias, horarios, historial paciente, frecuencia de cambios

Gobernanza de eventos
- schema_version obligatorio
- backward compatibility
- eventos deprecados documentados

### Ejemplos concretos de inteligencia

Predicción de fallas
- EntregaProgramada
- Modelo detecta riesgo (histórico + zona)
- EntregaRiesgoAltoDetectado
- Logística refuerza ruta

Ajuste de producción
- HistorialBatch + Merma
- Modelo recomienda ajuste
- ProduccionAjusteRecomendado

### Testing en Fase 3

Necesitas:
- Replay de eventos
- Tests de drift de modelos
- Simulación de escenarios históricos



#39
