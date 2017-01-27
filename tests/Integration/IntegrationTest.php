<?php

namespace CarterZenk\Tests\JsonApi\Integration;

use CarterZenk\JsonApi\App\App;
use CarterZenk\JsonApi\Exceptions\Forbidden;
use CarterZenk\JsonApi\Exceptions\ResourceNotFound;
use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use CarterZenk\JsonApi\Transformer\TypeTrait;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laracasts\TestDummy\Factory;
use There4\Slim\Test\WebTestCase;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing;

class IntegrationTest extends WebTestCase
{
    use TypeTrait;

    public function getSlimInstance()
    {
        return SlimInstance::getInstance();
    }

    private function getResponseContentAsArray()
    {
        $body = $this->client->response->getBody();
        $body->rewind();
        return json_decode($body->getContents(), true);
    }

    private function dumpResponse()
    {
        $body = $this->client->response->getBody();
        $body->rewind();
        echo $body->getContents();
    }

    public function testGetResponseAsString()
    {
        $this->client->get('/contacts/1');
        $serializer = new JsonApiSerializer(JSON_PRETTY_PRINT);
        $responseString = $serializer->getBodyAsString($this->client->response);
        $this->assertTrue(is_string($responseString));
    }

    public function testGetContactSuccess()
    {
        $contact = Factory::create(Contact::class);

        $this->client->get('/contacts/'.$contact->id);
        $this->assertEquals(200, $this->client->response->getStatusCode());

        $document = $this->getResponseContentAsArray();
        $contact = Contact::find($contact->id);

        // Check primary id
        $this->assertEquals($contact->id, $document['data']['id']);

        // Check primary type
        $this->assertEquals('contact', $document['data']['type']);

        // Check attributes
        $this->assertArrayHasKey('attributes', $document['data']);
        $attributes = $document['data']['attributes'];

        // Check attributes
        $this->assertEquals([
            'f_name' => $contact->f_name,
            'l_name' => $contact->l_name,
            'email' => $contact->email,
            'title' => $contact->title,
            'phone' => $contact->phone,
            'phone_cell' => $contact->phone_cell,
            'phone_office' => $contact->phone_office,
            'address' => $contact->address,
            'city' => $contact->city,
            'state' => $contact->state,
            'zip' => $contact->zip,
            'birthday' => $contact->birthday
        ], $attributes);

        // Check relationships
        $this->assertArrayHasKey('relationships', $document['data']);
        $relationships = $document['data']['relationships'];
        $this->assertEquals(3, count($relationships));
        $this->assertArrayHasKey('owner', $relationships);
        $this->assertArrayHasKey('assignee', $relationships);
        $this->assertArrayHasKey('active-user', $relationships);
        $this->assertArrayHasKey('data', $relationships['assignee']);
        $this->assertArrayHasKey('links', $relationships['assignee']);
        $this->assertArrayHasKey('links', $relationships['owner']);
        $this->assertArrayHasKey('links', $relationships['active-user']);
        $this->assertEquals(2, count($relationships['owner']['links']));
        $this->assertArrayHasKey('self', $relationships['owner']['links']);
        $this->assertArrayHasKey('related', $relationships['owner']['links']);
    }

    public function testGetContactRelationshipSuccess()
    {
        $this->client->get('/contacts/1/relationships/assignee');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactsSuccess()
    {
        $this->client->get('/contacts');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetUsersSuccess()
    {
        $this->client->get('/users');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactsFiltersSuccess()
    {
        $this->client->get('/contacts?filter[owner_id]=1');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactsSortingSuccess()
    {
        $this->client->get('/contacts?sort=f_name');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetUserSuccess()
    {
        $this->client->get('/users/1');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetLeadsPagination()
    {
        $this->client->get('/contacts?page[size]=3&page[number]=2');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetLeadsIncludeAssignee()
    {
        $this->client->get('/contacts?include=assignee');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetLeadsIncludeAssigneeAndOwnedContacts()
    {
        $this->client->get('/contacts?include=assignee,owner');
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

    public function testCreateContactsSuccess()
    {
        $user = Factory::create(User::class);

        $this->client->post('/contacts', [
            'data' => [
                'type' => 'contact',
                'attributes' => [
                    'f_name' => 'John',
                    'l_name' => 'Doe',
                    'email' => 'john@example.com',
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
                            'id' => $user->id
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals(201, $this->client->response->getStatusCode());

        $contact = Contact::all()->last();

        $this->assertEquals('John', $contact->f_name);
        $this->assertEquals('Doe', $contact->l_name);
        $this->assertEquals('john@example.com', $contact->email);
        $this->assertEquals('Mr.', $contact->title);
        $this->assertEquals('888-888-8888', $contact->phone);
        $this->assertEquals('999-999-9999', $contact->phone_cell);
        $this->assertEquals('777-777-7777', $contact->phone_office);
        $this->assertEquals('123 Main St', $contact->address);
        $this->assertEquals('Exampletown', $contact->city);
        $this->assertEquals('MN', $contact->state);
        $this->assertEquals('33212', $contact->zip);
        $this->assertEquals('1990-07-01', $contact->birthday);

        $this->assertEquals($user->id, $contact->assignee->id);
    }

    public function testUpdateContactsSuccess()
    {
        $contact = Factory::create(Contact::class);
        $user = Factory::create(User::class);

        $this->client->patch('/contacts/'.$contact->id, [
            'data' => [
                'type' => 'contact',
                'id' => $contact->id,
                'attributes' => [
                    'f_name' => 'John',
                    'l_name' => 'Doe',
                    'email' => 'john@example.com',
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
                            'id' => $user->id
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals(202, $this->client->response->getStatusCode());

        $contact = Contact::find($contact->id);

        $this->assertEquals('John', $contact->f_name);
        $this->assertEquals('Doe', $contact->l_name);
        $this->assertEquals('john@example.com', $contact->email);
        $this->assertEquals('Mr.', $contact->title);
        $this->assertEquals('888-888-8888', $contact->phone);
        $this->assertEquals('999-999-9999', $contact->phone_cell);
        $this->assertEquals('777-777-7777', $contact->phone_office);
        $this->assertEquals('123 Main St', $contact->address);
        $this->assertEquals('Exampletown', $contact->city);
        $this->assertEquals('MN', $contact->state);
        $this->assertEquals('33212', $contact->zip);
        $this->assertEquals('1990-07-01', $contact->birthday);
        $this->assertEquals($user->id, $contact->assignee->id);
    }

    public function testHydrateToManyRelationshipForCreate()
    {
        $this->client->post('/users', [
            'data' => [
                'type' => 'user',
                'attributes' => [
                    'f_name' => 'John',
                    'l_name' => 'Doe',
                    'email' => 'john@example.com',
                    'phone' => '888-888-8888',
                    'phone_cell' => '999-999-9999',
                    'phone_office' => '777-777-7777',
                    'address' => '123 Main St',
                    'city' => 'Exampletown',
                    'state' => 'MN'
                ],
                'relationships' => [
                    'assigned-contacts' => [
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

        $this->assertEquals(201, $this->client->response->getStatusCode());

        $user = User::all()->last();

        $assignedContacts = $user->assignedContacts;

        for ($i = 1; $i <= 5; $i++) {
            $this->assertNotNull($assignedContacts->find($i));
        }
    }

    public function testUpdateRelationshipSuccess()
    {
        $contact = Factory::create(Contact::class);
        $user = Factory::create(User::class);

        $this->client->patch('/contacts/'.$contact->id.'/relationships/assignee', [
            'data' => [
                'type' => 'user',
                'id' => $user->id
            ]
        ]);

        $this->assertEquals(202, $this->client->response->getStatusCode());

        $contact = Contact::find($contact->id);

        $this->assertEquals($user->id, $contact->assignee->id);
    }

    public function testDeleteContactSuccess()
    {
        $contact = Factory::create(Contact::class);

        $this->client->delete('/contacts/'.$contact->id);
        $this->assertEquals(204, $this->client->response->getStatusCode());

        $this->expectException(ModelNotFoundException::class);
        Contact::findOrFail($contact->id);
    }

    public function testMissingTypeError()
    {
        $this->expectException(ResourceTypeMissing::class);
        $this->client->patch('/contacts/1', [
            'data' => [
                'id' => '1'
            ]
        ]);
    }

    public function testErrorResponse()
    {
        $this->expectException(ResourceNotFound::class);
        $this->client->get('/contacts/594865876');
    }

    public function testUpdateInvalidRelationshipError()
    {
        $this->expectException(Forbidden::class);
        $this->client->patch('/contacts/1/relationships/some-relationship', [
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
}
