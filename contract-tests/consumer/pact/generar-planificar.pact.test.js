const path = require("path");
const axios = require("axios");
const { PactV3 } = require("@pact-foundation/pact");

(async () => {
  const provider = new PactV3(
    {
      consumer: "orquestador-produccion",
      provider: "microservicio-produccion-cocina",
      dir: path.resolve(__dirname, "..", "pacts"),
      logLevel: "info"
    }
  );
  const JSON_HEADERS = {
    "Content-Type": "application/json",
    "Accept": "application/json"
  };

  // 1) Generar OP
  provider.given("product SKU1 exists").uponReceiving("POST generar OP")
    .withRequest({
      method: "POST",
      path: "/api/produccion/ordenes/generar",
      headers: JSON_HEADERS,
      body: {fecha: "2025-12-19", sucursalId: "SCZ", items: [{sku: "SKU1", qty: 2}]}
    })
    .willRespondWith({
      status: 201,
      headers: {"Content-Type": "application/json"},
      body: {ordenProduccionId: 1}
    });

  // 2) Planificar OP
  provider.given("orden produccion 1 exists and porcion 1 exists").uponReceiving("POST planificar OP")
    .withRequest({
      method: "POST",
      path: "/api/produccion/ordenes/planificar",
      headers: JSON_HEADERS,
      body: {ordenProduccionId: 1, estacionId: 1, recetaVersionId: 1, porcionId: 1}
    })
    .willRespondWith({
      status: 201,
      headers: {"Content-Type": "application/json"},
      body: {ordenProduccionId: 1}
    });

  await provider.executeTest(async (mockServer) => {
    const client = axios.create({baseURL: mockServer.url, validateStatus: () => true, headers: JSON_HEADERS});
    const request1 = await client.post("/api/produccion/ordenes/generar", {fecha: "2025-12-19", sucursalId: "SCZ", items: [{sku: "SKU1", qty: 2}]});

    if (request1.status !== 201 || typeof request1.data?.ordenProduccionId !== "number") {
      throw new Error(`Fallo contrato generar OP (status=${request1.status}, body=${JSON.stringify(request1.data)})`);
    }

    const opId = request1.data.ordenProduccionId;
    const request2 = await client.post("/api/produccion/ordenes/planificar", {ordenProduccionId: opId, estacionId: 1, recetaVersionId: 1, porcionId: 1});

    if (request2.status !== 201 || typeof request2.data?.ordenProduccionId !== "number") {
      throw new Error(`Fallo contrato planificar OP (status=${request2.status}, body=${JSON.stringify(request2.data)})`);
    }
  });

  console.log("Pact generado en contract-tests/consumer/pacts/");
})().catch((e) => {
  console.error(e);
  process.exit(1);
});