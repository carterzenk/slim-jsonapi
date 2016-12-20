<?php

namespace CarterZenk\Tests\JsonApi\App;

use CarterZenk\JsonApi\App\App;
use CarterZenk\JsonApi\Exceptions\BadRequest;
use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Exceptions\ResourceNotExists;
use CarterZenk\JsonApi\Serializer\JsonApiSerializer;
use CarterZenk\JsonApi\Transformer\TypeTrait;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use WoohooLabs\Yin\JsonApi\Exception\ClientGeneratedIdNotSupported;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Exception\ResourceNotFound;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing;

class AppTest extends BaseTestCase
{
    use TypeTrait;

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
        $this->client->get('/contacts/1');
        $serializer = new JsonApiSerializer(JSON_PRETTY_PRINT);
        $responseString = $serializer->getBodyAsString($this->client->response);
        $this->assertTrue(is_string($responseString));
    }

    public function testGetContactSuccess()
    {
        $this->client->get('/contacts/1');
        $this->assertEquals(200, $this->client->response->getStatusCode());

        $document = $this->getResponseContentAsArray();
        $contact = Contact::find(1);

        // Check primary id.
        $this->assertEquals('1', $document['data']['id']);

        // Check primary type
        $this->assertEquals('contact', $document['data']['type']);

        // Check attributes
        $this->assertArrayHasKey('attributes', $document['data']);
        $this->assertEquals($contact->f_name, $document['data']['attributes']['f_name']);
        $this->assertEquals($contact->l_name, $document['data']['attributes']['l_name']);
        $this->assertEquals($contact->email, $document['data']['attributes']['email']);
        $this->assertEquals($contact->title, $document['data']['attributes']['title']);
        $this->assertEquals($contact->phone, $document['data']['attributes']['phone']);
        $this->assertEquals($contact->phone_cell, $document['data']['attributes']['phone_cell']);
        $this->assertEquals($contact->phone_office, $document['data']['attributes']['phone_office']);
        $this->assertEquals($contact->address, $document['data']['attributes']['address']);
        $this->assertEquals($contact->city, $document['data']['attributes']['city']);
        $this->assertEquals($contact->state, $document['data']['attributes']['state']);
        $this->assertEquals($contact->zip, $document['data']['attributes']['zip']);
        $this->assertEquals($contact->birthday, $document['data']['attributes']['birthday']);

        // Check relationships
        $this->assertEquals(2, count($document['data']['relationships']));
        $this->assertArrayHasKey('owner', $document['data']['relationships']);
        $this->assertArrayHasKey('assignee', $document['data']['relationships']);
        $this->assertArrayHasKey('data', $document['data']['relationships']['assignee']);
        $this->assertArrayHasKey('links', $document['data']['relationships']['assignee']);
        $this->assertArrayHasKey('links', $document['data']['relationships']['owner']);
        $this->assertEquals(2, count($document['data']['relationships']['owner']['links']));
        $this->assertArrayHasKey('self', $document['data']['relationships']['owner']['links']);
        $this->assertArrayHasKey('related', $document['data']['relationships']['owner']['links']);
    }

    public function testGetContactRelationshipSuccess()
    {
        $this->client->get('/contacts/1/relationships/assignee');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testGetContactRelationshipWithIncludeSuccess()
    {
        $this->client->get('/contacts/1/relationships/assignee?include=owned-contacts');
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

    public function testGetHasManyRelationshipWithIncludedSuccess()
    {
        $this->client->get('/users/1/relationships/owned-contacts?include=owned-contacts,owned-contacts.assignee');
        $this->assertEquals(200, $this->client->response->getStatusCode());
    }

    public function testCreateContactsSuccess()
    {
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
                            'id' => '1'
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
    }

    public function testUpdateContactsSuccess()
    {
        $this->client->patch('/contacts/1', [
            'data' => [
                'type' => 'contact',
                'id' => '1',
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
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertEquals(202, $this->client->response->getStatusCode());

        $contact = Contact::find(1);

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
        $this->assertEquals(1, $contact->assignee->id);
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

        $this->dumpResponse();

        $this->assertEquals(201, $this->client->response->getStatusCode());

        $user = User::all()->last();

        $assignedContacts = $user->assignedContacts;

        for ($i = 1; $i <= 5; $i++) {
            $this->assertNotNull($assignedContacts->find($i));
        }
    }

    public function testUpdateRelationshipSuccess()
    {
        $this->client->patch('/contacts/1/relationships/assignee', [
            'data' => [
                'type' => 'user',
                'id' => '2'
            ]
        ]);

        $this->assertEquals(202, $this->client->response->getStatusCode());

        $contact = Contact::find(1);

        $this->assertEquals(2, $contact->assignee->id);
    }

    public function testDeleteContactSuccess()
    {
        $this->client->delete('/contacts/30');
        $this->assertEquals(204, $this->client->response->getStatusCode());

        $this->expectException(ModelNotFoundException::class);
        Contact::findOrFail(30);
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
        $this->expectException(ResourceNotExists::class);
        $this->client->get('/contacts/5948');
    }

    public function testUpdateInvalidRelationshipError()
    {
        $this->expectException(RelationshipNotExists::class);
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
