const path = require("path");
const axios = require("axios");
const { PactV3, MatchersV3 } = require("@pact-foundation/pact");

const { integer, string } = MatchersV3;

(async () => {
  const provider = new PactV3({
    consumer: "orquestador-produccion",
    provider: "microservicio-produccion-cocina",
    dir: path.resolve(__dirname, "..", "pacts"),
    logLevel: "info",
  });

  // IMPORTANTE (Laravel):
  // - Accept: application/json evita redirecciones 302 y respuestas HTML
  const JSON_HEADERS = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };

  // 1) Interacción: Generar OP
  provider
    .given("product SKU1 exists")
    .uponReceiving("POST generar OP")
    .withRequest({
      method: "POST",
      path: "/api/produccion/ordenes/generar",
      headers: JSON_HEADERS,
      body: {
        fecha: string("2025-12-19"),
        sucursalId: string("SCZ"),
        items: [{ sku: string("SKU1"), qty: integer(2) }],
      },
    })
    .willRespondWith({
      status: 201,
      headers: { "Content-Type": "application/json" },
      body: {
        ordenProduccionId: integer(1),
      },
    });

  // 2) Interacción: Planificar OP
  // OJO: tu controller valida estacionId y recetaVersionId como required,
  // si no los envías, Laravel devuelve 422.
  provider
    .given("orden produccion 1 exists and porcion 1 exists")
    .uponReceiving("POST planificar OP")
    .withRequest({
      method: "POST",
      path: "/api/produccion/ordenes/planificar",
      headers: JSON_HEADERS,
      body: {
        ordenProduccionId: integer(1),
        estacionId: integer(1),
        recetaVersionId: integer(1),
        porcionId: integer(1),
      },
    })
    .willRespondWith({
      status: 201,
      headers: { "Content-Type": "application/json" },
      body: {
        ordenProduccionId: integer(1),
      },
    });

  await provider.executeTest(async (mockServer) => {
    const client = axios.create({
      baseURL: mockServer.url,
      validateStatus: () => true,
      headers: JSON_HEADERS,
    });

    // Llamada 1: generar
    const r1 = await client.post("/api/produccion/ordenes/generar", {
      fecha: "2025-12-19",
      sucursalId: "SCZ",
      items: [{ sku: "SKU1", qty: 2 }],
    });

    if (r1.status !== 201 || typeof r1.data?.ordenProduccionId !== "number") {
      throw new Error(
        `Fallo contrato generar OP (status=${r1.status}, body=${JSON.stringify(
          r1.data
        )})`
      );
    }

    const opId = r1.data.ordenProduccionId;

    // Llamada 2: planificar (incluye estacionId + recetaVersionId)
    const r2 = await client.post("/api/produccion/ordenes/planificar", {
      ordenProduccionId: opId,
      estacionId: 1,
      recetaVersionId: 1,
      porcionId: 1,
    });

    if (r2.status !== 201 || typeof r2.data?.ordenProduccionId !== "number") {
      throw new Error(
        `Fallo contrato planificar OP (status=${r2.status}, body=${JSON.stringify(
          r2.data
        )})`
      );
    }
  });

  console.log("✅ Pact generado en contract-tests/consumer/pacts/");
})().catch((e) => {
  console.error(e);
  process.exit(1);
});