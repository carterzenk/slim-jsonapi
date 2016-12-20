<?php

namespace CarterZenk\Tests\JsonApi\Hydrator;

use CarterZenk\JsonApi\Exceptions\AttributeUpdateNotAllowed;
use CarterZenk\JsonApi\Exceptions\MethodNotAllowed;
use CarterZenk\JsonApi\Exceptions\RelatedResourceNotFound;
use CarterZenk\JsonApi\Exceptions\RelationshipUpdateNotAllowed;
use CarterZenk\JsonApi\Hydrator\ModelHydrator;
use CarterZenk\JsonApi\Hydrator\Relationship\Factory\RelationshipHydratorFactory;
use CarterZenk\Tests\JsonApi\BaseTestCase;
use CarterZenk\Tests\JsonApi\Model\Contact;
use CarterZenk\Tests\JsonApi\Model\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Uri;
use WoohooLabs\Yin\JsonApi\Exception\DataMemberMissing;
use WoohooLabs\Yin\JsonApi\Exception\DefaultExceptionFactory;
use WoohooLabs\Yin\JsonApi\Exception\FullReplacementProhibited;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipNotExists;
use WoohooLabs\Yin\JsonApi\Exception\RelationshipTypeInappropriate;
use WoohooLabs\Yin\JsonApi\Exception\RemovalProhibited;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeMissing;
use WoohooLabs\Yin\JsonApi\Exception\ResourceTypeUnacceptable;
use WoohooLabs\Yin\JsonApi\Request\RequestInterface;

class HydratorTest extends BaseTestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists(ModelHydrator::class));
    }

    public function testNoDataThrowsException()
    {
        $request = $this->getRequest(['error'], 'POST');
        $this->expectException(DataMemberMissing::class);
        $this->hydrate(new Contact(), $request);
    }

    public function testTypeMissingThrowsException()
    {
        $data = [
            'data' => [
                'attributes' => [
                    'f_name' => 'John'
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $this->expectException(ResourceTypeMissing::class);
        $this->hydrate(new Contact(), $request);
    }

    public function testInvalidTypeThrowsException()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'attributes' => [
                    'f_name' => 'John'
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $this->expectException(ResourceTypeUnacceptable::class);
        $this->hydrate(new Contact(), $request);
    }

    public function testHydrateAttributes()
    {
        $data = [
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
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $contact = $this->hydrate(new Contact(), $request);

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

    public function testHydrateNotFillableAttributeThrowsException()
    {
        $data = [
            'data' => [
                'type' => 'contact',
                'attributes' => [
                    'owner_id' => '2'
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');

        $this->expectException(AttributeUpdateNotAllowed::class);
        $this->hydrate(new Contact(), $request);
    }

    public function testHydrateResourceWithToOneRelationshipForUpdate()
    {
        $data = [
            'data' => [
                'type' => 'contact',
                'relationships' => [
                    'assignee' => [
                        'data' => [
                            'type' => 'user',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $contact = $this->hydrate(new Contact(), $request);

        $this->assertEquals(1, $contact->assignee->id);
    }

    public function testHydrateResourceWithInvalidRelationshipThrowsException()
    {
        $data = [
            'data' => [
                'type' => 'contact',
                'relationships' => [
                    'invalidrelationship' => [
                        'data' => [
                            'type' => 'user',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $this->expectException(RelationshipNotExists::class);
        $this->hydrate(new Contact(), $request);
    }

    public function testHydrateResourceWithGuardedRelationshipThrowsException()
    {
        $data = [
            'data' => [
                'type' => 'contact',
                'relationships' => [
                    'owner' => [
                        'data' => [
                            'type' => 'user',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $this->expectException(RelationshipUpdateNotAllowed::class);
        $this->hydrate(new Contact(), $request);
    }

    public function testHydrateHasOneRelationshipForCreate()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'active-contact' => [
                        'data' => [
                            'type' => 'contact',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $user = $this->hydrate(new User(), $request);
        $this->assertEquals(1, $user->activeContact->id);
    }

    public function testHydrateHasOneRelationshipForCreateWithInvalidId()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'active-contact' => [
                        'data' => [
                            'type' => 'contact',
                            'id' => '9999'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $this->expectException(RelatedResourceNotFound::class);
        $this->hydrate(new User(), $request);
    }

    public function testHydrateEmptyHasOneRelationshipForUpdate()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'active-contact' => [
                        'data' => null
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $this->expectException(RemovalProhibited::class);
        $this->hydrate(User::find(1), $request);
    }

    public function testHydrateHasOneRelationshipForUpdateWithExisting()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'active-contact' => [
                        'data' => [
                            'type' => 'contact',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $this->expectException(RemovalProhibited::class);
        $this->hydrate(User::find(1), $request);
    }

    public function testHydrateHasOneRelationshipForDelete()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'id' => '1'
            ]
        ];

        $request = $this->getRequest($data, 'DELETE');
        $this->expectException(MethodNotAllowed::class);
        $this->hydrateRelationship(User::find(1), $request, 'active-contact');
    }

    public function testHydrateHasOneRelationshipWithInvalidType()
    {
        $data = [
            'data' => [
                [
                    'type' => 'user',
                    'id' => '1'
                ]
            ]
        ];

        $request = $this->getRequest($data, 'DELETE');
        $this->expectException(RelationshipTypeInappropriate::class);
        $this->hydrateRelationship(User::find(1), $request, 'active-contact');
    }

    public function testHydrateHasManyRelationshipWithInvalidType()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'assigned-contacts' => [
                        'data' => [
                            'type' => 'contact',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');

        $this->expectException(RelationshipTypeInappropriate::class);
        $this->hydrate(new User(), $request);
    }

    public function testHydrateHasManyRelationshipForUpdate()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'assigned-contacts' => [
                        'data' => [
                            [
                                'type' => 'contact',
                                'id' => '1'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');

        $this->expectException(FullReplacementProhibited::class);
        $this->hydrate(new User(), $request);
    }

    public function testHydrateHasManyRelationshipForCreate()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'assigned-contacts' => [
                        'data' => [
                            [
                                'type' => 'contact',
                                'id' => '1'
                            ],
                            [
                                'type' => 'contact',
                                'id' => '2'
                            ],
                            [
                                'type' => 'contact',
                                'id' => '3'
                            ],
                            [
                                'type' => 'contact',
                                'id' => '4'
                            ],
                            [
                                'type' => 'contact',
                                'id' => '5'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $user = $this->hydrate(new User(), $request);

        for ($i = 1; $i <= 5; $i++) {
            $this->assertNotNull($user->assignedContacts->find($i));
        }
    }

    public function testHydrateHasManyRelationshipForDelete()
    {
        $data = [
            'data' => [
                [
                    'type' => 'contact',
                    'id' => '1'
                ],
                [
                    'type' => 'contact',
                    'id' => '2'
                ]
            ]
        ];

        $request = $this->getRequest($data, 'DELETE');

        $this->expectException(RemovalProhibited::class);
        $this->hydrateRelationship(new User(), $request, 'assigned-contacts');
    }

    public function testHydrateEmptyHasManyRelationshipForCreate()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'assigned-contacts' => [
                        'data' => []
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $user = $this->hydrate(new User(), $request);

        $this->assertEmpty($user->assignedContacts);
    }

    public function testHydrateWithoutData()
    {
        $data = [
            'data' => [
                'type' => 'user',
                'relationships' => [
                    'assigned-contacts' => []
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $this->expectException(DataMemberMissing::class);
        $this->hydrate(new User(), $request);
    }

    public function testHydrateBelongsToRelationshipForCreate()
    {
        $data = [
            'data' => [
                'type' => 'contact',
                'relationships' => [
                    'assignee' => [
                        'data' => [
                            'type' => 'user',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $contact = $this->hydrate(new Contact(), $request);

        $this->assertEquals(1, $contact->assignee->id);
    }

    public function testHydrateEmptyBelongsToRelationshipForCreate()
    {
        $data = [
            'data' => [
                'type' => 'contact',
                'relationships' => [
                    'assignee' => [
                        'data' => null
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'POST');
        $contact = $this->hydrate(new Contact(), $request);

        $this->assertNull($contact->assignee);
    }

    public function testHydrateBelongsToRelationshipForUpdate()
    {
        $contact = new Contact();
        $contact->save();
        $contact = $contact->fresh();

        $data = [
            'data' => [
                'type' => 'contact',
                'id' => $contact->id,
                'relationships' => [
                    'assignee' => [
                        'data' => [
                            'type' => 'user',
                            'id' => '1'
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $contact = $this->hydrate($contact, $request);

        $this->assertEquals(1, $contact->assignee->id);
    }

    public function testHydrateNullBelongsToRelationshipForUpdateRemovesRelated()
    {
        $contact = new Contact();
        $user = User::find(1);
        $contact->assignee()->associate($user);
        $this->assertEquals(1, $contact->assignee->id);
        $contact->push();
        $contact->fresh();

        $data = [
            'data' => [
                'type' => 'contact',
                'id' => $contact->id,
                'relationships' => [
                    'assignee' => [
                        'data' => null
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $contact = $this->hydrate($contact, $request);

        $this->assertNull($contact->assignee);
    }

    public function testHydrateBelongsToManyRelationshipForCreate()
    {
        $user = new User();
        $user->save();
        $user->organizations()->attach(1);
        $user->organizations()->attach(2);
        $data = [
            'data' => [
                [
                    'type' => 'organization',
                    'id' => '3'
                ],
                [
                    'type' => 'organization',
                    'id' => '4'
                ]
            ]
        ];


        $request = $this->getRequest($data, 'POST');
        $user = $this->hydrateRelationship($user, $request, 'organizations');

        $this->assertEquals(4, count($user->organizations));
        $this->assertNotNull($user->organizations->find(3));
        $this->assertNotNull($user->organizations->find(4));
    }

    public function testHydrateBelongsToManyRelationshipForUpdateClear()
    {
        $user = new User();
        $user->organizations()->attach(1);
        $user->organizations()->attach(2);
        $user->push();
        $user->fresh();

        $data = [
            'data' => [
                'type' => 'user',
                'id' => $user->id,
                'relationships' => [
                    'organizations' => [
                        'data' => []
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $user = $this->hydrate($user, $request);

        $this->assertEmpty($user->organizations);
    }

    public function testHydrateBelongsToManyRelationshipForUpdate()
    {
        $user = new User();
        $user->organizations()->attach(1);
        $user->organizations()->attach(2);
        $user->push();
        $user->fresh();

        $data = [
            'data' => [
                'type' => 'user',
                'id' => $user->id,
                'relationships' => [
                    'organizations' => [
                        'data' => [
                            [
                                'type' => 'organization',
                                'id' => '3'
                            ],
                            [
                                'type' => 'organization',
                                'id' => '4'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $request = $this->getRequest($data, 'PATCH');
        $user = $this->hydrate($user, $request);

        $this->assertEquals(2, count($user->organizations));
        $this->assertNotNull($user->organizations->find(3));
        $this->assertNotNull($user->organizations->find(4));
    }

    public function testHydrateBelongsToManyRelationshipForDelete()
    {
        $user = new User();
        $user->save();
        $user->organizations()->attach([1, 2, 3]);

        $data = [
            'data' => [
                [
                    'type' => 'organization',
                    'id' => '1'
                ],
                [
                    'type' => 'organization',
                    'id' => '2'
                ]
            ]
        ];

        $request = $this->getRequest($data, 'DELETE');
        $user = $this->hydrateRelationship($user, $request, 'organizations');

        $this->assertEquals(1, count($user->organizations));
        $this->assertNotNull($user->organizations->find(3));
    }

    public function testHydratorFactoryWithInvalidRelation()
    {
        $relationshipHydratorFactory = new RelationshipHydratorFactory();

    }

    protected function getRequest(array $jsonData, $method)
    {
        $method = strtoupper($method);
        $options = array(
            'REQUEST_METHOD' => $method,
            'REQUEST_URI'    => '/'
        );

        $params  = json_encode($jsonData);

        // Prepare a mock environment
        $env = Environment::mock(array_merge($options, []));
        $uri = Uri::createFromEnvironment($env);
        $headers = Headers::createFromEnvironment($env);
        $serverParams = $env->all();
        $body = new RequestBody();

        // Attach JSON request
        if (isset($params)) {
            $headers->set('Content-Type', 'application/vnd.api+json');
            $body->write($params);
        }

        $request = new Request($method, $uri, $headers, [], $serverParams, $body);

        return new \WoohooLabs\Yin\JsonApi\Request\Request($request, new DefaultExceptionFactory());
    }

    private function hydrate($domainObject, RequestInterface $request)
    {
        $hydrator = new ModelHydrator();
        return $hydrator->hydrate($request, new DefaultExceptionFactory(), $domainObject);
    }

    private function hydrateRelationship($domainObject, RequestInterface $request, $relationshipName)
    {
        $hydrator = new ModelHydrator();
        return $hydrator->hydrateRelationship($relationshipName, $request, new DefaultExceptionFactory(), $domainObject);
    }
}
