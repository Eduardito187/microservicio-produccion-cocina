<?php

namespace App\Application\Integration\Events;

use App\Application\Integration\Events\Support\Payload;

class RecetaActualizadaEvent
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $nombre,
        public readonly ?array $nutrientes,
        public readonly ?array $ingredientes,
        public readonly ?int $version
    ) {
    }

    /**
     * @param array $payload
     * @return self
     */
    public static function fromPayload(array $payload): self
    {
        $p = new Payload($payload);

        return new self(
            $p->getString(['id', 'recetaVersionId', 'receta_version_id', 'recetaId', 'receta_id'], null, true),
            $p->getString(['nombre', 'name']),
            $p->getArray(['nutrientes', 'nutrients']),
            $p->getArray(['ingredientes', 'ingredients']),
            $p->getInt(['version', 'versionNumber'])
        );
    }
}
