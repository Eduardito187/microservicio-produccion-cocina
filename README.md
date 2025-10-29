[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/rVhxZd2x)



![Diagrama de flujo](diagrama.png)

Microservicio: Producción y Cocina
Propósito

Gestionar el ciclo de vida de la Orden de Producción/Cocina (OP) desde su creación con ítems, pasando por sus 4 estados efectivos, hasta generar la lista de despacho para el siguiente microservicio.

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

Generación de lotes asociados a la OP durante la planificación, en función de los ítems.

Lista de despacho

Al cerrar la OP se genera la lista de despacho para el siguiente microservicio (logística/entrega).

Arquitectura (implementada)

DDD + Clean Architecture + CQRS:

Agregado OrdenProduccion con colección OrderItems y VOs (Sku, Quantity).

Comandos y handlers (p. ej., GenerarOPHandler, PlanificarOPHandler, IniciarOPHandler, CerrarOPHandler).

Repositorio Eloquent para persistencia (IDs BIGINT AUTO_INCREMENT; FK order_item.op_id).

Outbox pattern:

Los eventos de dominio (p. ej., OrdenProduccionCreada, OrdenProduccionPlanificada, OrdenProduccionIniciada, OrdenProduccionCerrada) se registran en tabla outbox dentro de la misma transacción y se publican after-commit.
