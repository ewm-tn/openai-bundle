<?php

declare(strict_types=1);

namespace EwmOpenaiBundle\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenAIMediaDescriptionGenerator
{
    private const PROMPT_EN = <<<'PROMPT'
Generate a concise 2-line marketing description for alt text that includes:
1. Visual elements - Describe colors, key objects, and composition
2. Emotional tone - Convey the mood (e.g., "inviting", "dynamic")
3. Context - Note any industry relevance if apparent

Example: "Sunlit modern kitchen featuring marble countertops and stainless steel appliances, conveying luxury and practicality for home cooking enthusiasts."

Keep descriptions:
- Factual yet engaging
- Free of promotional language
- Under 200 characters
PROMPT;
    private const PROMPT_FR = <<<'PROMPT'
Générez une description marketing en 2 lignes pour le texte alternatif incluant :
1. Éléments visuels - Décrivez couleurs, objets principaux et composition
2. Ton émotionnel - Exprimez l'ambiance (ex: "accueillant", "dynamique")
3. Contexte - Mentionnez le secteur si pertinent (immobilier, santé, etc.)

Exemple : "Cuisine moderne baignée de lumière avec plan de travail en marbre et électroménager inox, alliant luxe et fonctionnalité pour les amateurs de cuisine."

Caractéristiques requises :
- Descriptions factuelles mais engageantes
- Sans langage promotionnel
- Moins de 200 caractères
PROMPT;
    private const PROMPT_DE = <<<'PROMPT'
Erstellen Sie eine 2-zeilige Marketing-Beschreibung für Alt-Text mit:
1. Visuellen Elementen - Beschreiben Sie Farben, Hauptobjekte und Komposition
2. Emotionalem Ton - Vermitteln Sie die Stimmung (z.B. "einladend", "dynamisch")
3. Kontext - Nennen Sie die Branche, falls erkennbar (Immobilien, Pharma, etc.)

Beispiel: "Sonnenbeschienene moderne Küche mit Marmor-Arbeitsplatte und Edelstahlgeräten, die Luxus und Funktionalität für Hobbyköche vereint."

Anforderungen:
- Sachlich aber ansprechend
- Ohne Werbesprache
- Maximal 200 Zeichen
PROMPT;
    private const PROMPT_ES = <<<'PROMPT'
Genera una descripción comercial de 2 líneas para texto alternativo que incluya:
1. Elementos visuales - Colores, objetos principales y composición
2. Tono emocional - Ambiente (ej: "acogedor", "innovador")
3. Contexto - Sector si es relevante (bienes raíces, farmacia, etc.)

Ejemplo: "Cocina moderna iluminada con encimera de mármol y electrodomésticos de acero inoxidable, que combina lujo y funcionalidad para chefs caseros."

Requisitos:
- Descriptivo pero conciso
- Sin lenguaje promocional
- Máximo 200 caracteres
PROMPT;
    private const PROMPT_IT = <<<'PROMPT'
Genera una descrizione commerciale di 2 righe per il testo alternativo includendo:
1. Elementi visivi - Colori, oggetti principali e composizione
2. Atmosfera - Trasmettere l'emozione (es: "accogliente", "innovativo")
3. Contesto - Settore se rilevante (immobiliare, farmaceutico, ecc.)

Esempio: "Cucina moderna illuminata dal sole con piano di lavoro in marmo ed elettrodomestici in acciaio inox, che unisce lusso e funzionalità per gli chef a casa."

Requisiti:
- Descrittivo ma conciso
- Senza linguaggio promozionale
- Massimo 200 caratteri
PROMPT;
    private const MODEL = 'gpt-4-turbo';
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct()
    {
        $this->client = HttpClient::create();
        $this->apiKey = $_ENV['OPEN_API_KEY'] ?? '';
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function generateDescription(string $imageUrl, string $locale): ?string
    {
        try {
            $prompt = 'fr' === $locale ? self::PROMPT_FR : self::PROMPT_EN;
            $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => self::MODEL,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $prompt],
                                ['type' => 'image_url', 'image_url' => [
                                    'url' => $imageUrl,
                                ]],
                            ],
                        ],
                    ],
                    'max_tokens' => 300,
                ],
            ]);

            return $response->toArray()['choices'][0]['message']['content'];
        } catch (ClientExceptionInterface|ServerExceptionInterface|RedirectionExceptionInterface|DecodingExceptionInterface|TransportExceptionInterface|\Exception|\Error  $e) {
            LoggerService::logMessage('Error for ' . $imageUrl . ': ' . $e->getMessage());

            return null;
        }
    }
}
