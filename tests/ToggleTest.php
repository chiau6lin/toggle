<?php

namespace Tests;

use MilesChou\Toggle\Context;
use MilesChou\Toggle\Feature;
use MilesChou\Toggle\Toggle;

class ToggleTest extends \PHPUnit_Framework_TestCase
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
     */
    public function shouldThrowExceptionWhenCallIsActiveWithNoDataAndStrictMode()
    {
        $this->setExpectedException('RuntimeException');

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
     */
    public function shouldThrowExceptionWhenCreateFeatureAndRemoveFeature()
    {
        $this->setExpectedException('RuntimeException', 'foo');

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
        $this->target->create('f1', function (Context $context) {
            $id = $context->getParam('id');

            return 0 === $id % 2;
        });

        $this->assertTrue($this->target->isActive('f1', Context::create(['id' => 2])));
        $this->assertTrue($this->target->isActive('f1', Context::create(['id' => 3])));
    }

    /**
     * @test
     */
    public function shouldReturnDifferentResultWhenIsActiveWithDifferentContextWithoutPreserve()
    {
        $this->target->create('f1', function (Context $context) {
            $id = $context->getParam('id');

            return 0 === $id % 2;
        });

        $this->target->setPreserve(false);

        $this->assertTrue($this->target->isActive('f1', Context::create(['id' => 2])));
        $this->assertFalse($this->target->isActive('f1', Context::create(['id' => 3])));
    }

    /**
     * @test
     */
    public function shouldBeWorkWhenCallWhenIsActive()
    {
        $this->target->create('f1', true, ['bar' => 'a']);

        $context = new Context(['foo' => 'b']);

        $this->target->when('f1')
            ->isActive($context)
            ->then(function (Feature $feature, Context $context) {
                $this->assertSame('a', $feature->params('bar'));
                $this->assertSame('b', $context->get('foo'));
            });
    }

    /**
     * @test
     */
    public function shouldBeWorkWhenCallWhenIsDeactivate()
    {
        $this->target->create('f1', false, ['bar' => 'a']);

        $context = new Context(['foo' => 'b']);

        $this->target->when('f1')
            ->isInactive($context)
            ->then(function (Feature $feature, Context $context) {
                $this->assertSame('a', $feature->params('bar'));
                $this->assertSame('b', $context->get('foo'));
            });
    }
}
