<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProductsTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const API_TOKEN = '8637a8b9692309411959485b0fe23ae534ee3769a1aeeace0f6dbc62eb7287454980b75b603a6d04a8ae60464ef1fbbea9550ad528b3a3b4cb9feeed';

    private HttpClientInterface $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = $this->createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();

        $user = new User();
        $user->setEmail('test@test.com');
        $user->setPassword('test');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $apiToken = new ApiToken();
        $apiToken->setToken(self::API_TOKEN);
        $apiToken->setUser($user);
        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testGetCollection(): void
    {
        $response = $this->client->request('GET', '/api/products', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame(
            'content-type', 'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@context' => '/api/contexts/Product',
            '@id' => '/api/products',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 100,
            'hydra:view'       => [
                '@id'         => '/api/products?page=1',
                '@type'       => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:next'  => '/api/products?page=2',
            ],
        ]);

        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testPagination(): void
    {
        $this->client->request('GET', '/api/products?page=2', [
            'headers' => ['x-api-token' => self::API_TOKEN]
        ]);

        $this->assertJsonContains([
            'hydra:view' => [
                '@id' => '/api/products?page=2',
                '@type'  => 'hydra:PartialCollectionView',
                'hydra:first' => '/api/products?page=1',
                'hydra:last' => '/api/products?page=20',
                'hydra:previous' => '/api/products?page=1',
                'hydra:next' => '/api/products?page=3',
            ],
        ]);
    }

//    /**
//     * @throws TransportExceptionInterface
//     * @throws ServerExceptionInterface
//     * @throws RedirectionExceptionInterface
//     * @throws DecodingExceptionInterface
//     * @throws ClientExceptionInterface
//     */
//    public function testCreateProduct(): void
//    {
//        /**
//         * Получаем список производителей
//         */
//        $response = $this->client->request('GET', '/api/manufacturers', [
//            'headers' => [
//                'x-api-token' => self::API_TOKEN
//            ]
//        ]);
//        /**
//         * Проверяем успешный ответ на запрос списка производителей
//         */
//        $this->assertResponseStatusCodeSame(200);
//        /**
//         * Получаем данные из ответа
//         */
//        $manufacturers = $response->toArray();
//        $this->assertArrayHasKey('hydra:member', $manufacturers);
//        $this->assertNotEmpty($manufacturers['hydra:member']);
//        /**
//         * Получаем ID первого производителя
//         */
//        $manufacturerId = $manufacturers['hydra:member'][0]['@id'];
//
//        $response = $this->client->request('POST', '/api/products', [
//            'headers' => [
//                'Content-Type' => 'application/ld+json',
//                'x-api-token' => self::API_TOKEN
//            ],
//            'json' => [
//                'mpn' => '1234',
//                'name' => 'A Test Product',
//                'description' => 'A Test Description',
//                'issueDate' => '1985-07-31 00:00:00',
//                'manufacturer' => $manufacturerId,
//            ]
//        ]);
//
//
//        $this->assertResponseStatusCodeSame(201);
//        $this->assertResponseHeaderSame(
//            'content-type', 'application/ld+json; charset=utf-8'
//        );
//
//        $this->assertJsonContains([
//            'mpn' => '1234',
//            'name' => 'A Test Product',
//            'description' => 'A Test Description',
//            'issueDate' => '1985-07-31T00:00:00+00:00'
//        ]);
//    }

//    /**
//     * @throws RedirectionExceptionInterface
//     * @throws DecodingExceptionInterface
//     * @throws ClientExceptionInterface
//     * @throws TransportExceptionInterface
//     * @throws ServerExceptionInterface
//     */
//    public function testUpdateProduct(): void
//    {
//        /**
//         * Получаем список продуктов
//         */
//        $response = $this->client->request('GET', '/api/products', [
//            'headers' => [
//                'Content-Type' => 'application/ld+json',
//                'x-api-token' => self::API_TOKEN
//            ]
//        ]);
//        /**
//         * Проверяем успешный ответ на запрос списка продуктов
//         */
//        $this->assertResponseStatusCodeSame(200);
//        /**
//         * Получаем данные из ответа
//         */
//        $products = $response->toArray();
//        $this->assertArrayHasKey('hydra:member', $products);
//        $this->assertNotEmpty($products['hydra:member']);
//        /**
//         * Получаем id, mpn, manufacturer первого продукта
//         */
//        $product = $products['hydra:member'][0];
//        $productId = $product['@id'];
//        $mpn = $product['mpn'];
//        $manufacturerId = $product['manufacturer']['@id'];
//
//        $this->client->request('PUT', $productId, [
//            'headers' => [
//                'Content-Type' => 'application/ld+json',
//                'x-api-token' => self::API_TOKEN,
//            ],
//            'json' => [
//                'mpn' => $mpn,
//                'name' => $product['name'],
//                'description' => 'An updated description',
//                'issueDate'  => $product['issueDate'],
//                'manufacturer' => $manufacturerId,
//            ]
//        ]);
//
//        $this->assertResponseIsSuccessful();
//
//        $this->assertJsonContains([
//            '@id' => $productId,
//            'description' => 'An updated description',
//        ]);
//    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function testCreateInvalidProduct(): void
    {
        $this->client->request('POST', '/api/products', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'x-api-token' => self::API_TOKEN
            ],
            'json' => [
                'mpn'          => '1234',
                'name'         => 'A Test Product',
                'description'  => 'A Test Description',
                'issueDate'    => '1985-07-31',
                'manufacturer' => null,
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/problem+json; charset=utf-8');

        $this->assertJsonContains([
            '@id' => '/api/validation_errors/ad32d13f-c3d4-423b-909a-857b961eb720',
            '@type' => 'ConstraintViolationList',
            'hydra:title'  => 'An error occurred',
            'hydra:description' => 'manufacturer: This value should not be null.',
        ]);
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testInvalidToken(): void
    {
        /**
         * Получаем список продуктов
         */
        $response = $this->client->request('GET', '/api/products', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'x-api-token' => self::API_TOKEN
            ]
        ]);
        /**
         * Проверяем успешный ответ на запрос списка продуктов
         */
        $this->assertResponseStatusCodeSame(200);
        /**
         * Получаем данные из ответа
         */
        $products = $response->toArray();
        $this->assertArrayHasKey('hydra:member', $products);
        $this->assertNotEmpty($products['hydra:member']);
        /**
         * Получаем id, mpn, manufacturer первого продукта
         */
        $product = $products['hydra:member'][0];
        $productId = $product['@id'];
        $mpn = $product['mpn'];
        $manufacturerId = $product['manufacturer']['@id'];

        $this->client->request('PUT', $productId, [
            'headers' => [
                'Content-Type' => 'application/ld+json',
                'x-api-token' => 'fake-token',
            ],
            'json' => [
                'mpn' => $mpn,
                'name' => $product['name'],
                'description' => 'An updated description',
                'issueDate'  => $product['issueDate'],
                'manufacturer' => $manufacturerId,
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);

        $this->assertJsonContains([
            'message' => 'Invalid credentials.',
        ]);
    }
}
