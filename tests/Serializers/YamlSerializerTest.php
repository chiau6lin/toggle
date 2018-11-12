<?php

namespace Tests\Serializers;

use MilesChou\Toggle\Providers\DataProvider;
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

EXCEPTED_DATA;

        $provider = new DataProvider([
            'feature' => [
                'f1' => [
                    'return' => true,
                ],
                'f2' => [
                    'return' => false,
                ],
            ]
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
f1:
  return: true
f2:
  return: false

INPUT_DATA;

        $expectedFeature = [
            'f1' => [
                'return' => true,
            ],
            'f2' => [
                'return' => false,
            ],
        ];

        $actual = $this->target->deserialize($input, new DataProvider());

        $this->assertSame($expectedFeature, $actual->toArray());
    }
}
