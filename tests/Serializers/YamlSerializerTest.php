<?php

namespace Tests\Serializers;

use MilesChou\Toggle\Providers\ArrayProvider;
use MilesChou\Toggle\Serializers\YamlSerializer;

class YamlSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var YamlSerializer
     */
    private $target;

    protected function setUp()
    {
        $this->target = new YamlSerializer();
    }

    protected function tearDown()
    {
        $this->target = null;
    }

    /**
     * @test
     */
    public function shouldReturnSerializeResult()
    {
        $expected = <<< EXCEPTED_DATA
feature:
  f1:
    return: true
  f2:
    return: false
group:
  g1:
    list:
      - f1
      - f2
    return: f1

EXCEPTED_DATA;

        $provider = new ArrayProvider([
            'feature' => [
                'f1' => [
                    'return' => true,
                ],
                'f2' => [
                    'return' => false,
                ],
            ],
            'group' => [
                'g1' => [
                    'list' => [
                        'f1',
                        'f2',
                    ],
                    'return' => 'f1',
                ],
            ],
        ]);

        $actual = $this->target->serialize($provider);

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function shouldReturnDeserializeResult()
    {
        $input = <<< INPUT_DATA
feature:
  f1:
    return: true
  f2:
    return: false
group:
  g1:
    list:
      - f1
      - f2
    return: f1

INPUT_DATA;

        $expectedFeature = [
            'f1' => [
                'return' => true,
            ],
            'f2' => [
                'return' => false,
            ],
        ];

        $expectedGroup = [
            'g1' => [
                'list' => [
                    'f1',
                    'f2',
                ],
                'return' => 'f1',
            ],
        ];

        $actual = $this->target->deserialize($input, new ArrayProvider());

        $this->assertSame($expectedFeature, $actual->getFeatures());
        $this->assertSame($expectedGroup, $actual->getGroups());
    }
}
