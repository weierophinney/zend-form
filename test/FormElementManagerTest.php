<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Form;

use Zend\Form\ElementInterface;
use Zend\Form\Exception\InvalidElementException;
use Zend\Form\Factory;
use Zend\Form\Form;
use Zend\Form\FormElementManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

/**
 * @group      Zend_Form
 */
class FormElementManagerTest extends \PHPUnit_Framework_TestCase
{
    use CommonPluginManagerTrait;

    /**
     * @var FormElementManager
     */
    protected $manager;

    public function setUp()
    {
        $this->manager = new FormElementManager(new ServiceManager());
    }

    public function testInjectToFormFactoryAware()
    {
        $form = $this->manager->get('Form');
        $this->assertSame($this->manager, $form->getFormFactory()->getFormElementManager());
    }

    /**
     * @group 3735
     */
    public function testInjectsFormElementManagerToFormComposedByFormFactoryAwareElement()
    {
        $factory = new Factory();
        $this->manager->setFactory('my-form', function ($elements) use ($factory) {
            $form = new Form();
            $form->setFormFactory($factory);
            return $form;
        });
        $form = $this->manager->get('my-form');
        $this->assertSame($factory, $form->getFormFactory());
        $this->assertSame($this->manager, $form->getFormFactory()->getFormElementManager());
    }

    public function testStringCreationOptions()
    {
        $args = 'foo';
        $element = $this->manager->get('element', $args);
        $this->assertEquals('foo', $element->getName(), 'The argument is string');
    }

    public function testArrayCreationOptions()
    {
        $args = [
            'name' => 'foo',
            'options' => [
                'label' => 'bar'
            ],
        ];
        $element = $this->manager->get('element', $args);
        $this->assertEquals('foo', $element->getName(), 'Specified name in array[name]');
        $this->assertEquals('bar', $element->getLabel(), 'Specified options in array[options]');
    }

    public function testOptionsCreationOptions()
    {
        $args = [
            'label' => 'bar'
        ];
        $element = $this->manager->get('element', $args);
        $this->assertEquals('element', $element->getName(), 'Invokable CNAME');
        $this->assertEquals('bar', $element->getLabel(), 'Specified options in array');
    }

    public function testArrayOptionsCreationOptions()
    {
        $args = [
            'options' => [
                'label' => 'bar'
            ],
        ];
        $element = $this->manager->get('element', $args);
        $this->assertEquals('element', $element->getName(), 'Invokable CNAME');
        $this->assertEquals('bar', $element->getLabel(), 'Specified options in array[options]');
    }

    /**
     * @group 6132
     */
    public function testSharedFormElementsAreNotInitializedMultipleTimes()
    {
        $element = $this->getMock('Zend\Form\Element', ['init']);

        $element->expects($this->once())->method('init');

        $this->manager->setFactory('sharedElement', function () use ($element) {
            return $element;
        });

        $this->manager->setShared('sharedElement', true);

        $this->manager->get('sharedElement');
        $this->manager->get('sharedElement');
    }

    protected function getPluginManager()
    {
        return new FormElementManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidElementException::class;
    }

    protected function getInstanceOf()
    {
        return ElementInterface::class;
    }
}
