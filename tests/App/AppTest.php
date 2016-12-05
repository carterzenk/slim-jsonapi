<?php

namespace CarterZenk\Tests\JsonApi\App;

use CarterZenk\JsonApi\App\App;
use CarterZenk\JsonApi\Exceptions\BadRequest;
use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Exceptions\ResourceNotExists;
use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdNotSupported;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Exception\ResourceNotFound;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing;

class AppTest extends BaseTestCase
{
    public function testClassExists()
    {
        $this->assertEquals(true, class_exists(App::class));
    }

    public function testInit()
    {
        $this->assertInstanceOf(App::class, $this->app);
    }

    public function testGetResponseAsString()
    {
        $this->client->get('/leads/1');
        $serializer = new JsonApiSerializer(JSON_PRETTY_PRINT);
        $responseString = $serializer->getBodyAsString($this->client->response);
        $this->assertTrue(is_string($responseString));
    }

    public function testGetContactSuccess()
    {
        $this->client->get('/leads/1');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactRelationshipSuccess()
    {
        $this->client->get('/leads/1/relationships/assignee');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactRelationshipWithIncludeSuccess()
    {
        $this->client->get('/leads/1/relationships/assignee?include=owned-contacts');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactsSuccess()
    {
        $this->client->get('/leads');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetUsersSuccess()
    {
        $this->client->get('/users');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactsFiltersSuccess()
    {
        $this->client->get('/leads?filter[owner_id]=1');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactsSortingSuccess()
    {
        $this->client->get('/leads?sort=f_name');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetUserSuccess()
    {
        $this->client->get('/users/1');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetLeadsPagination()
    {
        $this->client->get('/leads?page[size]=3&page[number]=2');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetLeadsIncludeAssignee()
    {
        $this->client->get('/leads?include=assignee');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetLeadsIncludeAssigneeAndOwnedContacts()
    {
        $this->client->get('/leads?include=assignee,owner');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetUserIncludeOwnedContacts()
    {
        $this->client->get('/users/1?include=owned-contacts');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetHasManyRelationshipSuccess()
    {
        $this->client->get('/users/1/relationships/owned-contacts');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetHasManyRelationshipWithIncludedSuccess()
    {
        $this->client->get('/users/1/relationships/owned-contacts?include=owned-contacts,owned-contacts.assignee');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testCreateContactsSuccess()
    {
        $this->client->post('/leads', [
            'data' => [
                'type' => 'lead',
                'attributes' => [
                    'f_name' => 'John',
                    'l_name' => 'Doe',
                    'email' => 'john@example.ecom',
                    'title' => 'Mr.',
                    'phone' => '888-888-8888',
                    'phone_cell' => '999-999-9999',
                    'phone_office' => '777-777-7777',
                    'address' => '123 Main St',
                    'city' => 'Exampletown',
                    'state' => 'MN',
                    'zip' => '33212',
                    'birthday' => '1990-07-01',
                ],
                'relationships' => [
                    'assignee' => [
                        'data' => [
                            'type' => 'user',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals(201, $this->client->response->getStatusCode());
    }

    public function testUpdateContactsSuccess()
    {
        $this->client->patch('/leads/1', [
            'data' => [
                'type' => 'lead',
                'id' => '1',
                'attributes' => [
                    'f_name' => 'John',
                    'l_name' => 'Doe',
                    'email' => 'john@example.ecom',
                    'title' => 'Mr.',
                    'phone' => '888-888-8888',
                    'phone_cell' => '999-999-9999',
                    'phone_office' => '777-777-7777',
                    'address' => '123 Main St',
                    'city' => 'Exampletown',
                    'state' => 'MN',
                    'zip' => '33212',
                    'birthday' => '1990-07-01',
                ],
                'relationships' => [
                    'assignee' => [
                        'data' => [
                            'type' => 'user',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals(202, $this->client->response->getStatusCode());
    }

    public function testHydrateToManyRelationship()
    {
        $this->client->patch('/users/1', [
            'data' => [
                'type' => 'user',
                'id' => '1',
                'attributes' => [
                    'f_name' => 'John',
                    'l_name' => 'Doe',
                    'email' => 'john@example.ecom',
                    'title' => 'Mr.',
                    'phone' => '888-888-8888',
                    'phone_cell' => '999-999-9999',
                    'phone_office' => '777-777-7777',
                    'address' => '123 Main St',
                    'city' => 'Exampletown',
                    'state' => 'MN'
                ],
                'relationships' => [
                    'owned-contacts' => [
                        'data' => [
                            [
                                'type' => 'lead',
                                'id' => '1'
                            ],
                            [
                                'type' => 'lead',
                                'id' => '2'
                            ],
                            [
                                'type' => 'lead',
                                'id' => '3'
                            ],
                            [
                                'type' => 'lead',
                                'id' => '4'
                            ],
                            [
                                'type' => 'lead',
                                'id' => '5'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals(202, $this->client->response->getStatusCode());
    }

    public function testUpdateRelationshipSuccess()
    {
        $this->client->patch('/leads/1/relationships/assignee', [
            'data' => [
                'type' => 'user',
                'id' => '2'
            ]
        ]);

        $this->assertEquals(202, $this->client->response->getStatusCode());
    }

    public function testDeleteContactSuccess()
    {
        $this->client->delete('/leads/30');
        $this->assertEquals(204, $this->client->response->getStatusCode());
    }

    public function testMissingTypeError()
    {
        $this->expectException(ResourceTypeMissing::class);
        $this->client->patch('/leads/1', [
            'data' => [
                'id' => '1'
            ]
        ]);
    }

    public function testClientGeneratedIdError()
    {
        $this->expectException(ClientGeneratedIdNotSupported::class);
        $this->client->post('/leads', [
            'data' => [
                'type' => 'lead',
                'id' => '1'
            ]
        ]);
    }

    public function testErrorResponse()
    {
        $this->expectException(ResourceNotExists::class);
        $this->client->get('/leads/5948');
    }

    public function testUpdateInvalidRelationshipError()
    {
        $this->expectException(RelationshipNotExists::class);
        $this->client->patch('/leads/1/relationships/some-relationship', [
            'data' => [
                'type' => 'user',
                'id' => '2'
            ]
        ]);
    }

    public function testGetInvalidRelationshipError()
    {
        $this->expectException(RelationshipNotExists::class);
        $this->client->get('/users/1/relationships/someinvalidrelationship');
    }

    public function testBadRequestError()
    {
        $this->expectException(BadRequest::class);
        $this->client->post('/leads', [
            'data' => [
                'type' => 'lead',
                'attributes' => [
                    'invalid' => '33212837492048294839403988457575'
                ]
            ]
        ]);
    }

    public function testHydrateToManyRelationshipWithInvalidIdError()
    {
        $this->expectException(RelatedResourceNotFound::class);
        $this->client->patch('/users/1', [
            'data' => [
                'type' => 'user',
                'id' => '1',
                'relationships' => [
                    'owned-contacts' => [
                        'data' => [
                            [
                                'type' => 'lead',
                                'id' => '1'
                            ],
                            [
                                'type' => 'lead',
                                'id' => '700'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}
