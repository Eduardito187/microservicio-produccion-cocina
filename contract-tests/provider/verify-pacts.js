const path = require("path");
const fs = require("fs");
const axios = require("axios");
const { Verifier } = require("@pact-foundation/pact");

(async () => {
    process.env.PACT_BYPASS_AUTH = "true";
    process.env.OUTBOX_SKIP_DISPATCH = "true";

    const pactDir = path.resolve(__dirname, "..", "consumer", "pacts");
    const providerBaseUrl = process.env.PROVIDER_BASE_URL || "http://laravel_app";
    const pactUrls = fs.readdirSync(pactDir).filter(f => f.endsWith(".json")).map(f => path.join(pactDir, f));
    const setupUrl = `${providerBaseUrl}/api/_pact/setup`;
    const opts = {
        provider: "microservicio-produccion-cocina",
        providerBaseUrl,
        pactUrls,
        customProviderHeaders: {
            "X-Pact-Request": "true"
        },
        publishVerificationResult: false,
        providerVersion: process.env.PROVIDER_VERSION || "dev-local",
        stateHandlers: {
            "product PIZZA-PEP exists": async () => {
                await axios.post(
                    setupUrl,
                    {state: "product PIZZA-PEP exists"},
                    {timeout: 15000, headers: {"X-Pact-Request": "true"}}
                );
                return Promise.resolve();
            },
            "orden produccion 1 exists and porcion 1 exists": async () => {
                await axios.post(
                    setupUrl,
                    {state: "orden produccion 1 exists and porcion 1 exists"},
                    {timeout: 15000, headers: {"X-Pact-Request": "true"}}
                );
                return Promise.resolve();
            }
        }
    };

    const output = await new Verifier(opts).verifyProvider();
    console.log(output);
})().catch((e) => {
    console.error(e);
    process.exit(1);
});
