<?php

namespace Tests\Toggle;

use MilesChou\Toggle\Feature;
use MilesChou\Toggle\Toggle;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ToggleTest extends TestCase
{
    /**
     * @var Toggle
     */
    private $target;

    protected function setUp()
    {
        $this->target = new Toggle();
    }

    protected function tearDown()
    {
        $this->target = null;
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function shouldThrowExceptionWhenCallIsActiveWithNoDataAndStrictMode()
    {
        $this->target->setStrict(true);

        $this->target->isActive('not-exist');
    }

    /**
     * @test
     */
    public function shouldReturnFalseDefaultWhenCallIsActive()
    {
        $this->assertFalse($this->target->isActive('not-exist'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureAndReturnTrue()
    {
        $this->target->create('foo', function () {
            return true;
        });

        $this->assertTrue($this->target->isActive('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureAndReturnFalse()
    {
        $this->target->create('foo', function () {
            return false;
        });

        $this->assertFalse($this->target->isActive('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureUsingStaticAndReturnTrue()
    {
        $this->target->create('foo', true);

        $this->assertTrue($this->target->isActive('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenCreateFeatureUsingStaticAndReturnFalse()
    {
        $this->target->create('foo', false);

        $this->assertFalse($this->target->isActive('foo'));
    }

    /**
     * @test
     * @expectedException RuntimeException
     */
    public function shouldThrowExceptionWhenCreateFeatureAndRemoveFeature()
    {
        $this->target->setStrict(true);

        $this->target->create('foo')
            ->remove('foo');

        $this->target->isActive('foo');
    }

    /**
     * @test
     */
    public function shouldReturnSameResultWhenIsActiveWithDifferentContext()
    {
        $this->target->create('f1', function ($context) {
            $id = $context['id'];

            return 0 === $id % 2;
        });

        $this->assertTrue($this->target->isActive('f1', ['id' => 2]));
        $this->assertTrue($this->target->isActive('f1', ['id' => 3]));
    }

    /**
     * @test
     */
    public function shouldReturnDifferentResultWhenIsActiveWithDifferentContextWithoutPreserve()
    {
        $this->target->create('f1', function ($context) {
            $id = $context['id'];

            return 0 === $id % 2;
        });

        $this->assertTrue($this->target->isActive('f1', ['id' => 2]));
        $this->assertFalse($this->target->duplicate()->isActive('f1', ['id' => 3]));
    }

    /**
     * @test
     */
    public function shouldReturnDifferentResultWhenIsActiveWithProvisionContext()
    {
        $this->target->create('f1', function ($context) {
            $id = $context['id'];

            return 0 === $id % 2;
        });

        $this->target->context(['id' => 2]);

        $this->assertTrue($this->target->isActive('f1'));

        $this->target = $this->target->duplicate()->context(['id' => 3]);

        $this->assertFalse($this->target->isActive('f1'));
    }

    /**
     * @test
     */
    public function shouldReplaceProvisionContextWhenUsingOnDemandContext()
    {
        $this->target->create('f1', function ($context) {
            $id = $context['id'];

            return 0 === $id % 2;
        });

        $this->target->context(['id' => 2]);

        $this->assertFalse($this->target->isActive('f1', ['id' => 3]));

        $this->target = $this->target->duplicate()->context(['id' => 3]);

        $this->assertTrue($this->target->isActive('f1', ['id' => 2]));
    }

    /**
     * @test
     */
    public function shouldBeWorkWhenCallWhenIsActive()
    {
        $this->target->create('f1', true, ['bar' => 'a']);

        $this->target->when('f1', function (Feature $feature, $context) {
            $this->assertSame('a', $feature->params('bar'));
            $this->assertSame('b', $context['foo']);
        }, null, ['foo' => 'b']);
    }

    /**
     * @test
     */
    public function shouldBeWorkWhenCallWhenIsDeactivate()
    {
        $this->target->create('f1', false, ['bar' => 'a']);

        $this->target->when('f1', function (Feature $feature, $context) {
            $this->assertSame('a', $feature->params('bar'));
            $this->assertSame('b', $context['foo']);
        }, null, ['foo' => 'b']);

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function shouldBeWorkWhenCallSet()
    {
        $this->target->set('f1', Feature::create(true));

        $this->assertTrue($this->target->isActive('f1'));
    }

    /**
     * @test
     */
    public function shouldUsedLatestResultWhenCallSetTwice()
    {
        $this->target->set('f1', Feature::create(true));
        $this->target->set('f1', Feature::create(false));

        $this->assertFalse($this->target->isActive('f1'));
    }
}
