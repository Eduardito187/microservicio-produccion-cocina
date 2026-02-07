const path = require("path");
const axios = require("axios");
const { PactV3, MatchersV3 } = require("@pact-foundation/pact");
const { uuid } = MatchersV3;

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
  const ordenProduccionId = "e28e9cc2-5225-40c0-b88b-2341f96d76a3";
  const estacionId = "9b7b5fbe-6b65-4d1d-8fdd-52f143b2552f";
  const recetaVersionId = "d2c3b4a5-1f3c-4b2f-9f54-7ab02d1b33c9";
  const porcionId = "f7a1e0b2-2c4d-4c0a-9b8e-0a4b2f9d8f7a";

  // 1) Generar OP
  provider.given("product PIZZA-PEP exists").uponReceiving("POST generar OP")
    .withRequest({
      method: "POST",
      path: "/api/produccion/ordenes/generar",
      headers: JSON_HEADERS,
      body: {fecha: "2025-12-19", sucursalId: "SCZ", items: [{sku: "PIZZA-PEP", qty: 1}]}
    })
    .willRespondWith({
      status: 201,
      headers: {"Content-Type": "application/json"},
      body: {ordenProduccionId: uuid()}
    });

  // 2) Planificar OP
  provider.given("orden produccion 1 exists and porcion 1 exists", {
    ordenProduccionId,
    estacionId,
    recetaVersionId,
    porcionId
  }).uponReceiving("POST planificar OP")
    .withRequest({
      method: "POST",
      path: "/api/produccion/ordenes/planificar",
      headers: JSON_HEADERS,
      body: {
        ordenProduccionId: uuid(ordenProduccionId),
        estacionId: uuid(estacionId),
        recetaVersionId: uuid(recetaVersionId),
        porcionId: uuid(porcionId)
      }
    })
    .willRespondWith({
      status: 201,
      headers: {"Content-Type": "application/json"},
      body: {ordenProduccionId: uuid(ordenProduccionId)}
    });

  await provider.executeTest(async (mockServer) => {
    const client = axios.create({baseURL: mockServer.url, validateStatus: () => true, headers: JSON_HEADERS});
    const request1 = await client.post("/api/produccion/ordenes/generar", {fecha: "2025-12-19", sucursalId: "SCZ", items: [{sku: "PIZZA-PEP", qty: 1}]});

    if (request1.status !== 201 || typeof request1.data?.ordenProduccionId !== "string") {
      throw new Error(`Fallo contrato generar OP (status=${request1.status}, body=${JSON.stringify(request1.data)})`);
    }

    const opId = request1.data.ordenProduccionId;
    const request2 = await client.post("/api/produccion/ordenes/planificar", {
      ordenProduccionId: opId,
      estacionId,
      recetaVersionId,
      porcionId
    });

    if (request2.status !== 201 || typeof request2.data?.ordenProduccionId !== "string") {
      throw new Error(`Fallo contrato planificar OP (status=${request2.status}, body=${JSON.stringify(request2.data)})`);
    }
  });

  console.log("Pact generado en contract-tests/consumer/pacts/");
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
